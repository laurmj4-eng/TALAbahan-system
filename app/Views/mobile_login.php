<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in with Google</title>
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
        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 14px 24px;
            background: white;
            color: #1f2937;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s, box-shadow 0.15s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        }
        .google-btn:hover { background: #f8fafc; box-shadow: 0 2px 8px rgba(0,0,0,0.18); }
        .google-btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .google-btn svg { width: 22px; height: 22px; flex-shrink: 0; }
        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.15);
            width: 22px; height: 22px;
            border-radius: 50%;
            border-left-color: white;
            animation: spin 0.8s linear infinite;
            display: none;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        #status {
            font-size: 0.9375rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 20px;
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
        .hidden { display: none !important; }
        .heading { font-size: 1.25rem; font-weight: 600; margin-bottom: 8px; }
        .sub { color: rgba(255,255,255,0.6); font-size: 0.875rem; margin-bottom: 28px; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="container">
        <div id="initial-state">
            <div style="width:64px;height:64px;border-radius:50%;background:#1e293b;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:30px">🐟</div>
            <div class="heading">TALAbahan Seafood</div>
            <div class="sub">Sign in with your Google account</div>
            <button class="google-btn" id="signInBtn">
                <svg viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3c-2.5 6.9-9.1 12-17.3 12-9.9 0-18-8.1-18-18s8.1-18 18-18c4.6 0 8.8 1.7 12 4.6l5.7-5.7C32.7 0 28.6-2 24-2 10.7-2 0 9.6 0 22s10.7 24 24 24c11.4 0 21.3-7.9 23.6-18.7.3-1.5.4-3 .4-4.5 0-1.5-.2-3-.4-4.5z"/><path fill="#FF3D00" d="M4.4 13.5l6.6 4.8C13.1 14 17.9 11 24 11c3.1 0 5.9 1.1 8.1 2.9l6.1-6.1C34.6 4.1 29.6 2 24 2 16.3 2 9.5 6.5 5.9 13.1l-1.5.4z"/><path fill="#4CAF50" d="M24 44c5.7 0 10.9-2.1 14.8-5.6l-6.8-5.5c-2.1 1.5-4.8 2.4-8 2.4-6.2 0-11.4-4.2-13.3-9.9l-6.6 5.1C7.5 38.3 15.1 44 24 44z"/><path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-1.2 3.3-3.5 6.1-6.6 7.8.1 0 6.7 5.4 6.7 5.4 4.9-4.5 8.2-11.1 8.2-19.5 0-1.5-.1-2.9-.3-4.3z"/></svg>
                <span id="btnText">Sign in with Google</span>
                <div class="spinner" id="btnSpinner"></div>
            </button>
            <div id="status" class="hidden"></div>
        </div>

        <div id="error-container" class="hidden">
            <div id="error-msg" class="error-box"></div>
            <button class="google-btn" style="margin-top:16px;" onclick="retrySignIn()">
                <span>Try Again</span>
            </button>
        </div>
    </div>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
        import {
            getAuth,
            GoogleAuthProvider,
            signInWithPopup
        } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";

        const btn = document.getElementById('signInBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');
        const statusEl = document.getElementById('status');
        const initialState = document.getElementById('initial-state');
        const errorContainer = document.getElementById('error-container');
        const errorMsg = document.getElementById('error-msg');

        function setLoading(loading) {
            btn.disabled = loading;
            btnText.style.display = loading ? 'none' : 'inline';
            btnSpinner.style.display = loading ? 'block' : 'none';
        }

        window.retrySignIn = function() {
            errorContainer.classList.add('hidden');
            initialState.classList.remove('hidden');
            statusEl.classList.add('hidden');
            btnText.textContent = 'Sign in with Google';
            setLoading(false);
        };

        function showError(msg) {
            initialState.classList.add('hidden');
            errorContainer.classList.remove('hidden');
            errorMsg.textContent = msg;
        }

        async function doGoogleSignIn() {
            if (!window.FIREBASE_CONFIG || !window.FIREBASE_CONFIG.apiKey) {
                showError('Firebase is not configured. Please contact support.');
                return;
            }

            setLoading(true);
            statusEl.textContent = 'Opening Google sign-in...';
            statusEl.classList.remove('hidden');

            try {
                const app = initializeApp(window.FIREBASE_CONFIG);
                const auth = getAuth(app);
                const provider = new GoogleAuthProvider();
                provider.setCustomParameters({ prompt: 'select_account' });

                await auth.signOut();

                const result = await signInWithPopup(auth, provider);
                statusEl.textContent = 'Sign-in successful! Redirecting...';

                const email = encodeURIComponent(result.user.email);
                const name = encodeURIComponent(result.user.displayName || '');
                window.location.href = window.BASE_URL + 'auth/mobile-callback?email=' + email + '&name=' + name + '&app_return=1';
            } catch (error) {
                console.error('Google auth error:', error);
                setLoading(false);
                statusEl.classList.add('hidden');

                if (error.code === 'auth/popup-blocked') {
                    showError('Popup was blocked. Tap the button again to allow it.');
                    btnText.textContent = 'Sign in with Google';
                    return;
                }
                if (error.code === 'auth/credential-already-in-use') {
                    const email = error.customData?.email;
                    if (email) {
                        window.location.href = window.BASE_URL + 'auth/mobile-callback?email=' + encodeURIComponent(email) + '&app_return=1';
                        return;
                    }
                }
                showError('Sign-in failed: ' + (error.message || 'Please try again.'));
            }
        }

        btn.addEventListener('click', doGoogleSignIn);
    </script>
</body>
</html>
