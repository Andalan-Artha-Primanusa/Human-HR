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

<div id="globalFeedbackModal" class="fixed inset-0 z-[9999] hidden items-center justify-center px-4 py-6 bg-slate-950/50" role="dialog" aria-modal="true">
  <div class="w-full max-w-md overflow-hidden bg-white shadow-2xl rounded-2xl ring-1 ring-slate-200">
    <div id="globalFeedbackAccent" class="h-1.5 bg-[#a77d52]"></div>
    <div class="p-6">
      <div class="flex items-start gap-4">
        <div id="globalFeedbackIcon" class="grid w-11 h-11 rounded-full place-items-center bg-[#f5ede4] text-[#8b5e3c] shrink-0">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <div class="min-w-0">
          <h2 id="globalFeedbackTitle" class="text-base font-semibold text-slate-950">Berhasil</h2>
          <p id="globalFeedbackMessage" class="mt-1 text-sm leading-6 text-slate-600">Data berhasil disimpan.</p>
        </div>
      </div>
      <div id="globalFeedbackActions" class="flex justify-end gap-2 mt-6">
        <button type="button" id="globalFeedbackCancel" class="hidden px-4 py-2 text-sm font-semibold bg-white border rounded-lg border-slate-200 text-slate-700 hover:bg-slate-50">Batal</button>
        <button type="button" id="globalFeedbackOk" class="px-4 py-2 text-sm font-semibold text-white rounded-lg bg-[#a77d52] hover:opacity-95">Oke</button>
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
    const icon = document.getElementById('globalFeedbackIcon');
    const accent = document.getElementById('globalFeedbackAccent');
    const ok = document.getElementById('globalFeedbackOk');
    const cancel = document.getElementById('globalFeedbackCancel');
    let onConfirm = null;

    const themes = {
      success: { accent: 'bg-emerald-500', icon: 'bg-emerald-50 text-emerald-700' },
      error: { accent: 'bg-red-500', icon: 'bg-red-50 text-red-700' },
      warning: { accent: 'bg-amber-500', icon: 'bg-amber-50 text-amber-700' },
      info: { accent: 'bg-[#a77d52]', icon: 'bg-[#f5ede4] text-[#8b5e3c]' },
      confirm: { accent: 'bg-red-500', icon: 'bg-red-50 text-red-700' },
    };

    function setTheme(type){
      const theme = themes[type] || themes.info;
      accent.className = 'h-1.5 ' + theme.accent;
      icon.className = 'grid w-11 h-11 rounded-full place-items-center shrink-0 ' + theme.icon;
      icon.innerHTML = type === 'error' || type === 'warning' || type === 'confirm'
        ? '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>'
        : '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
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

    if (initialNotice && initialNotice.message) {
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => openFeedback(initialNotice));
      } else {
        openFeedback(initialNotice);
      }
    }
  })();
</script>
