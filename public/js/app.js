// 1. Import Firebase libraries (Only ONCE at the top)
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js";
import { 
    getAuth, 
    signInWithEmailAndPassword, 
    createUserWithEmailAndPassword, 
    signInWithPopup, 
    GoogleAuthProvider, 
    sendPasswordResetEmail 
} from "https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js";

// 2. Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyDSR0dDw5ETDH4SLpPA7BJT9OOgs2zovCw",
    authDomain: "hahaah-90451.firebaseapp.com",
    projectId: "hahaah-90451",
    storageBucket: "hahaah-90451.firebasestorage.app",
    messagingSenderId: "794371226159",
    appId: "1:794371226159:web:ed82175cca31d7368864da",
    measurementId: "G-P161NP90XW"
};

// 3. Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const googleProvider = new GoogleAuthProvider();

// Force Google to show the "Choose an account" popup
googleProvider.setCustomParameters({
    prompt: 'select_account'
});

// 4. Elements
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');

// 5. Helpers
function isCaptchaChecked() {
    // Check if grecaptcha exists to prevent crashes
    if (typeof grecaptcha === 'undefined') {
        alert("reCAPTCHA is still loading. Please try again in a second.");
        return false;
    }
    const response = grecaptcha.getResponse();
    if (response.length === 0) {
        alert("Please complete the CAPTCHA to verify you are human.");
        return false;
    }
    return true;
}

function handleLoginSuccess(user) {
    localStorage.setItem('mj_user_uid', user.uid); 
    // This redirects to your CodeIgniter dashboard route
    window.location.href = '/dashboard'; 
}

// 6. EVENT LISTENERS

// Email/Password Login
document.getElementById('loginBtn').addEventListener('click', () => {
    if (!isCaptchaChecked()) return;
    const email = emailInput.value;
    const password = passwordInput.value;
    
    signInWithEmailAndPassword(auth, email, password)
        .then((userCredential) => {
            handleLoginSuccess(userCredential.user);
        })
        .catch((error) => alert("Login Error: " + error.message));
});

// Create Account (Signup)
document.getElementById('signupBtn').addEventListener('click', () => {
    if (!isCaptchaChecked()) return;
    const email = emailInput.value;
    const password = passwordInput.value;
    
    createUserWithEmailAndPassword(auth, email, password)
        .then((userCredential) => {
            alert("Account created successfully!");
            handleLoginSuccess(userCredential.user);
        })
        .catch((error) => alert("Signup Error: " + error.message));
});

// Google Sign-In
document.getElementById('googleBtn').addEventListener('click', () => {
    signInWithPopup(auth, googleProvider)
        .then((result) => {
            handleLoginSuccess(result.user);
        })
        .catch((error) => alert("Google Error: " + error.message));
});

// Forgot Password
document.getElementById('forgotPasswordBtn').addEventListener('click', (e) => {
    e.preventDefault(); 
    const email = emailInput.value;
    if (!email) return alert("Please type your email address first.");
    
    sendPasswordResetEmail(auth, email)
        .then(() => {
            alert("Password reset link has been sent to your email!");
        })
        .catch((error) => alert("Error: " + error.message));
});

console.log("Firebase Auth Script Loaded Successfully");