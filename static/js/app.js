// Redway Academy — small client-side helpers only.
// No facial recognition, no biometric data, no tracking.
document.addEventListener("DOMContentLoaded", () => {
  // Add the CSRF token (from the <meta name="csrf-token"> tag in
  // base.html) to every form that does not already have one, so
  // normal browser forms pass the server-side CSRF check.
  const meta = document.querySelector('meta[name="csrf-token"]');
  const token = meta ? meta.getAttribute("content") : "";
  const forms = document.querySelectorAll("form");
  forms.forEach((f) => {
    if (token && !f.querySelector('input[name="csrf_token"]')) {
      const hidden = document.createElement("input");
      hidden.type = "hidden";
      hidden.name = "csrf_token";
      hidden.value = token;
      f.appendChild(hidden);
    }
    f.addEventListener("submit", () => {
      const btn = f.querySelector("button[type=submit]");
      if (btn) btn.disabled = true;
    });
  });
});
