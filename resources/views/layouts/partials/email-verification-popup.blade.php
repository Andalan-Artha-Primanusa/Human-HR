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
      class="fixed inset-0 flex items-center justify-center overflow-y-auto px-4 py-6"
      style="z-index:99999; background:rgba(15,23,42,0.35); backdrop-filter:blur(2px);"
      role="dialog"
      aria-modal="true"
      aria-labelledby="emailVerificationPopupTitle"
    >
      <div class="relative w-[calc(100%-32px)] max-w-[480px] rounded-[18px] border border-[#E5E7EB] bg-white p-6 text-center shadow-[0_20px_50px_rgba(15,23,42,0.12)] sm:p-8">
        <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-[#F8F3ED] text-[#B18455]">
          @if (session('status') === 'verification-link-sent')
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
              <path d="m9 11 3 3L22 4"/>
            </svg>
          @else
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5v11a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 17.5z"/>
                <path d="m4.5 7 7.5 5.5L19.5 7"/>
            </svg>
          @endif
        </div>

        <h2 id="emailVerificationPopupTitle" class="mt-5 text-[21px] font-bold leading-tight text-[#1F2937]">
          Verifikasi email dulu
        </h2>
        <p class="mx-auto mt-2 max-w-[360px] text-sm leading-[22px] text-[#64748B]">
          Akun kamu belum terverifikasi. Verifikasi email agar semua fitur dan proses lamaran dapat digunakan.
        </p>

          @if (session('status') === 'verification-link-sent')
            <div class="mx-auto mt-5 max-w-[360px] rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold leading-5 text-emerald-800">
              Email verifikasi berhasil dikirim. Silakan cek inbox email kamu.
            </div>
          @endif

          @if ($errors->any())
            <div class="mx-auto mt-5 max-w-[360px] rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold leading-5 text-red-700">
              Gagal mengirim email verifikasi. Silakan coba kembali.
            </div>
          @endif

          <div class="mt-6 flex justify-center">
            @if (Route::has('verification.send'))
              <form method="POST" action="{{ route('verification.send') }}" class="m-0" data-email-verification-form data-skip-global-feedback="1">
                @csrf
                <button type="submit" class="inline-flex h-11 min-w-[190px] items-center justify-center gap-2 rounded-xl bg-[#a77d52] px-6 text-sm font-bold text-white shadow-sm transition duration-200 hover:bg-[#906844] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#a77d52]/25 disabled:cursor-not-allowed disabled:opacity-75 sm:h-[46px]">
                  <svg data-send-icon xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m22 2-7 20-4-9-9-4Z"/>
                    <path d="M22 2 11 13"/>
                  </svg>
                  <svg data-loading-icon xmlns="http://www.w3.org/2000/svg" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                  </svg>
                  <span data-button-label>Kirim Ulang Email</span>
                </button>
              </form>
            @endif
          </div>
          <p class="mt-4 text-xs leading-5 text-[#94A3B8]">
            Belum menerima email? Periksa folder Spam atau Junk.
          </p>
        </div>
    </div>
  @endif
@endauth
