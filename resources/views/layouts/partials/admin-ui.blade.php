{{-- resources/views/layouts/partials/admin-ui.blade.php --}}
{{-- Global CSS untuk standarisasi semua halaman admin (#a77d52 + white) --}}
<style>
  /* ============================================================
     ADMIN GLOBAL UI — Human.Careers
     Palet: #a77d52 (accent) · #5c3d1e (dark) · #fff (white)
  ============================================================ */

  /* ===== 0. DESIGN TOKENS (shared header system) ===== */
  :root {
    --primary-brown: #a77d52;
    --primary-brown-dark: #8b5e3c;
    --surface: #ffffff;
    --border: #ede4dc;
    --text-primary: #5c3d1e;
    --text-muted: #6b7280;
    --radius-lg: 1.5rem;    /* 24px — page header */
    --radius-md: 1rem;      /* 16px — filter card */
    --shadow-sm: 0 1px 2px rgba(48,31,15,.06), 0 2px 8px rgba(48,31,15,.05);
  }

  /* ===== 0b. PAGE CONTAINER (standard for semua halaman) ===== */
  .page-container {
    width: 100%;
    max-width: 1440px;
    margin-inline: auto;
    padding-inline: 1rem;
    padding-block: 1.5rem;
  }
  @media (min-width: 640px) { .page-container { padding-inline: 1.5rem; } }
  @media (min-width: 1024px){ .page-container { padding-inline: 2rem; } }

  /* ===== 0c. SHARED PAGE HEADER (source of truth) ===== */
  .page-header {
    background-color: var(--primary-brown) !important;
    color: #fff !important;
    border-radius: var(--radius-lg) !important;
    padding: 26px 30px !important;
    box-shadow: var(--shadow-sm) !important;
    margin-bottom: 28px !important;
  }
  .page-header__inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.25rem;
    width: 100%;
  }
  .page-header__copy {
    min-width: 0;
    display: flex;
    flex-direction: column;
  }
  .page-header__eyebrow {
    margin: 0 0 6px !important;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .32em;
    text-transform: uppercase;
    color: rgba(255,255,255,.78) !important;
  }
  .page-header__title {
    margin: 0 !important;
    font-size: 31px;
    font-weight: 700;
    line-height: 1.15;
    letter-spacing: -.01em;
    color: #fff !important;
  }
  .page-header__desc {
    margin: 7px 0 0 !important;
    font-size: 15.5px;
    line-height: 1.45;
    color: rgba(255,255,255,.92) !important;
    max-width: 62ch;
  }
  .page-header__actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: .75rem;
    flex-shrink: 0;
  }
  /* Tombol aksi di dalam header — putih, teks gelap, sejajar kanan */
  .page-header__actions .ph-action,
  .page-header a.ph-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    min-height: 44px;
    padding: .625rem 1.25rem;
    border-radius: 12px;
    background-color: #fff !important;
    color: #5c3d1e !important;
    font-size: .9rem;
    font-weight: 700;
    white-space: nowrap;
    text-decoration: none;
    border: none !important;
    box-shadow: 0 1px 2px rgba(48,31,15,.12);
    transition: background-color .15s, box-shadow .15s, transform .1s !important;
  }
  .page-header__actions .ph-action:hover,
  .page-header a.ph-action:hover { background-color: #f7efe1 !important; box-shadow: 0 3px 10px rgba(48,31,15,.18); }
  .page-header__actions .ph-action:active { transform: translateY(1px); }
  .page-header__actions .ph-action svg { width: 1.05rem; height: 1.05rem; flex-shrink: 0; }
  /* Meta info kanan (mis. Last Updated) */
  .page-header__meta { text-align: right; color: rgba(255,255,255,.92) !important; }
  .page-header__meta .label { margin: 0; font-size: 11px; letter-spacing: .14em; text-transform: uppercase; color: rgba(255,255,255,.7) !important; }
  .page-header__meta .value { margin: 2px 0 0; font-size: 14px; font-weight: 600; color: #fff !important; }

  @media (max-width: 767px) {
    .page-header { padding: 22px 20px !important; border-radius: 18px !important; margin-bottom: 22px !important; }
    .page-header__inner { flex-direction: column; align-items: stretch; gap: 1rem; }
    .page-header__title { font-size: 26px; }
    .page-header__desc { font-size: 14px; }
    .page-header__actions { justify-content: flex-start; }
    .page-header__actions .ph-action { width: 100%; }
  }

  /* ===== 1. FILTER / SEARCH BUTTON ===== */
  /* Ganti semua tombol hitam/biru filter → #a77d52 */
  button[style*="background-color:#0f172a"],
  button[style*="background:#0f172a"],
  a[style*="background-color:#0f172a"],
  .btn-filter, .filter-btn {
    background-color: #a77d52 !important;
    border-color: #a77d52 !important;
    color: #fff !important;
  }
  button[style*="background-color:#0f172a"]:hover,
  button[style*="background:#0f172a"]:hover { opacity: .88 !important; }

  /* Input & select focus ring */
  input:focus, select:focus, textarea:focus {
    outline: none !important;
    border-color: #a77d52 !important;
    box-shadow: 0 0 0 3px rgba(167,125,82,.15) !important;
  }

  /* ===== 1b. BASE BUTTON ADMIN `.abtn` (batch admin; tidak mengganggu `.btn` logout) ===== */
  .abtn {
    display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
    font-size: .875rem; font-weight: 600; line-height: 1;
    padding: .625rem 1rem; border-radius: .625rem; cursor: pointer;
    transition: background-color .15s, border-color .15s, box-shadow .15s, opacity .15s !important;
  }
  .abtn-sm { font-size: .8125rem; padding: .4375rem .75rem; border-radius: .625rem; }
  .abtn-xs { font-size: .75rem; padding: .3125rem .625rem; border-radius: .5rem; }
  /* tombol ikon saja (baris tabel) */
  .abtn-icon {
    width: 2rem; height: 2rem; padding: 0; border-radius: .625rem;
    display: inline-flex; align-items: center; justify-content: center;
    background-color: #fff !important; border: 1px solid #e5e7eb !important; color: #5c3d1e !important;
    transition: background-color .15s, border-color .15s, color .15s !important;
  }
  .abtn-icon:hover { background-color: #fdf7f0 !important; border-color: rgba(167,125,82,.4) !important; }
  .abtn-icon svg { width: 1rem; height: 1rem; }
  .abtn-icon-danger { color: #dc2626 !important; }
  .abtn-icon-danger:hover {
    background-color: #fef2f2 !important; border-color: #fecaca !important; color: #dc2626 !important;
  }

  /* ===== 1c. WARNA SEMANTIK AKSI — bukan coklat semua ===== */
  /* Coklat = CTA utama (Create/Save/Submit) */
  .abtn-primary {
    background-color: #a77d52 !important; color: #fff !important;
    border: none !important;
    box-shadow: 0 2px 8px rgba(167,125,82,.3) !important;
  }
  .abtn-primary:hover { background-color: #8b5e3c !important; }
  /* Merah = destruktif (Delete/Reject) */
  .abtn-danger {
    background-color: #dc2626 !important; color: #fff !important;
    border: none !important;
  }
  .abtn-danger:hover { background-color: #b91c1c !important; }
  /* Hijau = aksi setuju/aktif/publish */
  .abtn-success {
    background-color: #059669 !important; color: #fff !important;
    border: none !important;
  }
  .abtn-success:hover { background-color: #047857 !important; }
  /* Kuning = aksi warning/pending/reopen */
  .abtn-warning {
    background-color: #d97706 !important; color: #fff !important;
    border: none !important;
  }
  .abtn-warning:hover { background-color: #b45309 !important; }
  /* Outline coklat = aksi sekunder bertema (View/Edit/Detail) */
  .abtn-secondary {
    background-color: #fff !important; color: #a77d52 !important;
    border: 1px solid #a77d52 !important; border-radius: .625rem !important;
  }
  .abtn-secondary:hover { background-color: #fdf7f0 !important; }
  /* Outline abu = aksi sekunder netral (Cancel/Back/Reset/Export) */
  .abtn-neutral {
    background-color: #fff !important; color: #5c3d1e !important;
    border: 1px solid #e5e7eb !important;
  }
  .abtn-neutral:hover { background-color: #fdf7f0 !important; }

  /* ===== 2. TOMBOL AKSI PRIMER (Create/Save/Submit) ===== */
  .btn-primary,
  button.btn-create,
  a.btn-create,
  [data-action="create"],
  button[type="submit"].btn-save {
    background-color: #a77d52 !important;
    color: #fff !important;
    border: none !important;
    border-radius: .625rem !important;
    font-weight: 600 !important;
    transition: opacity .2s, box-shadow .2s !important;
    box-shadow: 0 2px 8px rgba(167,125,82,.3) !important;
  }
  .btn-primary:hover, button.btn-create:hover, a.btn-create:hover { opacity: .9 !important; }

  /* ===== 3. TOMBOL DELETE (merah) ===== */
  .btn-danger,
  button.btn-delete,
  a.btn-delete,
  [data-action="delete"] {
    background-color: #dc2626 !important;
    color: #fff !important;
    border-radius: .625rem !important;
    font-weight: 600 !important;
  }
  .btn-danger:hover, button.btn-delete:hover, a.btn-delete:hover, [data-action="delete"]:hover {
    background-color: #b91c1c !important;
  }

  /* ===== 4. TOMBOL SECONDARY (Edit/Cancel/View) ===== */
  .btn-secondary,
  button.btn-edit, a.btn-edit,
  button.btn-view, a.btn-view {
    background-color: #fff !important;
    color: #a77d52 !important;
    border: 1.5px solid #a77d52 !important;
    border-radius: .625rem !important;
    font-weight: 600 !important;
    transition: background .2s !important;
  }
  .btn-secondary:hover, button.btn-edit:hover, a.btn-edit:hover { background-color: #fdf7f0 !important; }

  /* ===== 5. HEADER SECTION (hero bar) ===== */
  /* Pastikan header section pakai #a77d52 */
  section > div > div.bg-\[#a77d52\],
  .admin-hero-bar { background-color: #a77d52 !important; }

  /* "New ..." button di dalam hero bar */
  section div[class*="absolute"] a[class*="bg-white"] {
    background-color: #fff !important;
    color: #5c3d1e !important;
    font-weight: 600 !important;
    border-radius: .625rem !important;
    transition: background .15s !important;
  }
  section div[class*="absolute"] a[class*="bg-white"]:hover { background-color: #fdf7f0 !important; }

  /* ===== 6. BADGE STATUS ===== */
  /* Active → hijau tetap, tapi inactive/unknown → #a77d52/5 */
  .badge-inactive { background-color: rgba(167,125,82,.1) !important; color: #5c3d1e !important; }

  /* ===== 7. TABEL STANDAR ===== */
  table thead th {
    background-color: #fdf7f0 !important;
    color: #5c3d1e !important;
    font-weight: 700 !important;
    border-bottom: 2px solid rgba(167,125,82,.25) !important;
  }
  table tbody tr:hover { background-color: #fdf7f0 !important; }
  table tbody tr td { border-color: rgba(167,125,82,.1) !important; }

  /* ===== 8. CARD STANDAR ===== */
  .admin-card,
  .bg-white.border.shadow-sm.rounded-2xl {
    border-color: rgba(167,125,82,.15) !important;
  }
  .admin-card:hover { box-shadow: 0 4px 20px rgba(167,125,82,.12) !important; }

  /* ===== 9. PAGINATION ===== */
  nav[aria-label="Pagination"] li span[class*="bg-slate-100"] {
    background-color: #a77d52 !important;
    color: #fff !important;
    font-weight: 700 !important;
  }
  nav[aria-label="Pagination"] a:hover { background-color: #fdf7f0 !important; }

  /* ===== 10. MODAL / POPUP ===== */
  /* Overlay */
  .modal-overlay, [id*="modal"][class*="fixed inset-0"] {
    background-color: rgba(0,0,0,0.5) !important;
  }
  /* Modal box */
  .modal-box,
  [id*="modal"] > div[class*="bg-white"] {
    border-radius: 1rem !important;
    border: 1.5px solid rgba(167,125,82,.2) !important;
    box-shadow: 0 20px 60px rgba(0,0,0,.25) !important;
  }
  /* Modal header */
  .modal-header,
  [id*="modal"] > div > div:first-child { border-bottom: 1.5px solid rgba(167,125,82,.15) !important; }

  /* Tombol konfirmasi di dalam modal */
  [id*="modal"] button[class*="bg-red"],
  [id*="modal"] button[class*="red"],
  .modal-confirm-delete {
    background-color: #dc2626 !important;
    color: #fff !important;
    border-radius: .625rem !important;
  }
  [id*="modal"] button[class*="bg-slate"],
  [id*="modal"] button[class*="cancel"],
  .modal-cancel {
    background-color: #fff !important;
    color: #5c3d1e !important;
    border: 1.5px solid rgba(167,125,82,.3) !important;
    border-radius: .625rem !important;
  }
  [id*="modal"] button[class*="bg-slate"]:hover,
  .modal-cancel:hover { background-color: #fdf7f0 !important; }

  /* ===== 11. ALERT / FLASH MESSAGES ===== */
  .alert-success { background-color: #f0fdf4 !important; border-color: #bbf7d0 !important; color: #166534 !important; }
  .alert-error   { background-color: #fef2f2 !important; border-color: #fecaca !important; color: #991b1b !important; }
  .alert-warning { background-color: #fffbeb !important; border-color: #fef08a !important; color: #854d0e !important; }
  .alert-info    { background-color: #fdf7f0 !important; border-color: rgba(167,125,82,.3) !important; color: #5c3d1e !important; }

  /* ===== 12. FORM LABELS & INPUTS (halaman create/edit) ===== */
  .form-label, label.block { color: #5c3d1e !important; font-weight: 600 !important; }
  .form-input,
  input[type="text"], input[type="email"], input[type="number"],
  input[type="date"], input[type="tel"], input[type="url"],
  select, textarea {
    border-color: rgba(167,125,82,.25) !important;
    border-radius: .625rem !important;
    transition: border-color .2s, box-shadow .2s !important;
  }
  .form-input:focus,
  input[type="text"]:focus, input[type="email"]:focus,
  select:focus, textarea:focus {
    border-color: #a77d52 !important;
    box-shadow: 0 0 0 3px rgba(167,125,82,.15) !important;
  }

  /* ===== 13. LINK AKSI (Edit / Delete inline) ===== */
  a[href*="/edit"]:not([class*="btn"]):not([class*="side-link"]) { color: #a77d52 !important; }
  a[href*="/edit"]:hover { text-decoration: underline !important; }
</style>
