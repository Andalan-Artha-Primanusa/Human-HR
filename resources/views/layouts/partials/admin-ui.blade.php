{{-- resources/views/layouts/partials/admin-ui.blade.php --}}
{{-- Global CSS untuk standarisasi semua halaman admin (#a77d52 + white) --}}
<style>
  /* ============================================================
     ADMIN GLOBAL UI — Human.Careers
     Palet: #a77d52 (accent) · #5c3d1e (dark) · #fff (white)
  ============================================================ */

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
