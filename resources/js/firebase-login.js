import { initializeApp } from 'firebase/app';
import {
  getAuth, GoogleAuthProvider, signInWithPopup,
  onAuthStateChanged
} from 'firebase/auth';

const firebaseConfig = {
  apiKey: 'AIzaSyAP8RYrJfuWukYL3lrNhPdW8CknAjeKccM',
  authDomain: 'shoewalt.firebaseapp.com',
  projectId: 'shoewalt',
  storageBucket: 'shoewalt.appspot.com',
  appId: '1:75401815472:web:523625ae070bf8b647e275',
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const provider = new GoogleAuthProvider();

function isRegisterPage() {
  return location.pathname.endsWith('/register') ||
         document.querySelector('form[action*="register"]');
}

function fillRegisterInputs(user) {
  const name  = document.getElementById('name');
  const email = document.getElementById('email');
  if (name && !name.value)   name.value  = user?.displayName || '';
  if (email && !email.value) email.value = user?.email || '';
}

async function postIdToken(idToken) {
  // Debug: inspect ID token payload
  const payload = JSON.parse(atob(idToken.split('.')[1]));
  console.log("🔑 Firebase ID token debug →");
  console.log("iss:", payload.iss);
  console.log("aud:", payload.aud);
  console.log("sub (uid):", payload.sub);
  console.log("email:", payload.email);

  // 1) Ask Sanctum for the CSRF cookie
  await fetch('/sanctum/csrf-cookie', { credentials: 'include' });

  // 2) Read XSRF-TOKEN cookie
  const xsrf = decodeURIComponent(
    (document.cookie.split('; ').find(c => c.startsWith('XSRF-TOKEN=')) || '')
      .split('=')[1] || ''
  );

  return fetch('/api/auth/firebase', {
    method: 'POST',
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json',
      'X-XSRF-TOKEN': xsrf,
    },
    body: JSON.stringify({ idToken, as: 'session' }),
  });
}

document.addEventListener('DOMContentLoaded', () => {
  // REGISTER: just prefill the form
  document.getElementById('googleRegisterBtn')?.addEventListener('click', async () => {
    try {
      const { user } = await signInWithPopup(auth, provider);
      fillRegisterInputs(user);
    } catch (e) {
      console.error('register popup failed', e);
      alert('Google sign-in failed: ' + (e?.message || e));
    }
  });

  // LOGIN: post the ID token to backend
  document.getElementById('googleLoginBtn')?.addEventListener('click', async () => {
    try {
      const { user } = await signInWithPopup(auth, provider);
      const t = await user.getIdToken(true);
      const res = await postIdToken(t);

      if (res.ok || res.status === 204) {
        location.assign('/dashboard');
      } else {
        const data = await res.json().catch(() => ({}));
        throw new Error(data?.detail || data?.message || `Login failed (${res.status})`);
      }
    } catch (e) {
      console.error('login popup failed', e);
      alert('Login failed: ' + (e?.message || e));
    }
  });

  // If already signed in and you open /register, prefill as well
  onAuthStateChanged(auth, (user) => {
    if (user && isRegisterPage()) fillRegisterInputs(user);
  });

  // helpers for debugging in DevTools
  window.firebaseAuth = auth;
  window.dumpIdToken = async () => {
    if (!auth.currentUser) { console.warn('Not signed in'); return null; }
    const t = await auth.currentUser.getIdToken(true);
    const payload = JSON.parse(atob(t.split('.')[1]));
    console.log('idToken length:', t.length);
    console.log('aud:', payload.aud);
    console.log('iss:', payload.iss);
    console.log('sub (uid):', payload.sub);
    console.log('email:', payload.email);
    try { copy(t); } catch {}
    return t;
  };
});
