<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Sign-In</title>
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
        .container {
            max-width: 360px;
            width: 100%;
        }
        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.15);
            width: 44px;
            height: 44px;
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
        <!-- Action state (visible by default) -->
        <div id="action-state">
            <h2 style="margin-bottom: 10px;">TALAbahan Sign-In</h2>
            <p style="margin-bottom: 20px; font-size: 0.9rem; color: rgba(255, 255, 255, 0.8);">
                Please confirm your sign-in to securely link your Google account to the app.
            </p>
            <button class="retry-btn" onclick="doGoogleSignIn()" style="display: flex; align-items: center; justify-content: center; width: 100%; gap: 10px; background: white; color: #333;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="24px" height="24px">
                    <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
                    <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
                    <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
                    <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
                </svg>
                <span style="font-weight: 600;">Continue with Google</span>
            </button>
        </div>

        <!-- Loading state (hidden by default) -->
        <div id="loading-state" class="hidden">
            <div class="spinner"></div>
            <p id="status">Connecting to Google...</p>
        </div>

        <!-- Error container (hidden by default) -->
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

        const actionState = document.getElementById('action-state');
        const statusEl = document.getElementById('status');
        const loadingState = document.getElementById('loading-state');
        const errorContainer = document.getElementById('error-container');
        const errorMsg = document.getElementById('error-msg');

        window.retrySignIn = function() {
            errorContainer.classList.add('hidden');
            loadingState.classList.add('hidden');
            actionState.classList.remove('hidden');
        };

        function showError(msg) {
            loadingState.classList.add('hidden');
            actionState.classList.add('hidden');
            errorContainer.classList.remove('hidden');
            errorMsg.textContent = msg;
        }

        function handleSuccess(user) {
            statusEl.textContent = 'Redirecting back to app...';
            const email = encodeURIComponent(user.email);
            const name = encodeURIComponent(user.displayName || '');
            const callbackUrl = window.BASE_URL + 'auth/mobile-callback?email=' + email + '&name=' + name;
            window.location.href = 'talabahan://auth?redirect=' + encodeURIComponent(callbackUrl);
        }

        if (!window.FIREBASE_CONFIG || !window.FIREBASE_CONFIG.apiKey) {
            showError('Firebase is not configured. Please contact support.');
        } else {
            const app = initializeApp(window.FIREBASE_CONFIG);
            const auth = getAuth(app);
            const provider = new GoogleAuthProvider();
            // Force account picker to bypass any cached sessions
            provider.setCustomParameters({ prompt: 'select_account' });

            window.doGoogleSignIn = async function() {
                actionState.classList.add('hidden');
                errorContainer.classList.add('hidden');
                loadingState.classList.remove('hidden');
                statusEl.textContent = 'Opening Google Sign-In...';

                try {
                    // Sign out to clear any old cached session
                    await auth.signOut();
                    
                    // Use Popup because Redirect is blocked by Chrome third-party cookies on Android
                    const result = await signInWithPopup(auth, provider);
                    if (result && result.user) {
                        handleSuccess(result.user);
                    } else {
                        showError('Sign-in did not return a user. Please try again.');
                    }
                } catch (error) {
                    console.error('Google auth error:', error);
                    if (error.code === 'auth/popup-closed-by-user') {
                        // User cancelled the popup, go back to main screen
                        retrySignIn();
                    } else {
                        showError('Authentication failed: ' + (error.message || 'Unknown error'));
                    }
                }
            };
        }
    </script>
</body>
</html>
