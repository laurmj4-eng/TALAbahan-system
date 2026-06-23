<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signing in...</title>
    <script src="/app-config.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: #0f172a;
            color: white;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            text-align: center;
            padding: 1rem;
        }
        .container { max-width: 360px; width: 100%; }
        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.15);
            width: 44px; height: 44px;
            border-radius: 50%;
            border-left-color: #3b82f6;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        #status {
            font-size: 0.9375rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.5;
        }
        .error-box {
            color: #f87171;
            font-size: 0.8125rem;
            margin-top: 20px;
            padding: 12px 16px;
            background: rgba(248, 113, 113, 0.1);
            border: 1px solid rgba(248, 113, 113, 0.2);
            border-radius: 10px;
            line-height: 1.4;
        }
        .retry-btn {
            margin-top: 16px;
            padding: 12px 28px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: background 0.15s;
        }
        .retry-btn:hover { background: #2563eb; }
        .hidden { display: none !important; }
    </style>
</head>
<body>
    <div class="container">
        <div id="loading-state">
            <div class="spinner"></div>
            <p id="status">Connecting to Google...</p>
        </div>

        <div id="error-container" class="hidden">
            <div id="error-msg" class="error-box"></div>
            <button class="retry-btn" onclick="retrySignIn()">Try Again</button>
        </div>
    </div>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
        import {
            getAuth,
            GoogleAuthProvider,
            signInWithPopup
        } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";

        const statusEl = document.getElementById('status');
        const loadingState = document.getElementById('loading-state');
        const errorContainer = document.getElementById('error-container');
        const errorMsg = document.getElementById('error-msg');

        window.retrySignIn = function() {
            errorContainer.classList.add('hidden');
            loadingState.classList.remove('hidden');
            statusEl.textContent = 'Connecting to Google...';
            doGoogleSignIn();
        };

        function showError(msg) {
            loadingState.classList.add('hidden');
            errorContainer.classList.remove('hidden');
            errorMsg.textContent = msg;
        }

        function handleSuccess(user) {
            statusEl.textContent = 'Redirecting back to app...';
            const email = encodeURIComponent(user.email);
            const name = encodeURIComponent(user.displayName || '');

            const callbackUrl = window.BASE_URL + 'auth/mobile-callback?email=' + email + '&name=' + name;

            const appLinkUrl = window.BASE_URL + 'auth/mobile-callback?email=' + email + '&name=' + name + '&app_return=1';

            window.location.href = appLinkUrl;
        }

        async function doGoogleSignIn() {
            try {
                const app = initializeApp(window.FIREBASE_CONFIG);
                const auth = getAuth(app);
                const provider = new GoogleAuthProvider();
                provider.setCustomParameters({ prompt: 'select_account' });

                await auth.signOut();

                const result = await signInWithPopup(auth, provider);
                handleSuccess(result.user);
            } catch (error) {
                console.error('Google auth error:', error);
                if (error.code === 'auth/popup-blocked') {
                    showError('Popup was blocked. Please allow popups for this site, then tap Try Again.');
                    return;
                }
                if (error.code === 'auth/credential-already-in-use') {
                    const email = error.customData?.email;
                    if (email) {
                        window.location.href = window.BASE_URL + 'auth/mobile-callback?email=' + encodeURIComponent(email) + '&app_return=1';
                        return;
                    }
                }
                showError('Authentication failed: ' + (error.message || 'Unknown error'));
            }
        }

        if (!window.FIREBASE_CONFIG || !window.FIREBASE_CONFIG.apiKey) {
            showError('Firebase is not configured. Please contact support.');
        } else {
            doGoogleSignIn();
        }
    </script>
</body>
</html>
