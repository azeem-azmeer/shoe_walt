// resources/js/user-checkout.js

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("checkoutForm");
  if (!form) return;

  const submitBtn = form.querySelector("button[type='submit']");

  form.addEventListener("submit", (e) => {
    // allow second submit (after our delay)
    if (form.dataset.submitting === "1") return;

    e.preventDefault();
    form.dataset.submitting = "1";

    // disable button + feedback
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.style.opacity = "0.7";
      submitBtn.textContent = "Processing...";
    }

    // remove old overlay if any
    document.getElementById("sw-thankyou-overlay")?.remove();

    // build full-screen overlay (always visible)
    const overlay = document.createElement("div");
    overlay.id = "sw-thankyou-overlay";
    overlay.className =
      "fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 backdrop-blur-sm animate-fadeInFast";

    const card = document.createElement("div");
    card.className =
      "mx-4 max-w-md w-full rounded-2xl bg-white shadow-xl border p-6 text-center animate-popIn";

    card.innerHTML = `
      <div class="mx-auto mb-3 h-12 w-12 rounded-full border-4 border-gray-200 border-t-gray-800 animate-spin"></div>
      <h3 class="text-xl font-bold">🎉 Thank you for shopping at <span class="whitespace-nowrap">Shoe Walt</span>!</h3>
      <p class="mt-2 text-gray-600">We hope to meet you again soon.<br>Processing your order…</p>
    `;

    overlay.appendChild(card);
    document.body.appendChild(overlay);

    // submit after short pause (tweak if you want longer)
    setTimeout(() => {
      form.submit();
    }, 1200);
  });

  // tiny animations
  const style = document.createElement("style");
  style.innerHTML = `
    @keyframes fadeInFast { from {opacity:0} to {opacity:1} }
    .animate-fadeInFast { animation: fadeInFast .2s ease-out forwards; }
    @keyframes popIn {
      0% { opacity:0; transform: translateY(6px) scale(.98); }
      100% { opacity:1; transform: translateY(0) scale(1); }
    }
    .animate-popIn { animation: popIn .25s ease-out forwards; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .animate-spin { animation: spin .9s linear infinite; }
  `;
  document.head.appendChild(style);
});
