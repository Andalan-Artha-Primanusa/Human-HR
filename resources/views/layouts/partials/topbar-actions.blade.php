{{-- resources/views/layouts/partials/topbar-actions.blade.php --}}
@php
  use Illuminate\Support\Facades\Schema;
  use Illuminate\Support\Facades\DB;
  use Illuminate\Support\Str;

  /** @var \App\Models\User|null $u */
  $u = auth()->user();

  // ====== Notifications (initial render sebagai fallback) ======
  $notifUnread = 0;
  $notifItems  = collect();
  if ($u && Schema::hasTable('notifications')) {
      try {
          $notifUnread = $u->unreadNotifications()->count();
          $notifItems  = $u->notifications()->latest()->limit(10)->get();
      } catch (\Throwable $e) {
          $notifUnread = 0;
          $notifItems  = collect();
      }
  }

  // ====== Upcoming Interviews (punya user) - initial (opsional, polling bisa ditambah belakangan) ======
  $interviewUpcoming = 0;
  $interviewItems    = collect();
  if ($u && Schema::hasTable('interviews') && Schema::hasTable('job_applications')) {
      try {
          $base = DB::table('interviews')
              ->join('job_applications','interviews.application_id','=','job_applications.id')
              ->where('job_applications.user_id', $u->id)
              ->orderBy('interviews.start_at','asc');

          $interviewUpcoming = (clone $base)->where('interviews.start_at','>=', now())->count();

          $interviewItems = (clone $base)
              ->where('interviews.start_at','>=', now()->subDays(7))
              ->select([
                  'interviews.id',
                  'interviews.title',
                  'interviews.mode',
                  'interviews.location',
                  'interviews.meeting_link',
                  'interviews.start_at',
                  'interviews.end_at',
              ])
              ->limit(10)
              ->get();
      } catch (\Throwable $e) {
          $interviewUpcoming = 0;
          $interviewItems    = collect();
      }
  }

  // Helper format tanggal singkat
  function ac_dt($dt) {
      try {
          return \Carbon\Carbon::parse($dt)->locale('id')->isoFormat('ddd, D MMM HH:mm');
      } catch (\Throwable $e) {
          return (string) $dt;
      }
  }
@endphp

