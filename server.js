require('dotenv').config();
const express = require('express');
const cors = require('cors');
const rateLimit = require('express-rate-limit');
const mysql = require('mysql2/promise'); 

if (!process.env.OPENROUTER_API_KEY) {
    console.error("❌ ERROR: OPENROUTER_API_KEY missing in .env!");
    process.exit(1);
}

const app = express();

app.use(cors({
    origin: '*',
    methods: ['GET', 'POST']
})); 

app.use(express.json());

let pool;
try {
    let poolConfig = { 
        host: process.env.DB_HOST || 'localhost', 
        user: process.env.DB_USER || 'root', 
        password: process.env.DB_PASS || '', 
        database: process.env.DB_NAME || 'mj_chatbot',
        port: process.env.DB_PORT || 3306,
    };

    // Conditionally add SSL configuration based on environment variable
    if (process.env.DB_SSL === 'true') {
        poolConfig.ssl = {
            rejectUnauthorized: false
        };
    }

    pool = mysql.createPool(poolConfig);
    pool.query(`CREATE TABLE IF NOT EXISTS firebase_users_tracking (uid VARCHAR(255) PRIMARY KEY, prompt_count INT DEFAULT 0, last_reset DATE)`);
    console.log("Database connected");
} catch (err) { 
    console.error('❌ SQL Error:', err.message); 
}

const chatLimiter = rateLimit({ windowMs: 60 * 1000, max: 15 });
app.use('/api/chat', chatLimiter);

const SYS_INSTRUCT = "You are Mj, a friendly, intelligent, and helpful AI assistant. Always provide complete, well-thought-out sentences. Format securely with emojis. You speak English and Tagalog. 🤖✨";

app.post('/api/chat', async (req, res) => {
    const { uid, history, modelName } = req.body;
    
    console.log(`\n💬 New Chat Request. Requested Model: ${modelName}`);

    if (!uid) return res.status(401).json({ error: "Missing UID token" });
    if (!history || !Array.isArray(history) || history.length === 0) return res.status(400).json({ error: "Invalid input." });

    try {
        const todayStr = new Date().toISOString().slice(0, 10);
        let [rows] = await pool.query('SELECT prompt_count, last_reset FROM firebase_users_tracking WHERE uid = ?', [uid]);
        let dbPromptsCount = 0;
        
        if (rows.length === 0) {
            await pool.query('INSERT INTO firebase_users_tracking (uid, prompt_count, last_reset) VALUES (?, 0, CURDATE())', [uid]);
        } else {
            if (new Date(rows[0].last_reset).toISOString().slice(0, 10) !== todayStr) {
                await pool.query('UPDATE firebase_users_tracking SET prompt_count = 0, last_reset = CURDATE() WHERE uid = ?', [uid]);
            } else {
                dbPromptsCount = rows[0].prompt_count;
            }
        }
        
        if (dbPromptsCount >= 20) {
            res.setHeader('Content-Type', 'text/event-stream');
            res.write(`data: ${JSON.stringify({ text: "🛑 **Daily limit reached!** Refresh at midnight! 🕰️" })}\n\n`);
            return res.end();
        }

        const lastUserMessageText = history[history.length - 1].content.trim().toLowerCase();
        const devKeywords = ["sino", "who", "gumawa", "develop", "creator", "maker", "made", "nito", "neto", "built", "program"];
        const matchCount = devKeywords.filter(word => lastUserMessageText.includes(word)).length;

        if (lastUserMessageText.includes("developer") || matchCount >= 2) {
            res.setHeader('Content-Type', 'text/event-stream');
            res.write(`data: ${JSON.stringify({ text: "The Developer or the Creator of this is MJ laurito the pogi 😎✨" })}\n\n`);
            res.write('data: [DONE]\n\n');
            return res.end(); 
        }

        // ⭐ THE FIX: Simple, clean model string. No arrays.
        let safeModel = modelName || "openrouter/auto"; 

        let aiMemoryArray = [{ role: "system", content: SYS_INSTRUCT }];
        history.slice(-5).forEach(msg => aiMemoryArray.push({ role: msg.role, content: typeof msg.content === "string" ? msg.content : " " }));

        // CONNECT TO OPENROUTER (Restored to 100% working version)
        const openRouterResponse = await fetch("https://openrouter.ai/api/v1/chat/completions", {
            method: "POST", 
            headers: { 
                "Authorization": `Bearer ${process.env.OPENROUTER_API_KEY.trim()}`, 
                "HTTP-Referer": "http://localhost:8080", 
                "Content-Type": "application/json" 
            },
            body: JSON.stringify({ 
                model: safeModel, 
                messages: aiMemoryArray, 
                stream: true, 
                temperature: 0.7 
            })
        });

        if (!openRouterResponse.ok) {
            const errorData = await openRouterResponse.json();
            console.error("❌ OpenRouter Error Details:", errorData); 
            throw new Error(`${openRouterResponse.status}`); 
        }

        await pool.query('UPDATE firebase_users_tracking SET prompt_count = prompt_count + 1 WHERE uid = ?', [uid]);
        res.setHeader('Content-Type', 'text/event-stream');

        const reader = openRouterResponse.body.getReader();
        const decoder = new TextDecoder("utf-8");
        
        let buffer = ""; 

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split('\n');
            buffer = lines.pop(); 

            for(const line of lines) {
                if(line.startsWith('data: ') && line.trim() !== 'data: [DONE]') {
                   try { 
                       const parsedChunkObj = JSON.parse(line.slice(6));
                       const text = parsedChunkObj.choices?.[0]?.delta?.content;
                       if(text) {
                           res.write(`data: ${JSON.stringify({ text: text })}\n\n`);
                       }
                   } catch(e) { } 
                }
            }
        }
        res.write('data: [DONE]\n\n'); 
        return res.end();
        
    } catch (error) {
        console.error("Fetch Catch Error:", error.message);
        if (!res.headersSent) res.setHeader('Content-Type', 'text/event-stream');
        
        let displayError = "Whoops, OpenRouter API Error. Please try again! 🛠️";
        if (error.message.includes('429')) displayError = "Woah, too fast! The free servers are currently busy. Try again in a minute! 😅";
        if (error.message.includes('404')) displayError = "That specific model is currently offline. Please choose 'Auto Free' from the list! 🔄";

        res.write(`data: ${JSON.stringify({ text: `*${displayError}*` })}\n\n`); 
        res.write('data: [DONE]\n\n');
        return res.end();
    }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log("AI backend is connected");
    console.log("http://localhost:8080/");
});