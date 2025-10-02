import { getAuth, signOut } from "firebase/auth";

const auth = getAuth();

//  Expose globally so Blade/Alpine can call it
window.firebaseLogout = function () {
    return signOut(auth)
        .then(() => {
            console.log("✅ Firebase user signed out");
        })
        .catch((error) => {
            console.error("❌ Firebase sign-out failed", error);
        });
};