<div class="ml-auto flex items-center gap-1 md:gap-2 relative">

  @auth
    {{-- ====== DROPDOWN: Interviews ====== --}}
    <div class="relative">
      <button type="button"
              class="relative p-2 rounded-lg hover:bg-slate-100"
              data-dd-trigger="dd-interviews"
              aria-haspopup="true"
              aria-expanded="false"
              title="Interview Saya">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V5m8 2V5M4 9h16M7 11h10a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2z" />
        </svg>
        @if($interviewUpcoming > 0)
          <span class="absolute -top-0.5 -right-0.5 text-[10px] px-1.5 py-0.5 rounded-full bg-emerald-500 text-white">
            {{ $interviewUpcoming }}
          </span>
        @endif
      </button>

      <div id="dd-interviews"
           class="dropdown-panel hidden absolute right-0 mt-2 w-[22rem] bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden z-40">
        <div class="px-3 py-2 border-b border-slate-200 flex items-center justify-between">
          <div class="font-semibold text-slate-800">Interview</div>
          @if($interviewUpcoming > 0)
            <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
              {{ $interviewUpcoming }} mendatang
            </span>
          @endif
        </div>

        @if($interviewItems->isEmpty())
          <div class="p-4 text-sm text-slate-500">Belum ada jadwal interview.</div>
        @else
          <ul class="max-h-96 overflow-y-auto divide-y divide-slate-100">
            @foreach($interviewItems as $iv)
              <li class="p-3 hover:bg-slate-50">
                <div class="flex items-start gap-3">
                  <div class="mt-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V5m8 2V5M4 9h16M7 11h10a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2z" />
                    </svg>
                  </div>
                  <div class="min-w-0">
                    <div class="font-medium text-slate-800 truncate">{{ $iv->title ?? 'Interview' }}</div>
                    <div class="text-xs text-slate-600 mt-0.5">
                      {{ ac_dt($iv->start_at) }}
                      @if($iv->mode)
                        • {{ Str::title($iv->mode) }}
                      @endif
                    </div>
                    @if($iv->location)
                      <div class="text-xs text-slate-600 truncate">Lokasi: {{ $iv->location }}</div>
                    @elseif($iv->meeting_link)
                      <div class="text-xs">
                        <a href="{{ $iv->meeting_link }}" target="_blank" class="text-blue-600 hover:underline">Link meeting</a>
                      </div>
                    @endif
                  </div>
                </div>
              </li>
            @endforeach
          </ul>
        @endif

        <div class="px-3 py-2 border-t border-slate-200 text-right">
          @if (Route::has('me.interviews.index'))
            <a href="{{ route('me.interviews.index') }}" class="text-sm text-blue-600 hover:underline">Lihat semua</a>
          @endif
        </div>
      </div>
    </div>

    {{-- ====== DROPDOWN: Notifications ====== --}}
    <div class="relative">
      <button type="button"
              class="relative p-2 rounded-lg hover:bg-slate-100"
              data-dd-trigger="dd-notifs"
              aria-haspopup="true"
              aria-expanded="false"
              title="Notifikasi">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 18.75a2.25 2.25 0 1 1-4.5 0m9-4.5v-3a6.75 6.75 0 1 0-13.5 0v3l-1.5 1.5v1.5h16.5v-1.5l-1.5-1.5z" />
        </svg>
        {{-- badge unread (NEW: pakai id agar bisa diupdate JS) --}}
        <span id="notif-badge"
              class="absolute -top-0.5 -right-0.5 text-[10px] px-1.5 py-0.5 rounded-full bg-red-500 text-white {{ $notifUnread>0 ? '' : 'hidden' }}">
          {{ $notifUnread }}
        </span>
      </button>

      <div id="dd-notifs"
           class="dropdown-panel hidden absolute right-0 mt-2 w-[26rem] bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden z-40">
        <div class="px-3 py-2 border-b border-slate-200 flex items-center justify-between">
          <div class="font-semibold text-slate-800">Notifikasi</div>
          <span id="notif-chip"
                class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 {{ $notifUnread>0 ? '' : 'hidden' }}">
            {{ $notifUnread }} belum dibaca
          </span>
        </div>

        {{-- list notifikasi (NEW: beri id agar bisa diupdate JS) --}}
        <div id="notif-empty"
             class="p-4 text-sm text-slate-500 {{ $notifItems->isEmpty() ? '' : 'hidden' }}">Belum ada notifikasi.</div>

        <ul id="notif-list" class="max-h-96 overflow-y-auto divide-y divide-slate-100 {{ $notifItems->isEmpty() ? 'hidden' : '' }}">
          @foreach($notifItems as $n)
            @php
              $data  = (array) ($n->data ?? []);
              $title = $data['title'] ?? ($data['message'] ?? 'Notifikasi');
              $desc  = $data['body']  ?? ($data['excerpt'] ?? null);
              $link  = $data['url']   ?? null;
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
                  <div class="font-medium text-slate-800 truncate">{{ $title }}</div>
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

        <div class="px-3 py-2 border-t border-slate-200 text-right">
          @if (Route::has('me.notifications.index'))
            <a href="{{ route('me.notifications.index') }}" class="text-sm text-blue-600 hover:underline">Lihat semua</a>
          @endif
        </div>
      </div>
    </div>

    {{-- ====== Avatar ====== --}}
    <a href="{{ route('profile.edit') }}" class="p-1 rounded-full border border-slate-200 hover:bg-slate-50" title="Profil">
      @if($u && property_exists($u,'profile_photo_url') && $u->profile_photo_url)
        <img src="{{ $u->profile_photo_url }}" class="w-8 h-8 rounded-full object-cover" alt="{{ $u->name }}">
      @else
        @php $initial = $u && $u->name ? mb_strtoupper(mb_substr(trim($u->name),0,1)) : 'U'; @endphp
        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 grid place-content-center text-sm font-semibold">
          {{ $initial }}
        </div>
      @endif
    </a>
  @endauth
</div>

{{-- === Tiny JS: toggle dropdown, close on outside/ESC + POLLING NOTIF === --}}
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
      if (id === 'dd-notifs' && willOpen) {
        refreshNotifications();
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

    // =======================
    // POLLING NOTIFICATIONS
    // =======================
    const notifUrl = "{{ route('me.notifications.index') }}"; // endpoint yang sama
    const badgeEl  = document.getElementById('notif-badge');
    const chipEl   = document.getElementById('notif-chip');
    const listEl   = document.getElementById('notif-list');
    const emptyEl  = document.getElementById('notif-empty');

    async function refreshNotifications(){
      try {
        const res = await fetch(notifUrl + '?format=json', {
          headers: { 'Accept': 'application/json' },
          credentials: 'same-origin',
        });
        if(!res.ok) return;
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
        if (!listEl || !emptyEl) return;

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
                  <div class="font-medium text-slate-800 truncate">${title}</div>
                  ${body}
                  ${time}
                  ${link}
                </div>
              </div>
            </li>`;
        }).join('');
      } catch(e) {
        // silent
      }
    }

    // helper aman untuk text
    function escapeHtml(s){
      return (s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
    }

    // jalankan polling tiap 15 detik
    setInterval(refreshNotifications, 15000);
    // first boot sedikit ditunda agar tidak mengganggu LCP
    setTimeout(refreshNotifications, 2000);
  })();
</script>
