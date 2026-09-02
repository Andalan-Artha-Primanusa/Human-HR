@php
  $notice = null;
  if (session('success') || session('ok')) {
      $notice = ['type' => 'success', 'title' => 'Berhasil', 'message' => session('success') ?: session('ok')];
  } elseif (session('error') || session('err')) {
      $notice = ['type' => 'error', 'title' => 'Gagal', 'message' => session('error') ?: session('err')];
  } elseif (session('warn') || session('warning')) {
      $notice = ['type' => 'warning', 'title' => 'Perhatian', 'message' => session('warn') ?: session('warning')];
  } elseif (session('info')) {
      $notice = ['type' => 'info', 'title' => 'Informasi', 'message' => session('info')];
  } elseif (session('status') && session('status') !== 'verification-link-sent') {
      $notice = ['type' => 'success', 'title' => 'Berhasil', 'message' => session('status')];
  } elseif ($errors->any()) {
      $notice = [
          'type' => 'error',
          'title' => 'Periksa kembali isian kamu',
          'message' => $errors->first(),
      ];
  }
@endphp

<style>
  @keyframes gfPop {
    from { transform: translateY(10px) scale(.98); opacity: 0; }
    to   { transform: translateY(0) scale(1); opacity: 1; }
  }
  @keyframes gfSpin {
    to { transform: rotate(360deg); }
  }
  #globalFeedbackModal {
    --gf-brown: #a77d52;
    --gf-brown-dark: #8b5e3c;
    --gf-fade: rgba(15,23,42,.42);
  }
  #globalFeedbackModal .gf-icon { display: grid; place-items: center; width: 3rem; height: 3rem; border-radius: 9999px; }
  #globalFeedbackModal .gf-badge {
    display: grid; place-items: center;
    width: 2.75rem; height: 2.75rem; border-radius: 9999px;
    background: #f5ede4;
    color: var(--gf-brown-dark);
  }
  #globalFeedbackModal.gf-error .gf-badge { background: #fee2e2; color: #dc2626; }
  #globalFeedbackModal.gf-warning .gf-badge { background: #fef3c7; color: #b45309; }
  #globalFeedbackModal.gf-success .gf-badge { background: #dcfce7; color: #047857; }
  #globalFeedbackModal .gf-badge.spin .gf-glyph { animation: gfSpin 6s linear infinite; }
  #globalFeedbackModal .gf-glyph { width: 1.45rem; height: 1.45rem; }
</style>

<div id="globalFeedbackModal"
     class="gf-info fixed inset-0 z-[9999] hidden items-center justify-center px-4 py-6"
     style="background: var(--gf-fade); backdrop-filter: blur(2px);"
     role="dialog" aria-modal="true">

  <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200"
       style="animation: gfPop .22s ease-out both;">

    <div class="p-6">
      <div class="flex items-start gap-4">
        <div class="gf-icon shrink-0">
        <div class="gf-badge">
          <svg class="gf-glyph" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 13l4 4L19 7"/>
          </svg>
        </div>
      </div>

        <div class="min-w-0 flex-1 text-left">
          <h2 id="globalFeedbackTitle" class="text-lg font-bold leading-6 text-slate-950">Berhasil</h2>
          <p id="globalFeedbackMessage" class="mt-1.5 text-sm leading-6 text-slate-600">Data berhasil disimpan.</p>
        </div>
      </div>

      <div id="globalFeedbackActions" class="mt-6 flex justify-end gap-2">
        <button type="button" id="globalFeedbackCancel" class="hidden rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
        <button type="button" id="globalFeedbackOk" class="rounded-lg bg-[#8b5e3c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#744d31]">Oke</button>
      </div>
    </div>
  </div>
</div>

