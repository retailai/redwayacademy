// Redway Academy — small client-side helpers only.
// No facial recognition, no biometric data, no tracking.

// Theme: apply the saved choice as early as possible (default light).
(function () {
  try {
    var saved = localStorage.getItem("rw_theme");
    if (saved === "dark" || saved === "light") {
      document.documentElement.setAttribute("data-theme", saved);
    }
  } catch (e) { /* private mode: stay on the default light theme */ }
})();

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

  // Dark / light theme toggle — persisted in localStorage, default light.
  // Two buttons: #themeToggle in the header, #themeToggle2 in the drawer.
  // Both toggle data-theme + localStorage "rw_theme" and stay in sync.
  const themeBtns = [document.getElementById("themeToggle"), document.getElementById("themeToggle2")].filter(Boolean);
  const applyThemeIcon = () => {
    const dark = document.documentElement.getAttribute("data-theme") === "dark";
    themeBtns.forEach((b) => { b.textContent = dark ? "☀" : "◐"; });
  };
  applyThemeIcon();
  themeBtns.forEach((b) => {
    b.addEventListener("click", () => {
      const next = document.documentElement.getAttribute("data-theme") === "dark" ? "light" : "dark";
      document.documentElement.setAttribute("data-theme", next);
      try { localStorage.setItem("rw_theme", next); } catch (e) { /* ignore */ }
      applyThemeIcon();
    });
  });

  // Mobile drawer (burger menu).
  const burger = document.getElementById("burgerBtn");
  const drawer = document.getElementById("drawer");
  const scrim = document.getElementById("drawerScrim");
  const drawerClose = document.getElementById("drawerClose");
  const setDrawer = (open) => {
    if (!drawer || !scrim) return;
    drawer.classList.toggle("open", open);
    scrim.classList.toggle("open", open);
    if (burger) burger.setAttribute("aria-expanded", open ? "true" : "false");
    document.body.style.overflow = open ? "hidden" : "";
  };
  if (burger) burger.addEventListener("click", () => setDrawer(!drawer.classList.contains("open")));
  if (drawerClose) drawerClose.addEventListener("click", () => setDrawer(false));
  if (scrim) scrim.addEventListener("click", () => setDrawer(false));
  if (drawer) drawer.querySelectorAll("a").forEach((a) => a.addEventListener("click", () => setDrawer(false)));

  // "আরও ▾" popover in the pill nav.
  const moreMenu = document.getElementById("moreMenu");
  const moreBtn = document.getElementById("moreBtn");
  if (moreMenu && moreBtn) {
    moreBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      const open = moreMenu.classList.toggle("open");
      moreBtn.setAttribute("aria-expanded", open ? "true" : "false");
    });
    document.addEventListener("click", () => {
      moreMenu.classList.remove("open");
      moreBtn.setAttribute("aria-expanded", "false");
    });
  }

  // "Coming soon" toast for links/buttons that have no real page yet.
  let toastTimer = null;
  const showToast = (msg) => {
    let toast = document.querySelector(".toast");
    if (!toast) {
      toast = document.createElement("div");
      toast.className = "toast";
      toast.setAttribute("role", "status");
      document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.style.display = "block";
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { toast.style.display = "none"; }, 2600);
  };
  const soonMsg = document.body.getAttribute("data-soon-msg") || "Coming soon.";
  document.querySelectorAll("[data-soon]").forEach((el) => {
    el.addEventListener("click", (e) => {
      e.preventDefault();
      showToast(soonMsg);
    });
  });

  // Lesson player: mobile lesson-list toggle.
  const listToggle = document.getElementById("lessonListToggle");
  const playerSide = document.getElementById("playerSide");
  if (listToggle && playerSide) {
    listToggle.addEventListener("click", () => {
      const hidden = playerSide.classList.toggle("collapsed");
      listToggle.setAttribute("aria-expanded", hidden ? "false" : "true");
      const show = listToggle.getAttribute("data-show") || "Show lesson list";
      const hide = listToggle.getAttribute("data-hide") || "Hide lesson list";
      listToggle.textContent = hidden ? show : hide;
      if (!hidden) playerSide.scrollIntoView({ behavior: "smooth", block: "start" });
    });
  }

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

  // Accordions (syllabus modules, curriculum, lesson note / rating).
  document.querySelectorAll("[data-acc]").forEach((head) => {
    head.addEventListener("click", () => {
      const box = head.closest(".module, .acc");
      if (box) {
        const open = box.classList.toggle("open");
        head.setAttribute("aria-expanded", open ? "true" : "false");
        const sign = head.querySelector("span[aria-hidden]");
        if (sign) sign.textContent = open ? "−" : "+";
      }
    });
  });

  // Register form: role-conditional fields (progressive disclosure).
  // Student: class only. Parent: student name + class + bKash payment no;
  // the Trxn ID field appears only after a valid 13-digit bKash number
  // starting with 01. Teacher: none of them. Server-side validation is
  // the real gate — this only shows/hides the fields.
  var regForm = document.getElementById("registerForm");
  if (regForm) {
    var roleEl = document.getElementById("role");
    var fieldClass = document.getElementById("fieldClass");
    var fieldStudent = document.getElementById("fieldStudentName");
    var fieldBkash = document.getElementById("fieldBkash");
    var fieldTrxn = document.getElementById("fieldTrxn");
    var classEl = document.getElementById("class_level");
    var studentEl = document.getElementById("student_name");
    var bkashEl = document.getElementById("bkash_payment_no");
    var trxnEl = document.getElementById("payment_trxn_id");
    var BKASH_RE = /^01\d{11}$/;
    var digitsOnly = function (v) { return (v || "").replace(/\D/g, ""); };
    var syncRegister = function () {
      var role = roleEl ? roleEl.value : "";
      var isStudent = role === "student";
      var isParent = role === "parent";
      if (fieldClass) fieldClass.hidden = !(isStudent || isParent);
      if (classEl) classEl.required = isStudent || isParent;
      if (fieldStudent) fieldStudent.hidden = !isParent;
      if (studentEl) studentEl.required = isParent;
      if (fieldBkash) fieldBkash.hidden = !isParent;
      if (bkashEl) bkashEl.required = isParent;
      var bkashOk = isParent && bkashEl && BKASH_RE.test(digitsOnly(bkashEl.value));
      if (fieldTrxn) fieldTrxn.hidden = !bkashOk;
      if (trxnEl) trxnEl.required = !!bkashOk;
    };
    if (roleEl) roleEl.addEventListener("change", syncRegister);
    if (bkashEl) bkashEl.addEventListener("input", syncRegister);
    syncRegister();
  }
});
