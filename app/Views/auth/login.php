<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mj AI Chatbot - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>

<!-- TOP RIGHT LOGO -->
<img src="<?= base_url('images/pic3.jpg') ?>" alt="Logo" class="top-right-logo">

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

    // CSRF Token Info from CodeIgniter
    const csrfTokenName = '<?= csrf_token() ?>';

    emailInput.addEventListener('input', () => {
        const typedEmail = emailInput.value.trim().toLowerCase();
        let trustedEmails = JSON.parse(localStorage.getItem('mj_trusted_emails')) || [];

        if (trustedEmails.includes(typedEmail)) {
            captchaBox.style.display = 'none'; 
        } else {
            captchaBox.style.display = 'block'; 
        }
    });

    // --- 1. NORMAL EMAIL & PASSWORD LOGIN ---
    document.getElementById('loginForm').addEventListener('submit', (e) => {
        e.preventDefault(); 

        const email = emailInput.value.trim().toLowerCase();
        const password = document.getElementById('password').value;
        const rememberMe = document.getElementById('rememberMe').checked;
        const loginBtn = document.getElementById('loginBtn');

        let recaptchaResponse = "";
        if (captchaBox.style.display !== 'none') {
            recaptchaResponse = grecaptcha.getResponse();
            if (recaptchaResponse.length === 0) {
                alert("Please complete the reCAPTCHA to verify you are human.");
                return; 
            }
        }

        loginBtn.textContent = "Logging in...";
        loginBtn.disabled = true;

        verifyWithBackend(email, password, rememberMe, loginBtn, "Login", "", "email", recaptchaResponse);
    });

    // --- 2. GOOGLE SIGN-IN LOGIC ---
    const googleBtn = document.getElementById('googleBtn');
    const provider = new GoogleAuthProvider();

    googleBtn.addEventListener('click', () => {
        const originalContent = googleBtn.innerHTML;
        googleBtn.textContent = "Please wait...";
        googleBtn.disabled = true;

        signInWithPopup(auth, provider)
            .then((result) => {
                const user = result.user;
                verifyWithBackend(user.email, "", true, googleBtn, originalContent, user.displayName, "google", "");
            })
            .catch((error) => {
                console.error(error);
                alert("Google Sign-In failed or was cancelled.");
                googleBtn.innerHTML = originalContent;
                googleBtn.disabled = false;
            });
    });

    // --- HELPER FUNCTION: SEND DATA TO PHP BACKEND ---
    function verifyWithBackend(email, password, rememberMe, buttonElement, originalButtonText, displayName = "", provider = "email", recaptchaToken = "") {
        let formData = new FormData();
        
        // ADD CSRF TOKEN (Crucial to fix "Action not allowed")
        const csrfHash = document.querySelector('input[name="' + csrfTokenName + '"]').value;
        formData.append(csrfTokenName, csrfHash);

        formData.append('email', email);
        formData.append('password', password);
        formData.append('remember', rememberMe); 
        formData.append('name', displayName); 
        formData.append('provider', provider); 
        formData.append('g-recaptcha-response', recaptchaToken); // Added for backend verification

        fetch('<?= base_url('auth/verify') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest' // Tells CI this is an AJAX request
            }
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
                window.location.href = data.redirect;
            } else {
                // UPDATE CSRF TOKEN ON FAILURE (So the next click doesn't get "Action not allowed")
                if (data.token) {
                    document.querySelector('input[name="' + csrfTokenName + '"]').value = data.token;
                }

                alert(data.message || "Account not found.");
                
                // Use innerHTML if it contains HTML (like the Google icon), else textContent
                if (originalButtonText.includes('<svg')) {
                    buttonElement.innerHTML = originalButtonText;
                } else {
                    buttonElement.textContent = originalButtonText;
                }
                
                buttonElement.disabled = false;
                
                if (typeof grecaptcha !== 'undefined') { 
                    grecaptcha.reset(); 
                }
            }
        })
        .catch(err => {
            console.error("Verification Error:", err);
            alert("System Error: Could not verify user.");
            
            if (originalButtonText.includes('<svg')) {
                buttonElement.innerHTML = originalButtonText;
            } else {
                buttonElement.textContent = originalButtonText;
            }
            
            buttonElement.disabled = false;
        });
    }
</script>

<form id="loginForm" class="login-container">
    <!-- ADDED: CSRF Field (Required by CodeIgniter) -->
    <?= csrf_field() ?>

    <h2>Sign In to Mj AI</h2>
    
    <input type="email" id="email" name="email" placeholder="Email Address" autocomplete="username" required>
    <input type="password" id="password" name="password" placeholder="Password" autocomplete="current-password" required>
    
    <div class="remember-me-container">
        <input type="checkbox" id="rememberMe">
        <label for="rememberMe">Remember Me (Trust this device for this email)</label>
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
    <button type="button" id="googleBtn" class="google-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 48 48">
            <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
            <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
            <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
            <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
        </svg>
        Sign in with Google
    </button>
</form>

</body>
</html>