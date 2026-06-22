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
        .logo-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 24px;
            background: rgba(59, 130, 246, 0.15);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-icon svg {
            width: 32px;
            height: 32px;
            fill: #3b82f6;
        }
        h2 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .subtitle {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.875rem;
            margin-bottom: 32px;
            line-height: 1.4;
        }
        .google-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: #ffffff;
            color: #1f2937;
            font-weight: 600;
            font-size: 0.9375rem;
            padding: 14px 20px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        .google-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
        }
        .google-btn:active {
            transform: scale(0.97);
        }
        .google-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .google-btn svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.15);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border-left-color: #3b82f6;
            animation: spin 0.8s linear infinite;
            margin: 24px auto 12px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        #status {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.7);
            min-height: 1.5em;
        }
        .error-msg {
            color: #f87171;
            font-size: 0.8125rem;
            margin-top: 16px;
            padding: 12px 16px;
            background: rgba(248, 113, 113, 0.1);
            border: 1px solid rgba(248, 113, 113, 0.2);
            border-radius: 10px;
            line-height: 1.4;
        }
        .retry-btn {
            margin-top: 16px;
            padding: 10px 24px;
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
        <div class="logo-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
            </svg>
        </div>
        <h2>TALAbahan Sign-In</h2>
        <p class="subtitle">Tap the button below to sign in with your Google account</p>

        <!-- Sign-in button (visible by default) -->
        <button id="google-btn" class="google-btn" onclick="doGoogleSignIn()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
                <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
                <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
                <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
            </svg>
            Sign in with Google
        </button>

        <!-- Loading state (hidden by default) -->
        <div id="loading-state" class="hidden">
            <div class="spinner"></div>
            <p id="status">Authenticating...</p>
        </div>

        <!-- Error container (hidden by default) -->
        <div id="error-container" class="hidden">
            <div id="error-msg" class="error-msg"></div>
            <button class="retry-btn" onclick="resetAndRetry()">Try Again</button>
        </div>
    </div>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
        import { getAuth, GoogleAuthProvider, signInWithPopup } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";

        const googleBtn = document.getElementById('google-btn');
        const loadingState = document.getElementById('loading-state');
        const statusEl = document.getElementById('status');
        const errorContainer = document.getElementById('error-container');
        const errorMsg = document.getElementById('error-msg');

        let auth = null;
        let provider = null;

        // Initialize Firebase immediately
        if (window.FIREBASE_CONFIG && window.FIREBASE_CONFIG.apiKey) {
            const app = initializeApp(window.FIREBASE_CONFIG);
            auth = getAuth(app);
            provider = new GoogleAuthProvider();
            provider.setCustomParameters({ prompt: 'select_account' });
        } else {
            showError('Firebase is not configured. Please contact support.');
            googleBtn.classList.add('hidden');
        }

        function showLoading(msg) {
            googleBtn.classList.add('hidden');
            errorContainer.classList.add('hidden');
            loadingState.classList.remove('hidden');
            statusEl.textContent = msg || 'Authenticating...';
        }

        function showError(msg) {
            googleBtn.classList.add('hidden');
            loadingState.classList.add('hidden');
            errorContainer.classList.remove('hidden');
            errorMsg.textContent = msg;
        }

        function handleSuccess(user) {
            showLoading('Verifying account...');
            const email = encodeURIComponent(user.email);
            const name = encodeURIComponent(user.displayName || '');
            const callbackUrl = window.BASE_URL + 'auth/mobile-callback?email=' + email + '&name=' + name;
            window.location.href = 'talabahan://auth?redirect=' + encodeURIComponent(callbackUrl);
        }

        // Exposed to onclick handler
        window.doGoogleSignIn = async function() {
            if (!auth || !provider) {
                showError('Firebase is not configured.');
                return;
            }

            showLoading('Opening Google Sign-In...');

            try {
                const result = await signInWithPopup(auth, provider);
                if (result && result.user) {
                    handleSuccess(result.user);
                } else {
                    showError('Sign-in did not return a user. Please try again.');
                }
            } catch (err) {
                console.error('Google sign-in error:', err);

                if (err.code === 'auth/popup-closed-by-user') {
                    // User closed the popup — just show the button again
                    googleBtn.classList.remove('hidden');
                    loadingState.classList.add('hidden');
                    errorContainer.classList.add('hidden');
                } else if (err.code === 'auth/popup-blocked') {
                    showError('Popup was blocked by your browser. Please allow popups for this site and try again.');
                } else if (err.code === 'auth/cancelled-popup-request') {
                    // Multiple popup requests — show button again
                    googleBtn.classList.remove('hidden');
                    loadingState.classList.add('hidden');
                } else {
                    showError('Sign-in failed: ' + (err.message || 'Unknown error. Please try again.'));
                }
            }
        };

        window.resetAndRetry = function() {
            errorContainer.classList.add('hidden');
            loadingState.classList.add('hidden');
            googleBtn.classList.remove('hidden');
        };
    </script>
</body>
</html>