<script>
  (function(){
    if (window.__karirGlobalFeedbackReady) return;
    window.__karirGlobalFeedbackReady = true;

    const csrfUrl = @json(route('csrf.token'));
    const initialNotice = @json($notice);
    const modal = document.getElementById('globalFeedbackModal');
    const title = document.getElementById('globalFeedbackTitle');
    const message = document.getElementById('globalFeedbackMessage');
    const glyph = modal.querySelector('.gf-glyph');
    const badge = modal.querySelector('.gf-badge');
    const ok = document.getElementById('globalFeedbackOk');
    const cancel = document.getElementById('globalFeedbackCancel');
    let onConfirm = null;

    const GLYPHS = {
      success: '<path d="M5 13l4 4L19 7"/>',
      error:   '<path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>',
      warning: '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4m0 4h.01"/>',
      info:    '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
      confirm: '<path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>',
    };
    const SPIN = { info: true, confirm: true };

    function setTheme(type){
      const t = type || 'info';
      modal.classList.remove('gf-success','gf-error','gf-warning','gf-info');
      modal.classList.add('gf-' + (GLYPHS[t] ? t : 'info'));
      badge.classList.toggle('spin', !!SPIN[t]);
      glyph.innerHTML = GLYPHS[t] || GLYPHS.info;
    }

    function openFeedback(options){
      if (!modal) return;
      const opts = options || {};
      setTheme(opts.type || 'info');
      title.textContent = opts.title || 'Informasi';
      message.textContent = opts.message || '';
      onConfirm = typeof opts.onConfirm === 'function' ? opts.onConfirm : null;
      cancel.classList.toggle('hidden', !onConfirm);
      ok.textContent = onConfirm ? (opts.confirmText || 'Lanjutkan') : 'Oke';
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function closeFeedback(){
      if (!modal) return;
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      onConfirm = null;
    }

    async function refreshCsrf(form){
      try {
        const response = await fetch(csrfUrl, {
          method: 'GET',
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          cache: 'no-store',
        });
        if (!response.ok) return;
        const data = await response.json();
        if (!data || !data.token) return;
        document.querySelectorAll('input[name="_token"]').forEach(input => { input.value = data.token; });
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) meta.setAttribute('content', data.token);
        if (form) {
          const token = form.querySelector('input[name="_token"]');
          if (token) token.value = data.token;
        }
      } catch(e) {}
    }

    async function submitWithFreshToken(form, submitter){
      if (!form || form.dataset.globalSubmitting === '1') return;
      form.dataset.globalSubmitting = '1';
      await refreshCsrf(form);
      if (submitter && submitter.name && !form.querySelector('input[type="hidden"][data-submit-proxy="' + submitter.name + '"]')) {
        const proxy = document.createElement('input');
        proxy.type = 'hidden';
        proxy.name = submitter.name;
        proxy.value = submitter.value || '';
        proxy.dataset.submitProxy = submitter.name;
        form.appendChild(proxy);
      }
      HTMLFormElement.prototype.submit.call(form);
    }

    function formMethod(form){
      const spoof = form.querySelector('input[name="_method"]');
      return String(spoof ? spoof.value : form.method || 'GET').toUpperCase();
    }

    function submitterText(submitter){
      return String(submitter?.innerText || submitter?.value || '').trim().toLowerCase();
    }

    function confirmCopy(form, submitter){
      const method = formMethod(form);
      const label = submitterText(submitter);
      if (method === 'DELETE' || /hapus|delete/.test(label)) {
        return {
          type: 'confirm',
          title: form.dataset.confirmTitle || 'Hapus data ini?',
          message: form.dataset.confirmMessage || 'Aksi ini tidak bisa dibatalkan setelah diproses.',
          confirmText: 'Ya, Hapus',
        };
      }
      if (method === 'PUT' || method === 'PATCH' || /update|ubah|simpan perubahan/.test(label)) {
        return {
          type: 'info',
          title: form.dataset.confirmTitle || 'Simpan perubahan?',
          message: form.dataset.confirmMessage || 'Pastikan data yang diubah sudah benar sebelum disimpan.',
          confirmText: 'Ya, Simpan',
        };
      }
      if (/lamar|apply/.test(label) || String(form.action || '').includes('/apply')) {
        return {
          type: 'info',
          title: form.dataset.confirmTitle || 'Kirim lamaran?',
          message: form.dataset.confirmMessage || 'Profil kandidat akan dicek dulu. Jika belum lengkap, kamu akan diarahkan untuk melengkapi data.',
          confirmText: 'Ya, Lamar',
        };
      }
      if (/kirim|send|jadwal|schedule|undang|email/.test(label)) {
        return {
          type: 'info',
          title: form.dataset.confirmTitle || 'Kirim data ini?',
          message: form.dataset.confirmMessage || 'Pastikan penerima dan isi data sudah benar.',
          confirmText: 'Ya, Kirim',
        };
      }
      if (/tambah|create|simpan|buat/.test(label) || String(form.action || '').includes('/create')) {
        return {
          type: 'info',
          title: form.dataset.confirmTitle || 'Simpan data baru?',
          message: form.dataset.confirmMessage || 'Data akan dibuat dan tersimpan di sistem.',
          confirmText: 'Ya, Simpan',
        };
      }
      return {
        type: 'info',
        title: form.dataset.confirmTitle || 'Lanjutkan aksi ini?',
        message: form.dataset.confirmMessage || 'Periksa kembali data sebelum diproses.',
        confirmText: 'Lanjutkan',
      };
    }

    document.addEventListener('submit', function(event){
      const form = event.target;
      if (!(form instanceof HTMLFormElement)) return;
      if (form.dataset.skipGlobalFeedback === '1') return;
      if (formMethod(form) !== 'DELETE') return;
      event.preventDefault();
      event.stopImmediatePropagation();
      const copy = confirmCopy(form, event.submitter);
      openFeedback({
        type: copy.type,
        title: copy.title,
        message: copy.message,
        confirmText: copy.confirmText,
        onConfirm: () => submitWithFreshToken(form, event.submitter),
      });
    }, true);

    document.addEventListener('submit', function(event){
      const form = event.target;
      if (!(form instanceof HTMLFormElement)) return;
      if (event.defaultPrevented || form.dataset.skipCsrfRefresh === '1' || form.dataset.globalSubmitting === '1') return;
      if (String(form.method || 'GET').toUpperCase() !== 'POST') return;
      event.preventDefault();
      const copy = confirmCopy(form, event.submitter);
      openFeedback({
        type: copy.type,
        title: copy.title,
        message: copy.message,
        confirmText: copy.confirmText,
        onConfirm: () => submitWithFreshToken(form, event.submitter),
      });
    });

    if (window.fetch && !window.__karirFetchGuardReady) {
      window.__karirFetchGuardReady = true;
      const nativeFetch = window.fetch.bind(window);
      window.fetch = async function(input, init){
        const options = init ? Object.assign({}, init) : {};
        const method = String(options.method || (input instanceof Request ? input.method : 'GET') || 'GET').toUpperCase();
        const url = typeof input === 'string' ? input : (input?.url || '');
        const sameOrigin = !/^https?:\/\//i.test(url) || url.startsWith(window.location.origin);

        if (sameOrigin && !['GET', 'HEAD', 'OPTIONS'].includes(method)) {
          const headers = new Headers(options.headers || (input instanceof Request ? input.headers : undefined) || {});
          const token = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value || '';
          if (token && !headers.has('X-CSRF-TOKEN')) {
            headers.set('X-CSRF-TOKEN', token);
          }
          headers.set('X-Requested-With', headers.get('X-Requested-With') || 'XMLHttpRequest');
          options.headers = headers;
        }

        const response = await nativeFetch(input, options);
        if (sameOrigin && response.status === 419) {
          openFeedback({
            type: 'warning',
            title: 'Sesi halaman kedaluwarsa',
            message: 'Refresh halaman atau login ulang, lalu coba aksi ini sekali lagi.',
          });
        } else if (sameOrigin && response.status >= 500) {
          openFeedback({
            type: 'error',
            title: 'Sistem sedang gangguan',
            message: 'Permintaan belum berhasil diproses. Coba ulangi beberapa saat lagi.',
          });
        }
        return response;
      };
    }

    ok && ok.addEventListener('click', function(){
      const callback = onConfirm;
      closeFeedback();
      if (callback) callback();
    });
    cancel && cancel.addEventListener('click', closeFeedback);
    modal && modal.addEventListener('click', function(event){
      if (event.target === modal && !onConfirm) closeFeedback();
    });
    document.addEventListener('keydown', function(event){
      if (event.key === 'Escape' && !modal.classList.contains('hidden') && !onConfirm) closeFeedback();
    });

    window.KarirFeedback = { open: openFeedback, refreshCsrf: refreshCsrf };
    if (!window.__karirNativeAlertGuardReady) {
      window.__karirNativeAlertGuardReady = true;
      window.alert = function(text){
        openFeedback({
          type: 'info',
          title: 'Informasi',
          message: String(text || ''),
        });
      };
    }

    if (initialNotice && initialNotice.message) {
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => openFeedback(initialNotice));
      } else {
        openFeedback(initialNotice);
      }
    }
  })();
</script>
