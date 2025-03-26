// Firebase references (since we're using the CDN, no need for imports)
const auth = firebase.auth();
const db = firebase.firestore();

// Register User
document.getElementById("register-button").addEventListener("click", function() {
    const email = document.getElementById("register-email").value;
    const password = document.getElementById("register-password").value;
    
    auth.createUserWithEmailAndPassword(email, password)
        .then(userCredential => {
            alert("User registered successfully!");
        })
        .catch(error => {
            alert(error.message);
        });
});

// Login User
document.getElementById("login-button").addEventListener("click", function() {
    const email = document.getElementById("login-email").value;
    const password = document.getElementById("login-password").value;

    auth.signInWithEmailAndPassword(email, password)
        .then(userCredential => {
            alert("Login successful!");
            window.location.href = "dashboard.html"; // Redirect after login
        })
        .catch(error => {
            alert(error.message);
        });
});

// Google Sign-In Function
document.getElementById("google-login").addEventListener("click", async () => {
    try {
        const result = await auth.signInWithPopup(provider);
        const user = result.user;

        // Check if user exists in Firestore
        const userRef = db.collection("users").doc(user.uid);
        const userSnap = await userRef.get();

        if (!userSnap.exists) {
            // Save new user in Firestore
            await userRef.set({
                name: user.displayName,
                email: user.email,
                profilePic: user.photoURL,
                uid: user.uid
            });
        }

        alert(`Welcome, ${user.displayName}!`);
        window.location.href = "dashboard.html"; // Redirect after login
    } catch (error) {
        console.error("Error signing in:", error.message);
        alert("Google sign-in failed. Try again.");
    }
});

// Facebook Login
document.getElementById("facebook-login").addEventListener("click", function() {
    const fbProvider = new firebase.auth.FacebookAuthProvider();
    auth.signInWithPopup(fbProvider)
        .then(userCredential => {
            alert("Facebook login successful!");
        })
        .catch(error => {
            alert(error.message);
        });
});

// Logout Function
document.getElementById("logout-button").addEventListener("click", async () => {
    try {
        await auth.signOut();
        alert("Logged out successfully!");
        window.location.href = "index.html"; // Redirect to home after logout
    } catch (error) {
        console.error("Logout error:", error.message);
    }
});

// Track authentication state
auth.onAuthStateChanged(user => {
    if (user) {
        console.log("✅ User is logged in:", user.uid);
    } else {
        console.log("❌ No user is logged in.");
    }
});
