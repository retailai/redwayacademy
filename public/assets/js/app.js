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

  // Avatar dropdown.
  const menu = document.getElementById("avatarMenu");
  const btn = document.getElementById("avatarBtn");
  if (menu && btn) {
    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      const open = menu.classList.toggle("open");
      btn.setAttribute("aria-expanded", open ? "true" : "false");
    });
    document.addEventListener("click", () => {
      menu.classList.remove("open");
      btn.setAttribute("aria-expanded", "false");
    });
  }

  // Back to top.
  const top = document.getElementById("backTop");
  if (top) {
    const onScroll = () => top.classList.toggle("show", window.scrollY > 400);
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
    top.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));
  }

  // Carousels: prev/next scroll buttons.
  document.querySelectorAll("[data-carousel]").forEach((track) => {
    const wrap = track.closest(".carousel-wrap");
    if (!wrap) return;
    const step = () => Math.min(track.clientWidth * 0.8, 380);
    wrap.querySelectorAll("[data-carousel-prev]").forEach((b) =>
      b.addEventListener("click", () => track.scrollBy({ left: -step(), behavior: "smooth" })));
    wrap.querySelectorAll("[data-carousel-next]").forEach((b) =>
      b.addEventListener("click", () => track.scrollBy({ left: step(), behavior: "smooth" })));
  });

  // Accordions (syllabus modules, curriculum).
  document.querySelectorAll("[data-acc]").forEach((head) => {
    head.addEventListener("click", () => {
      const box = head.closest(".module, .acc");
      if (box) box.classList.toggle("open");
    });
  });
});
