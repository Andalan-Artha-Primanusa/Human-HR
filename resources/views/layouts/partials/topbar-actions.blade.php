{{-- resources/views/layouts/partials/topbar-actions.blade.php --}}
@php
    use Illuminate\Support\Facades\Schema;

    /** @var \App\Models\User|null $u */
    $u = auth()->user();
    $accountInitial = $u && $u->name ? mb_strtoupper(mb_substr(trim($u->name), 0, 1)) : 'U';

    // ====== Notifications (initial render sebagai fallback) ======
    $notifUnread = 0;
    $notifItems = collect();
    if ($u && Schema::hasTable('notifications')) {
        try {
            $notifUnread = $u->unreadNotifications()->count();
            $notifItems = $u->notifications()->latest()->limit(10)->get();
        } catch (\Throwable $e) {
            $notifUnread = 0;
            $notifItems = collect();
        }
    }

    // URL JSON notifikasi untuk JS (kalau route tersedia)
    $notifJsonUrl = (Route::has('me.notifications.index') && $u)
        ? route('me.notifications.index', ['format' => 'json'])
        : null;
@endphp

<div class="relative flex items-center gap-1 ml-auto md:gap-2">

  @auth
    {{-- ====== DROPDOWN: Notifications ====== --}}
    <div class="relative">
      <button type="button"
              class="relative p-2 rounded-lg hover:bg-slate-100"
              data-dd-trigger="dd-notifs"
              aria-haspopup="true"
              aria-expanded="false"
              aria-label="Notifikasi"
              title="Notifikasi">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 18.75a2.25 2.25 0 1 1-4.5 0m9-4.5v-3a6.75 6.75 0 1 0-13.5 0v3l-1.5 1.5v1.5h16.5v-1.5l-1.5-1.5z" />
        </svg>
        <span class="sr-only">Notifikasi (klik untuk melihat pemberitahuan terbaru Anda)</span>
        <span id="notif-badge"
              class="absolute -top-0.5 -right-0.5 text-[10px] px-1.5 py-0.5 rounded-full bg-red-500 text-white {{ $notifUnread > 0 ? '' : 'hidden' }}">
          {{ $notifUnread }}
        </span>
      </button>

      <div id="dd-notifs"
           class="dropdown-panel hidden absolute right-0 mt-2 w-[26rem] bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden z-40">
        <div class="flex items-center justify-between px-3 py-2 border-b border-slate-200">
          <div class="font-semibold text-slate-800">Notifikasi</div>
          <span id="notif-chip"
                class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 {{ $notifUnread > 0 ? '' : 'hidden' }}">
            {{ $notifUnread }} belum dibaca
          </span>
        </div>

        <div id="notif-empty"
             class="p-4 text-sm text-slate-500 {{ $notifItems->isEmpty() ? '' : 'hidden' }}">Belum ada notifikasi.</div>

        <ul id="notif-list" class="max-h-96 overflow-y-auto divide-y divide-slate-100 {{ $notifItems->isEmpty() ? 'hidden' : '' }}">
          @foreach($notifItems as $n)
            @php
                $data = (array) ($n->data ?? []);
                $title = $data['title'] ?? ($data['message'] ?? 'Notifikasi');
                $desc = $data['body'] ?? ($data['excerpt'] ?? null);
                $link = $data['url'] ?? null;
                $isUnread = is_null($n->read_at);
            @endphp
            <li class="p-3 hover:bg-slate-50">
              <div class="flex items-start gap-3">
                <div class="mt-0.5">
                  <span class="inline-flex items-center justify-center w-5 h-5 rounded-full {{ $isUnread ? 'bg-red-500/10 text-red-600' : 'bg-slate-100 text-slate-500' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6 6 0 1 0-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5" />
                    </svg>
                  </span>
                </div>
                <div class="min-w-0">
                  <div class="font-medium truncate text-slate-800">{{ $title }}</div>
                  @if($desc)
                    <div class="text-xs text-slate-600 line-clamp-2">{{ $desc }}</div>
                  @endif
                  <div class="text-[11px] text-slate-500 mt-0.5">
                    {{ optional($n->created_at)->diffForHumans() }}
                  </div>
                  @if($link)
                    <div class="mt-1">
                      <a href="{{ $link }}" class="text-xs text-blue-600 hover:underline">Buka</a>
                    </div>
                  @endif
                </div>
              </div>
            </li>
          @endforeach
        </ul>

        <div class="px-3 py-2 text-right border-t border-slate-200">
          @if (Route::has('me.notifications.index'))
            <a href="{{ route('me.notifications.index') }}" class="text-sm text-blue-600 hover:underline">Lihat semua</a>
          @endif
        </div>
      </div>
    </div>

    {{-- ====== Account Dropdown ====== --}}
    <div class="relative">
      <button type="button"
              class="flex items-center gap-2 rounded-full border border-slate-200 bg-white p-1 pr-2 shadow-sm transition hover:bg-slate-50"
              data-dd-trigger="dd-account"
              aria-haspopup="true"
              aria-expanded="false"
              aria-label="Menu akun"
              title="Menu akun">
        @if($u && property_exists($u, 'profile_photo_url') && $u->profile_photo_url)
          <img src="{{ $u->profile_photo_url }}" class="object-cover w-8 h-8 rounded-full" alt="{{ $u->name }}">
        @else
          <div class="grid w-8 h-8 text-sm font-semibold text-white bg-[#8b5e3c] rounded-full place-content-center">
            {{ $accountInitial }}
          </div>
        @endif
        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="m6 9 6 6 6-6"/>
        </svg>
      </button>

      <div id="dd-account"
           class="dropdown-panel absolute right-0 z-40 mt-2 hidden w-72 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl shadow-slate-900/10">
        <div class="mb-1 flex items-center gap-3 rounded-xl bg-[#f7efe7] px-3 py-3">
          <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-[#8b5e3c] text-sm font-bold text-white">
            {{ $accountInitial }}
          </div>
          <div class="min-w-0">
            <div class="truncate text-sm font-bold text-slate-950">{{ $u->name }}</div>
            <div class="truncate text-xs text-slate-500">{{ $u->email }}</div>
          </div>
        </div>

        <a href="{{ route('profile.edit') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
          <span class="grid h-9 w-9 place-items-center rounded-lg bg-slate-100 text-slate-500 transition group-hover:bg-[#f5ede4] group-hover:text-[#8b5e3c]">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
          </span>
          <span>Profil Saya</span>
        </a>

        @if(Route::has('applications.mine'))
          <a href="{{ route('applications.mine') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            <span class="grid h-9 w-9 place-items-center rounded-lg bg-slate-100 text-slate-500 transition group-hover:bg-[#f5ede4] group-hover:text-[#8b5e3c]">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 13h18"/></svg>
            </span>
            <span>Lamaran Saya</span>
          </a>
        @endif

        <div class="my-2 border-t border-slate-100"></div>
        <form action="{{ route('logout') }}" method="POST">
          @csrf
          <button type="submit" class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-red-600 transition hover:bg-red-50">
            <span class="grid h-9 w-9 place-items-center rounded-lg bg-red-50 text-red-500 transition group-hover:bg-red-100">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M21 19V5a2 2 0 0 0-2-2h-5"/><path d="M14 21h5a2 2 0 0 0 2-2"/></svg>
            </span>
            <span>Keluar</span>
          </button>
        </form>
      </div>
    </div>
  @endauth
</div>

{{-- === Tiny JS: toggle dropdown, close on outside/ESC === --}}
<script>
  (function(){
    const panels = document.querySelectorAll('.dropdown-panel');
    const triggers = document.querySelectorAll('[data-dd-trigger]');
    const open = (id, btn) => {
      panels.forEach(p => { if (p.id !== id) p.classList.add('hidden'); });
      triggers.forEach(t => { if (t !== btn) t.setAttribute('aria-expanded','false'); });

      const panel = document.getElementById(id);
      if (!panel) return;
      const willOpen = panel.classList.contains('hidden');
      panel.classList.toggle('hidden');
      btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');

      // refresh ketika panel notifikasi dibuka
      if (id === 'dd-notifs' && willOpen && window.refreshNotifications) {
        window.refreshNotifications();
      }
    };
    const closeAll = () => {
      panels.forEach(p => p.classList.add('hidden'));
      triggers.forEach(t => t.setAttribute('aria-expanded','false'));
    };

    // click trigger
    triggers.forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const id = btn.getAttribute('data-dd-trigger');
        open(id, btn);
      });
    });

    // click outside
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.dropdown-panel') && !e.target.closest('[data-dd-trigger]')) {
        closeAll();
      }
    });

    // escape
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeAll();
    });

    // optional: close on resize/scroll
    window.addEventListener('resize', closeAll);
    window.addEventListener('scroll', () => closeAll(), { passive: true });
  })();
