<?php
/**
 * Direct API Test for 3 Chatbot Models
 * Tests: Gemini (native), Gemma (OpenRouter), GPT (OpenRouter)
 * Run: php test_models.php
 */

$dotenvFile = __DIR__ . '/.env';
$keys = [];
if (file_exists($dotenvFile)) {
    foreach (file($dotenvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $keys[trim($k)] = trim($v);
    }
}

$geminiKey = $keys['GEMINI_API_KEY'] ?? '';
$openrouterKey = $keys['OPENROUTER_API_KEY'] ?? '';

$models = [
    'Gemini (Gemini 2.5 Flash - Native API)' => [
        'type' => 'gemini',
        'model' => 'gemini-2.5-flash',
    ],
    'Gemma (Gemma 4 31B - OpenRouter)' => [
        'type' => 'openrouter',
        'model' => 'google/gemma-4-31b-it:free',
    ],
    'GPT (GPT-OSS 120B - OpenRouter)' => [
        'type' => 'openrouter',
        'model' => 'openai/gpt-oss-120b:free',
    ],
];

$testMessage = 'Say "Hello! I am working!" in one short sentence.';

echo "========================================\n";
echo "  CHATBOT MODEL TEST\n";
echo "========================================\n\n";

$results = [];

foreach ($models as $name => $config) {
    echo str_repeat('-', 50) . "\n";
    echo "Testing: {$name}\n";
    echo str_repeat('-', 50) . "\n";

    $apiKey = $config['type'] === 'gemini' ? $geminiKey : $openrouterKey;

    if (empty($apiKey)) {
        echo "❌ SKIP - API key not configured\n\n";
        $results[$name] = 'SKIP (no API key)';
        continue;
    }

    $startTime = microtime(true);

    if ($config['type'] === 'gemini') {
        $result = testGemini($config['model'], $geminiKey, $testMessage);
    } else {
        $result = testOpenRouter($config['model'], $openrouterKey, $testMessage);
    }

    $elapsed = round(microtime(true) - $startTime, 2);

    if ($result['success']) {
        echo "✅ PASS ({$elapsed}s)\n";
        echo "Response: " . substr($result['response'], 0, 200) . "\n\n";
        $results[$name] = "PASS ({$elapsed}s)";
    } else {
        echo "❌ FAIL ({$elapsed}s)\n";
        echo "Error: " . $result['error'] . "\n\n";
        $results[$name] = "FAIL: {$result['error']}";
    }
}

echo "========================================\n";
echo "  SUMMARY\n";
echo "========================================\n";
foreach ($results as $name => $status) {
    $icon = str_starts_with($status, 'PASS') ? '✅' : (str_starts_with($status, 'SKIP') ? '⏭️' : '❌');
    echo "{$icon} {$name}: {$status}\n";
}
echo "========================================\n";

// --- Helper Functions ---

function testGemini(string $model, string $apiKey, string $message): array {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

    $payload = json_encode([
        'contents' => [
            ['role' => 'user', 'parts' => [['text' => $message]]]
        ],
        'systemInstruction' => [
            'parts' => [['text' => 'You are a helpful assistant. Respond concisely.']]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 256,
        ],
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['success' => false, 'error' => "cURL error: {$error}"];
    }

    if ($httpCode !== 200) {
        $decoded = json_decode($response, true);
        $msg = $decoded['error']['message'] ?? "HTTP {$httpCode}";
        return ['success' => false, 'error' => $msg];
    }

    $decoded = json_decode($response, true);
    $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;

    if ($text) {
        return ['success' => true, 'response' => trim($text)];
    }

    return ['success' => false, 'error' => 'No text in response'];
}

function testOpenRouter(string $model, string $apiKey, string $message): array {
    $url = "https://openrouter.ai/api/v1/chat/completions";

    $payload = json_encode([
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful assistant. Respond concisely.'],
            ['role' => 'user', 'content' => $message],
        ],
        'temperature' => 0.7,
        'max_tokens' => 256,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
            'HTTP-Referer: http://localhost/TALAbahan-system/',
            'X-Title: TALAbahan Test',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['success' => false, 'error' => "cURL error: {$error}"];
    }

    if ($httpCode !== 200) {
        $decoded = json_decode($response, true);
        $msg = $decoded['error']['message'] ?? "HTTP {$httpCode}";
        return ['success' => false, 'error' => $msg];
    }

    $decoded = json_decode($response, true);
    $text = $decoded['choices'][0]['message']['content'] ?? null;

    if ($text) {
        return ['success' => true, 'response' => trim($text)];
    }

    return ['success' => false, 'error' => 'No text in response'];
}
