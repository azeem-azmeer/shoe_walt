import { initializeApp } from 'firebase/app';
import {
  getAuth,
  GoogleAuthProvider,
  signInWithPopup,
  signInWithRedirect,
  getRedirectResult,
  onAuthStateChanged,
} from 'firebase/auth';

/* -------------------- Firebase Web Config (shoewalt) -------------------- */
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

/* ----------------------------- Utilities -------------------------------- */
const KEY_LOGIN_PENDING = 'gLoginPending';
const KEY_REGISTER_PENDING = 'gRegisterPending';

function isRegisterPage() {
  return (
    location.pathname.endsWith('/register') ||
    !!document.querySelector('form[action*="register"]')
  );
}

function fillRegisterInputs(user) {
  const name = document.getElementById('name');
  const email = document.getElementById('email');
  if (name && !name.value) name.value = user?.displayName || '';
  if (email && !email.value) email.value = user?.email || '';
}

function getCookie(name) {
  const part = document.cookie.split('; ').find(c => c.startsWith(name + '='));
  if (!part) return '';
  try {
    return decodeURIComponent(part.split('=').slice(1).join('='));
  } catch {
    return '';
  }
}

async function postIdToken(idToken) {
  // Debug the token claims (helps confirm correct Firebase project)
  try {
    const payload = JSON.parse(atob(idToken.split('.')[1] || ''));
    console.log('🔑 Firebase ID token debug →');
    console.log('iss:', payload.iss);
    console.log('aud:', payload.aud);
    console.log('sub (uid):', payload.sub);
    console.log('email:', payload.email);
  } catch (_) {}

  // 1) Get Sanctum CSRF cookie
  await fetch('/sanctum/csrf-cookie', { credentials: 'include' });

  // 2) Read XSRF token and call your backend
  const xsrf = getCookie('XSRF-TOKEN');

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

async function loginWithUser(user) {
  const token = await user.getIdToken(true);
  const res = await postIdToken(token);
  if (res.ok || res.status === 204) {
    location.assign('/dashboard');
  } else {
    let detail = '';
    try {
      const data = await res.json();
      detail = data?.reason || data?.message || '';
    } catch {}
    throw new Error(detail || `Login failed (${res.status})`);
  }
}

/**
 * Try popup first; if blocked/closed, fall back to redirect.
 * After redirect, handle the result in `handleRedirectResult()`.
 */
async function googleSignIn(flow /* 'login' | 'register' */) {
  try {
    const { user } = await signInWithPopup(auth, provider);
    return user;
  } catch (e) {
    const code = e?.code || '';
    // Common popup problems
    if (
      code === 'auth/popup-blocked' ||
      code === 'auth/popup-closed-by-user' ||
      code === 'auth/cancelled-popup-request'
    ) {
      sessionStorage.setItem(
        flow === 'login' ? KEY_LOGIN_PENDING : KEY_REGISTER_PENDING,
        '1'
      );
      await signInWithRedirect(auth, provider);
      // The page will redirect away; result is handled on return.
      return null;
    }
    throw e;
  }
}

/** Handle the result after returning from `signInWithRedirect` */
async function handleRedirectResult() {
  try {
    const res = await getRedirectResult(auth);
    if (!res?.user) return;

    // Determine which flow was pending
    const loginPending = sessionStorage.getItem(KEY_LOGIN_PENDING) === '1';
    const registerPending = sessionStorage.getItem(KEY_REGISTER_PENDING) === '1';

    // Clear flags so we don't loop
    sessionStorage.removeItem(KEY_LOGIN_PENDING);
    sessionStorage.removeItem(KEY_REGISTER_PENDING);

    if (registerPending) {
      // Prefill the register form after redirect
      if (isRegisterPage()) fillRegisterInputs(res.user);
      return;
    }

    if (loginPending) {
      await loginWithUser(res.user);
    }
  } catch (e) {
    // It’s OK if there’s no redirect result; just ignore
    if (e?.message) console.warn('Redirect result error:', e.message);
  }
}

/* ------------------------------ Bind UI --------------------------------- */
document.addEventListener('DOMContentLoaded', () => {
  // Handle redirect results first (in case we just returned)
  handleRedirectResult();

  // Register: just prefill
  document.getElementById('googleRegisterBtn')?.addEventListener('click', async () => {
    try {
      const user = await googleSignIn('register');
      if (user) fillRegisterInputs(user); // popup path
    } catch (e) {
      console.error('register popup failed', e);
      alert('Google sign-in failed: ' + (e?.message || e));
    }
  });

  // Login: post ID token to backend
  document.getElementById('googleLoginBtn')?.addEventListener('click', async () => {
    try {
      const user = await googleSignIn('login');
      if (user) await loginWithUser(user); // popup path
    } catch (e) {
      console.error('login popup failed', e);
      alert('Login failed: ' + (e?.message || e));
    }
  });

  // If already signed in and you open /register, prefill as well
  onAuthStateChanged(auth, (user) => {
    if (user && isRegisterPage()) fillRegisterInputs(user);
  });

  // Expose helpers for debugging in DevTools (optional)
  window.firebaseAuth = auth;
  window.dumpIdToken = async () => {
    if (!auth.currentUser) {
      console.warn('Not signed in');
      return null;
    }
    const t = await auth.currentUser.getIdToken(true);
    try {
      const payload = JSON.parse(atob(t.split('.')[1] || ''));
      console.log('idToken length:', t.length);
      console.log('aud:', payload.aud);
      console.log('iss:', payload.iss);
      console.log('sub (uid):', payload.sub);
      console.log('email:', payload.email);
    } catch {}
    try {
      // copy to clipboard in supported browsers
      // eslint-disable-next-line no-undef
      copy?.(t);
    } catch {}
    return t;
  };
});
