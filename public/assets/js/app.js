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

  // Compact mobile header: shrink to a mini-bar while scrolling (the
  // search row hides, the logo gets smaller) — content starts right below.
  const header = document.getElementById("siteHeader");
  if (header) {
    const onScroll = () => header.classList.toggle("scrolled", window.scrollY > 60);
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
  }

  // Dismissible announcement strip — remembered for a year.
  const closeBtn = document.getElementById("announceClose");
  const bar = document.getElementById("announceBar");
  if (closeBtn && bar) {
    closeBtn.addEventListener("click", () => {
      bar.remove();
      const d = new Date();
      d.setTime(d.getTime() + 365 * 24 * 60 * 60 * 1000);
      document.cookie = "rw_announce_hide=1; expires=" + d.toUTCString() + "; path=/; SameSite=Lax";
    });
  }

  // Buy page: switching between Monthly / One-time updates the amount shown
  // in the payment steps and on the submit button.
  const buyForm = document.getElementById("buyForm");
  if (buyForm) {
    const bnDigits = (s) => document.documentElement.lang === "bn"
      ? String(s).replace(/[0-9]/g, (d) => "০১২৩৪৫৬৭৮৯"[Number(d)]) : String(s);
    const fmt = (n) => "৳" + bnDigits(n);
    const amountEl = document.getElementById("payAmount");
    const submitBtn = document.getElementById("paySubmit");
    buyForm.querySelectorAll('input[name="plan"]').forEach((radio) => {
      radio.addEventListener("change", () => {
        const amount = Number(radio.getAttribute("data-amount") || 0);
        if (amountEl) amountEl.textContent = fmt(amount);
        if (submitBtn) {
          const tpl = submitBtn.getAttribute("data-label-format") || "Submit payment (%s)";
          submitBtn.textContent = tpl.replace("%s", fmt(amount));
        }
        buyForm.querySelectorAll(".plan-option").forEach((opt) => {
          const r = opt.querySelector('input[name="plan"]');
          opt.classList.toggle("selected", !!(r && r.checked));
        });
      });
    });
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
