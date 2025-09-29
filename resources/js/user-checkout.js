// resources/js/user-checkout.js
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("checkoutForm");
  if (!form) return;

  const submitBtn   = form.querySelector("button[type='submit']");
  const methodSel   = document.getElementById("payment_method");

  // Card fields & errors
  const cardWrap    = document.getElementById("cardFields");
  const cardInput   = document.getElementById("card_number");
  const expiryInput = document.getElementById("expiry");
  const cvvInput    = document.getElementById("cvv");

  const errCard   = document.getElementById("card_number_error");
  const errExpiry = document.getElementById("expiry_error");
  const errCvv    = document.getElementById("cvv_error");

  // ---------- UI helpers ----------
  function show(el){ if(el){ el.classList.remove("hidden","opacity-50"); } }
  function hide(el){ if(el){ el.classList.add("hidden"); } }
  function dim (el){ if(el){ el.classList.add("opacity-50"); } }
  function undim(el){ if(el){ el.classList.remove("opacity-50"); } }

  function showError(el, errEl) {
    if (!el) return;
    el.classList.add("border-red-500");
    if (errEl) errEl.classList.remove("hidden");
  }
  function clearError(el, errEl) {
    if (!el) return;
    el.classList.remove("border-red-500");
    if (errEl) errEl.classList.add("hidden");
  }
  function setValidity(el, ok, errEl) {
    if (!el) return true;
    if (ok) {
      el.setCustomValidity("");
      clearError(el, errEl);
      return true;
    } else {
      el.setCustomValidity("Invalid");
      showError(el, errEl);
      return false;
    }
  }

  // ---------- Toggle card fields based on method ----------
  function toggleCardFields() {
    const isCard = (methodSel?.value === "card");

    // Show + enable for card; hide + disable for COD
    if (isCard) {
      show(cardWrap); undim(cardWrap);
      [cardInput, expiryInput, cvvInput].forEach(el => {
        if (!el) return;
        el.disabled = false;
      });
    } else {
      if (cardWrap) { dim(cardWrap); hide(cardWrap); }
      [cardInput, expiryInput, cvvInput].forEach(el => {
        if (!el) return;
        el.disabled = true;
        el.value = "";
        el.setCustomValidity("");
      });
      clearError(cardInput, errCard);
      clearError(expiryInput, errExpiry);
      clearError(cvvInput, errCvv);
    }
  }

  // ---------- Input formatting ----------
  cardInput?.addEventListener("input", (e) => {
    let v = e.target.value.replace(/\D/g, "").slice(0, 19);
    e.target.value = v.replace(/(\d{4})(?=\d)/g, "$1 ").trim();
  });

  // ---------- Validation ----------
  function luhnOk(num) {
    let sum = 0, dbl = false;
    for (let i = num.length - 1; i >= 0; i--) {
      let d = num.charCodeAt(i) - 48;
      if (dbl) { d = d * 2; if (d > 9) d -= 9; }
      sum += d; dbl = !dbl;
    }
    return sum % 10 === 0;
  }

  function validateCard() {
    if (methodSel.value !== "card") return true;
    const raw = (cardInput.value || "").replace(/\s+/g, "");
    const okLen  = /^[0-9]{13,19}$/.test(raw);
    const okLuhn = okLen && luhnOk(raw);
    return setValidity(cardInput, okLen && okLuhn, errCard);
  }

  function validateExpiry() {
    if (methodSel.value !== "card") return true;
    const m = /^(\d{2})\/(\d{2})$/.exec((expiryInput.value || "").trim());
    if (!m) return setValidity(expiryInput, false, errExpiry);
    const mm = +m[1], yy = +m[2];
    if (mm < 1 || mm > 12) return setValidity(expiryInput, false, errExpiry);

    const now = new Date();
    const year = 2000 + yy;
    // End of entered month
    const endOfMonth = new Date(year, mm, 0);
    const startOfThisMonth = new Date(now.getFullYear(), now.getMonth(), 1);
    const ok = endOfMonth >= startOfThisMonth;
    return setValidity(expiryInput, ok, errExpiry);
  }

  function validateCvv() {
    if (methodSel.value !== "card") return true;
    const ok = /^[0-9]{3,4}$/.test((cvvInput.value || "").trim());
    return setValidity(cvvInput, ok, errCvv);
  }

  [cardInput, expiryInput, cvvInput].forEach(el => el?.addEventListener("input", () => {
    if (el === cardInput)  validateCard();
    if (el === expiryInput) validateExpiry();
    if (el === cvvInput)    validateCvv();
  }));

  methodSel?.addEventListener("change", toggleCardFields);

  // ---------- Submit handler (validates if Card, always shows overlay) ----------
  form.addEventListener("submit", (e) => {
    // allow second submit (after our delay)
    if (form.dataset.submitting === "1") return;

    // If card, ensure valid first
    if (methodSel.value === "card") {
      const ok = validateCard() && validateExpiry() && validateCvv();
      if (!ok) {
        e.preventDefault();
        // focus first invalid
        if (!validateCard())  { cardInput?.focus();  return; }
        if (!validateExpiry()){ expiryInput?.focus(); return; }
        if (!validateCvv())   { cvvInput?.focus();   return; }
        return;
      }
    }

    // Passed (or COD): show the thank-you overlay and submit
    e.preventDefault();
    form.dataset.submitting = "1";

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.style.opacity = "0.7";
      submitBtn.textContent = "Processing...";
    }

    // remove old overlay if any
    document.getElementById("sw-thankyou-overlay")?.remove();

    // Decide subtext based on method
    const pm = methodSel?.value || "card";
    const subtext = pm === "cod"
      ? "Your order has been placed. Pay on delivery."
      : "We’re securely processing your payment…";

    // full-screen overlay
    const overlay = document.createElement("div");
    overlay.id = "sw-thankyou-overlay";
    overlay.className =
      "fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 backdrop-blur-sm animate-fadeInFast";

    const card = document.createElement("div");
    card.className =
      "mx-4 max-w-md w-full rounded-2xl bg-white shadow-xl border p-6 text-center animate-popIn";

    card.innerHTML = `
      <div class="mx-auto mb-4 h-14 w-14 rounded-full border-4 border-gray-200 border-t-gray-900 animate-spin"></div>
      <h3 class="text-2xl font-extrabold">Thank you for shopping at <span class="whitespace-nowrap">Shoe Walt</span>! 🎉</h3>
      <p class="mt-2 text-gray-600">${subtext}</p>
      <p class="mt-1 text-xs text-gray-500">Do not close this window.</p>
    `;

    overlay.appendChild(card);
    document.body.appendChild(overlay);

    // tiny, scoped animations
    const style = document.createElement("style");
    style.innerHTML = `
      @keyframes fadeInFast { from {opacity:0} to {opacity:1} }
      .animate-fadeInFast { animation: fadeInFast .18s ease-out forwards; }
      @keyframes popIn {
        0% { opacity:0; transform: translateY(8px) scale(.98); }
        100% { opacity:1; transform: translateY(0) scale(1); }
      }
      .animate-popIn { animation: popIn .22s ease-out forwards; }
      @keyframes spin { to { transform: rotate(360deg); } }
      .animate-spin { animation: spin .9s linear infinite; }
    `;
    document.head.appendChild(style);

    // submit after a short pause
    setTimeout(() => form.submit(), 1100);
  });

  // Initialize on load
  toggleCardFields();
});
