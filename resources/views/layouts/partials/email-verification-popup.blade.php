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
      class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/45 px-4 py-6 backdrop-blur-[2px]"
      role="dialog"
      aria-modal="true"
      aria-labelledby="emailVerificationPopupTitle"
    >
      <div class="w-full max-w-md overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-2xl ring-1 ring-slate-900/5">
        <div class="h-1.5 bg-gradient-to-r from-amber-500 via-[#a77d52] to-red-500"></div>
        <div class="p-5">
          <div class="flex items-start gap-4">
            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-amber-50 text-[#8a5a2f] ring-1 ring-amber-200">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5v11a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 17.5z"/>
                <path d="m4.5 7 7.5 5.5L19.5 7"/>
              </svg>
            </div>
            <div class="min-w-0 flex-1">
              <div id="emailVerificationPopupTitle" class="text-base font-bold text-slate-950">Verifikasi email dulu</div>
              <p class="mt-1 text-sm leading-6 text-slate-600">
                Akun belum terverifikasi. Selesaikan verifikasi supaya semua menu dan proses lamaran bisa dipakai.
              </p>
            </div>
          </div>

          @if (session('status') === 'verification-link-sent')
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800">
              Link verifikasi baru sudah dikirim ke email kamu.
            </div>
          @endif

          <div class="mt-5">
            @if (Route::has('verification.send'))
              <form method="POST" action="{{ route('verification.send') }}" class="m-0">
                @csrf
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#a77d52] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#906844] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#a77d52]/40">
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
