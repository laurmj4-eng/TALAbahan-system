<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Sign-In</title>
    <script src="/app-config.js"></script>
    <style>
        body {
            background-color: #0f172a;
            color: white;
            font-family: sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }
        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.1);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border-left-color: #3b82f6;
            animation: spin 1s linear infinite;
            margin: 0 auto 16px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div>
        <div class="spinner"></div>
        <h2 id="status">Connecting to Google...</h2>
    </div>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
        import { getAuth, GoogleAuthProvider, signInWithRedirect, getRedirectResult } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";

        const statusEl = document.getElementById('status');

        if (!window.FIREBASE_CONFIG || !window.FIREBASE_CONFIG.apiKey) {
            statusEl.innerText = "Firebase not configured.";
        } else {
            const app = initializeApp(window.FIREBASE_CONFIG);
            const auth = getAuth(app);
            const provider = new GoogleAuthProvider();

            // First check if we are returning from a redirect
            getRedirectResult(auth).then((result) => {
                if (result && result.user) {
                    statusEl.innerText = "Verifying...";
                    
                    const email = encodeURIComponent(result.user.email);
                    const name = encodeURIComponent(result.user.displayName || '');
                    
                    // The backend callback URL that will establish the session
                    const callbackUrl = window.BASE_URL + 'auth/mobile-callback?email=' + email + '&name=' + name;
                    
                    // Redirect back to the mobile app
                    window.location.href = 'talabahan://auth?redirect=' + encodeURIComponent(callbackUrl);
                } else {
                    // Start the redirect login flow
                    signInWithRedirect(auth, provider);
                }
            }).catch((error) => {
                console.error("Firebase auth error:", error);
                statusEl.innerText = "Authentication failed: " + error.message;
            });
        }
    </script>
</body>
</html>
