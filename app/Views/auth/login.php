<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mj AI Chatbot - Login</title>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>

<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
    import { getAuth, GoogleAuthProvider, signInWithPopup } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";

    const firebaseConfig = {
        apiKey: "AIzaSyDSR0dDw5ETDH4SLpPA7BJT9OOgs2zovCw",
        authDomain: "hahaah-90451.firebaseapp.com",
        projectId: "hahaah-90451",
        storageBucket: "hahaah-90451.firebasestorage.app",
        messagingSenderId: "794371226159",
        appId: "1:794371226159:web:ed82175cca31d7368864da",
        measurementId: "G-P161NP90XW"
    };

    const app = initializeApp(firebaseConfig);
    const auth = getAuth(app);

    const emailInput = document.getElementById('email');
    const captchaBox = document.getElementById('captcha-container');

    emailInput.addEventListener('input', () => {
        const typedEmail = emailInput.value.trim().toLowerCase();
        let trustedEmails = JSON.parse(localStorage.getItem('mj_trusted_emails')) || [];

        if (trustedEmails.includes(typedEmail)) {
            captchaBox.style.display = 'none'; 
        } else {
            captchaBox.style.display = 'block'; 
        }
    });

    // --- 1. NORMAL EMAIL & PASSWORD LOGIN (FIXED TO USE YOUR DATABASE) ---
    document.getElementById('loginForm').addEventListener('submit', (e) => {
        e.preventDefault(); 

        const email = emailInput.value.trim().toLowerCase();
        const password = document.getElementById('password').value; // Get password
        const rememberMe = document.getElementById('rememberMe').checked;
        const loginBtn = document.getElementById('loginBtn');

        if (captchaBox.style.display !== 'none') {
            const recaptchaResponse = grecaptcha.getResponse();
            if (recaptchaResponse.length === 0) {
                alert("Please complete the reCAPTCHA to verify you are human.");
                return; 
            }
        }

        loginBtn.textContent = "Logging in...";
        loginBtn.disabled = true;

        // FIXED: Send directly to your CodeIgniter Backend (Auth.php), DO NOT ask Firebase!
        verifyWithBackend(email, password, rememberMe, loginBtn, "Login", "", "email");
    });

    // --- 2. GOOGLE SIGN-IN LOGIC (Unchanged, this works fine!) ---
    const googleBtn = document.getElementById('googleBtn');
    const provider = new GoogleAuthProvider();

    googleBtn.addEventListener('click', () => {
        googleBtn.textContent = "Please wait...";
        googleBtn.disabled = true;

        signInWithPopup(auth, provider)
            .then((result) => {
                const user = result.user;
                // Send an empty password for Google, Auth.php knows how to handle it
                verifyWithBackend(user.email, "", true, googleBtn, "Sign in with Google", user.displayName, "google");
            })
            .catch((error) => {
                console.error(error);
                alert("Google Sign-In failed or was cancelled.");
                googleBtn.textContent = "Sign in with Google";
                googleBtn.disabled = false;
            });
    });

    // --- HELPER FUNCTION: SEND DATA TO PHP BACKEND (FIXED) ---
    function verifyWithBackend(email, password, rememberMe, buttonElement, originalButtonText, displayName = "", provider = "email") {
        let formData = new FormData();
        formData.append('email', email);
        formData.append('password', password); // FIXED: Actually send the password to PHP!
        formData.append('remember', rememberMe); 
        formData.append('name', displayName); 
        formData.append('provider', provider); 

        fetch('<?= base_url('auth/verify') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json()) 
        .then(data => {
            if (data.status === 'success') {
                if (data.trust_device === true) {
                    let trustedEmails = JSON.parse(localStorage.getItem('mj_trusted_emails')) || [];
                    if (!trustedEmails.includes(email)) {
                        trustedEmails.push(email);
                        localStorage.setItem('mj_trusted_emails', JSON.stringify(trustedEmails));
                    }
                }
                // Redirect on success
                window.location.href = data.redirect;
            } else {
                // Show the error message sent from Auth.php
                alert(data.message || "Account not found. Please try again.");
                buttonElement.textContent = originalButtonText;
                buttonElement.disabled = false;
                if (captchaBox.style.display !== 'none' && typeof grecaptcha !== 'undefined') { 
                    grecaptcha.reset(); 
                }
            }
        })
        .catch(err => {
            console.error("Verification Error:", err);
            alert("System Error: Could not verify user.");
            buttonElement.textContent = originalButtonText;
            buttonElement.disabled = false;
        });
    }
</script>

<form id="loginForm" class="login-container">
    <h2>Sign In to Mj AI</h2>
    
    <input type="email" id="email" name="email" placeholder="Email Address" autocomplete="username" required>
    <input type="password" id="password" name="password" placeholder="Password" autocomplete="current-password" required>
    
    <div style="display: flex; align-items: center; margin-bottom: 20px; font-size: 14px; text-align: left;">
        <input type="checkbox" id="rememberMe" style="width: auto; margin-right: 8px; cursor: pointer;">
        <label for="rememberMe" style="cursor: pointer; color: #555;">Remember Me (Trust this device for this email)</label>
    </div>
    
    <!-- reCAPTCHA Container -->
    <div id="captcha-container" style="margin-bottom: 15px;">
        <div class="g-recaptcha" data-sitekey="6LcVGI0sAAAAAOJIrADJWMJKDDGALrEHHnCB8c1A"></div>
    </div>
    
    <button type="submit" id="loginBtn">Login</button>
    <a href="<?= site_url('register') ?>"><button type="button">Create Account</button></a>
    
    <div class="links">
        <a id="forgotPasswordBtn" href="#">Forgot Password?</a>
    </div>

    <div class="divider"><span>OR</span></div>
    <button type="button" id="googleBtn" class="google-btn">Sign in with Google</button>
</form>

</body>
</html>