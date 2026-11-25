// Redway Academy — small client-side helpers only.
// No facial recognition, no biometric data, no tracking.
document.addEventListener("DOMContentLoaded", () => {
  const meta = document.querySelector('meta[name="csrf-token"]');
  const token = meta ? meta.getAttribute("content") : "";
  document.querySelectorAll("form").forEach((f) => {
    if (token && !f.querySelector('input[name="csrf_token"]')) {
      const hidden = document.createElement("input");
      hidden.type = "hidden"; hidden.name = "csrf_token"; hidden.value = token;
      f.appendChild(hidden);
    }
    f.addEventListener("submit", () => {
      const btn = f.querySelector("button[type=submit]");
      if (btn) btn.disabled = true;
    });
  });
});