</script>

@auth
    {{-- === POLLING NOTIF: hanya dimuat saat login === --}}
    <script>
      (function(){
        const notifUrl = @json($notifJsonUrl); // sudah ?format=json dari server
        if (!notifUrl) return; // safety

        const badgeEl  = document.getElementById('notif-badge');
        const chipEl   = document.getElementById('notif-chip');
        const listEl   = document.getElementById('notif-list');
        const emptyEl  = document.getElementById('notif-empty');

        let timer = null;
        let stopped = false;

        async function refreshNotifications(){
          if (stopped) return;
          try {
            const res = await fetch(notifUrl, {
              headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
              },
              credentials: 'same-origin'
            });

            if (res.status === 401) {
              // sesi tidak valid / guest: hentikan polling
              stopped = true;
              if (timer) clearInterval(timer);
              return;
            }
            if (!res.ok) return;

            const data = await res.json();
            const unread = Number(data.unread || 0);

            // badge
            if (badgeEl) {
              badgeEl.textContent = unread;
              if (unread > 0) badgeEl.classList.remove('hidden');
              else badgeEl.classList.add('hidden');
            }
            if (chipEl) {
              chipEl.textContent = unread > 0 ? `${unread} belum dibaca` : '';
              if (unread > 0) chipEl.classList.remove('hidden');
              else chipEl.classList.add('hidden');
            }

            // list
            if (!(listEl && emptyEl)) return;

            const items = Array.isArray(data.items) ? data.items : [];
            if (items.length === 0) {
              listEl.classList.add('hidden');
              emptyEl.classList.remove('hidden');
              listEl.innerHTML = '';
              return;
            }

            listEl.classList.remove('hidden');
            emptyEl.classList.add('hidden');

            listEl.innerHTML = items.map(it => {
              const title = escapeHtml(it.title || 'Notifikasi');
              const body  = it.body ? `<div class="text-xs text-slate-600 line-clamp-2">${escapeHtml(it.body)}</div>` : '';
              const time  = `<div class="text-[11px] text-slate-500 mt-0.5">${escapeHtml(it.created_at || '')}</div>`;
              const link  = it.url ? `<div class="mt-1"><a href="${it.url}" class="text-xs text-blue-600 hover:underline">Buka</a></div>` : '';
              const iconCls = it.unread ? 'bg-red-500/10 text-red-600' : 'bg-slate-100 text-slate-500';
              return `
                <li class="p-3 hover:bg-slate-50">
                  <div class="flex items-start gap-3">
                    <div class="mt-0.5">
                      <span class="inline-flex items-center justify-center w-5 h-5 rounded-full ${iconCls}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6 6 0 1 0-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5" />
                        </svg>
                      </span>
                    </div>
                    <div class="min-w-0">
                      <div class="font-medium truncate text-slate-800">${title}</div>
                      ${body}
                      ${time}
                      ${link}
                    </div>
                  </div>
                </li>`;
            }).join('');
          } catch(e) {
            // optional: stop/backoff supaya konsol tetap bersih
            stopped = true;
            if (timer) clearInterval(timer);
          }
        }

        function escapeHtml(s){
          return (s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
        }

        // expose ke global untuk dipanggil saat panel dibuka
        window.refreshNotifications = refreshNotifications;

        // jadwalkan polling (tiap 15 detik) + refresh saat tab aktif lagi
        setTimeout(refreshNotifications, 2000); // tunda awal agar tidak ganggu LCP
        timer = setInterval(refreshNotifications, 15000);
        document.addEventListener('visibilitychange', () => {
          if (!document.hidden) refreshNotifications();
        });
      })();
    </script>
@endauth
