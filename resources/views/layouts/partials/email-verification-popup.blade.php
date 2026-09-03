@auth
  @php
      $user = auth()->user();
      $isVerified = method_exists($user, 'hasVerifiedEmail')
          ? $user->hasVerifiedEmail()
          : (bool) ($user->email_verified_at ?? false);
  @endphp

  @if(!$isVerified)
    <div
      id="emailVerificationPopup"
      class="fixed inset-0 flex items-start justify-center overflow-y-auto bg-black/70 px-4 py-20 backdrop-blur-sm sm:items-center sm:py-6"
      style="z-index:99999;"
      role="dialog"
      aria-modal="true"
      aria-labelledby="emailVerificationPopupTitle"
    >
      <div class="relative w-full max-w-[460px] overflow-hidden rounded-2xl border border-white/70 bg-white shadow-[0_28px_80px_rgba(0,0,0,0.55)] ring-1 ring-black/10">
        <div class="absolute inset-x-0 top-0 h-1.5 bg-[#a77d52]"></div>
        <div class="p-6 sm:p-7">
          <div class="flex flex-col items-center text-center">
            <div class="grid h-16 w-16 place-items-center rounded-2xl bg-[#fff7ed] text-[#8a5a2f] shadow-sm ring-1 ring-[#ead8c5]">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5v11a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 17.5z"/>
                <path d="m4.5 7 7.5 5.5L19.5 7"/>
                <path d="M12 15.5h.01"/>
                <path d="M12 10.5v2"/>
              </svg>
            </div>
            <div id="emailVerificationPopupTitle" class="mt-5 text-xl font-extrabold text-slate-950">Verifikasi email dulu</div>
            <p class="mt-2 max-w-sm text-sm leading-6 text-slate-600">
              Akun belum terverifikasi. Selesaikan verifikasi supaya semua menu dan proses lamaran bisa dipakai.
            </p>
          </div>

          @if (session('status') === 'verification-link-sent')
            <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-sm font-semibold text-emerald-800">
              Link verifikasi baru sudah dikirim ke email kamu.
            </div>
          @endif

          <div class="mt-6">
            @if (Route::has('verification.send'))
              <form method="POST" action="{{ route('verification.send') }}" class="m-0">
                @csrf
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#a77d52] px-4 py-3 text-sm font-bold text-white shadow-[0_14px_28px_rgba(167,125,82,0.34)] transition hover:bg-[#906844] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#a77d52]/25">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m22 2-7 20-4-9-9-4Z"/>
                    <path d="M22 2 11 13"/>
                  </svg>
                  Kirim Ulang
                </button>
              </form>
            @endif
          </div>
        </div>
      </div>
    </div>
  @endif
@endauth
