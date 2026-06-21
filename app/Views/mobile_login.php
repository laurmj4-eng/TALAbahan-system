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
            // Force Google to show the account picker so the user can choose a different email
            provider.setCustomParameters({
                prompt: 'select_account'
            });

            const handleSuccess = (user) => {
                statusEl.innerText = "Verifying...";
                const email = encodeURIComponent(user.email);
                const name = encodeURIComponent(user.displayName || '');
                const callbackUrl = window.BASE_URL + 'auth/mobile-callback?email=' + email + '&name=' + name;
                window.location.href = 'talabahan://auth?redirect=' + encodeURIComponent(callbackUrl);
            };

            onAuthStateChanged(auth, (user) => {
                const hasTriggered = localStorage.getItem('mobileLoginTriggered');

                if (user && hasTriggered === 'true') {
                    // Successfully returned from Google Redirect
                    localStorage.removeItem('mobileLoginTriggered');
                    handleSuccess(user);
                } else if (!user && hasTriggered === 'true') {
                    // Returned from redirect, but user is null. Check getRedirectResult just in case.
                    getRedirectResult(auth).then((result) => {
                        if (result && result.user) {
                            localStorage.removeItem('mobileLoginTriggered');
                            handleSuccess(result.user);
                        } else {
                            // User cancelled or it failed
                            localStorage.removeItem('mobileLoginTriggered');
                            document.querySelector('.spinner').style.display = 'none';
                            statusEl.innerHTML = "Sign-in cancelled.<br><br><button onclick='window.location.reload()' style='padding:10px 20px; background:#3b82f6; color:white; border:none; border-radius:8px; font-weight:bold; cursor:pointer;'>Try Again</button>";
                        }
                    }).catch((error) => {
                        localStorage.removeItem('mobileLoginTriggered');
                        document.querySelector('.spinner').style.display = 'none';
                        statusEl.innerText = "Authentication failed: " + error.message;
                    });
                } else {
                    // hasTriggered is not true (First time opening the page)
                    // We FORCE a new login and clear any old cached Firebase sessions
                    localStorage.setItem('mobileLoginTriggered', 'true');
                    auth.signOut().then(() => {
                        signInWithRedirect(auth, provider);
                    });
                }
            });
        }
    </script>
</body>
</html>
