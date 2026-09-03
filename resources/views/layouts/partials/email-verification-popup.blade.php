@auth
  @php
      $user = auth()->user();
      $isVerified = method_exists($user, 'hasVerifiedEmail')
          ? $user->hasVerifiedEmail()
          : (bool) ($user->email_verified_at ?? false);
      $verifyNoticeUrl = Route::has('verification.notice') ? route('verification.notice') : url('/verify-email');
  @endphp

  @if(!$isVerified)
    <div
      id="emailVerificationPopup"
      class="fixed inset-x-3 bottom-4 z-[80] sm:inset-x-auto sm:right-5 sm:w-[390px]"
      role="dialog"
      aria-live="polite"
      aria-labelledby="emailVerificationPopupTitle"
    >
      <div class="overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-2xl ring-1 ring-slate-900/5">
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
            <button
              type="button"
              class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
              data-dismiss-email-verification-popup
              aria-label="Tutup popup verifikasi"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M18 6 6 18"/>
                <path d="m6 6 12 12"/>
              </svg>
            </button>
          </div>

          <div class="mt-5 grid gap-2 sm:grid-cols-2">
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
            <a href="{{ $verifyNoticeUrl }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-bold text-[#80572f] transition hover:bg-amber-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M15 3h6v6"/>
                <path d="M10 14 21 3"/>
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
              </svg>
              Halaman Verifikasi
            </a>
          </div>
        </div>
      </div>
    </div>
  @endif
@endauth
