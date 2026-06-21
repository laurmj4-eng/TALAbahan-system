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
        import { getAuth, GoogleAuthProvider, signInWithRedirect, getRedirectResult, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";

        const statusEl = document.getElementById('status');

        if (!window.FIREBASE_CONFIG || !window.FIREBASE_CONFIG.apiKey) {
            statusEl.innerText = "Firebase not configured.";
        } else {
            const app = initializeApp(window.FIREBASE_CONFIG);
            const auth = getAuth(app);
            const provider = new GoogleAuthProvider();

            onAuthStateChanged(auth, (user) => {
                if (user) {
                    statusEl.innerText = "Verifying...";
                    const email = encodeURIComponent(user.email);
                    const name = encodeURIComponent(user.displayName || '');
                    const callbackUrl = window.BASE_URL + 'auth/mobile-callback?email=' + email + '&name=' + name;
                    window.location.href = 'talabahan://auth?redirect=' + encodeURIComponent(callbackUrl);
                } else {
                    getRedirectResult(auth).then((result) => {
                        if (result && result.user) {
                            // Handled by onAuthStateChanged above
                        } else {
                            const hasTriggered = sessionStorage.getItem('mobileLoginTriggered');
                            if (!hasTriggered) {
                                sessionStorage.setItem('mobileLoginTriggered', 'true');
                                signInWithRedirect(auth, provider);
                            } else {
                                statusEl.innerHTML = "Google Sign-In failed or was cancelled.<br><br><button id='retryBtn' style='padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;'>Try Again</button>";
                                document.querySelector('.spinner').style.display = 'none';
                                document.getElementById('retryBtn').addEventListener('click', () => {
                                    sessionStorage.removeItem('mobileLoginTriggered');
                                    window.location.reload();
                                });
                            }
                        }
                    }).catch((error) => {
                        console.error("Firebase auth error:", error);
                        statusEl.innerText = "Authentication failed: " + error.message;
                        document.querySelector('.spinner').style.display = 'none';
                    });
                }
            });
        }
    </script>
</body>
</html>
