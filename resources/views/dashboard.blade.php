<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        $user = auth()->user();
        $role = $user->role ?? 'pelamar';
        $isAdmin = in_array($role, ['superadmin', 'hr', 'admin'], true);
    @endphp

    <div class="py-10">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if($isAdmin)
                <div class="space-y-6">
                    <section class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
                        <div class="p-6 text-white bg-[#a77d52]">
                            <p class="text-sm font-medium text-white/80">Dashboard Admin</p>
                            <h3 class="mt-1 text-2xl font-semibold">Ringkasan Rekrutmen</h3>
                            <p class="mt-1 text-sm text-white/80">Pantau lowongan, kandidat, lamaran, dan jadwal interview dari satu halaman.</p>
                        </div>

                        <div class="grid gap-4 p-6 md:grid-cols-2 lg:grid-cols-4">
                            <a href="{{ route('admin.jobs.index') }}" class="p-5 transition border rounded-xl border-slate-200 hover:border-[#a77d52] hover:bg-[#f8f5f1]">
                                <p class="text-sm text-slate-500">Lowongan Open</p>
                                <p class="mt-2 text-3xl font-bold text-[#a77d52]">{{ $openJobsCount ?? 0 }}</p>
                                <p class="mt-3 text-xs font-semibold text-[#8b5e3c]">Kelola Jobs</p>
                            </a>
                            <a href="{{ route('admin.applications.index') }}" class="p-5 transition border rounded-xl border-slate-200 hover:border-[#a77d52] hover:bg-[#f8f5f1]">
                                <p class="text-sm text-slate-500">Total Lamaran</p>
                                <p class="mt-2 text-3xl font-bold text-[#a77d52]">{{ $applicationsCount ?? 0 }}</p>
                                <p class="mt-3 text-xs font-semibold text-[#8b5e3c]">Lihat Applications</p>
                            </a>
                            <a href="{{ route('admin.candidates.index') }}" class="p-5 transition border rounded-xl border-slate-200 hover:border-[#a77d52] hover:bg-[#f8f5f1]">
                                <p class="text-sm text-slate-500">Kandidat</p>
                                <p class="mt-2 text-3xl font-bold text-[#a77d52]">{{ $candidatesCount ?? 0 }}</p>
                                <p class="mt-3 text-xs font-semibold text-[#8b5e3c]">Buka Candidates</p>
                            </a>
                            <a href="{{ route('admin.interviews.index') }}" class="p-5 transition border rounded-xl border-slate-200 hover:border-[#a77d52] hover:bg-[#f8f5f1]">
                                <p class="text-sm text-slate-500">Interview</p>
                                <p class="mt-2 text-3xl font-bold text-[#a77d52]">{{ $interviewsCount ?? 0 }}</p>
                                <p class="mt-3 text-xs font-semibold text-[#8b5e3c]">Atur Jadwal</p>
                            </a>
                        </div>
                    </section>

                    <section class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <a href="{{ route('admin.applications.board') }}" class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200 hover:bg-[#f8f5f1]">
                            <h4 class="font-semibold text-slate-900">Kanban Board</h4>
                            <p class="mt-1 text-sm text-slate-500">Pantau stage kandidat dan sinkron MinePro.</p>
                        </a>
                        <a href="{{ route('admin.jobs.create') }}" class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200 hover:bg-[#f8f5f1]">
                            <h4 class="font-semibold text-slate-900">Create Job</h4>
                            <p class="mt-1 text-sm text-slate-500">Ambil data RFR MinePro dan buat lowongan baru.</p>
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200 hover:bg-[#f8f5f1]">
                            <h4 class="font-semibold text-slate-900">Users</h4>
                            <p class="mt-1 text-sm text-slate-500">Kelola akun HR, admin, trainer, dan pelamar.</p>
                        </a>
                    </section>
                </div>
            @else
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div class="p-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-[#a77d52]/10 rounded-xl">
                                <svg class="w-6 h-6 text-[#a77d52]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7V6a3 3 0 116 0v1M6 11h12M5 17h14a2 2 0 002-2v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Lowongan Tersedia</p>
                                <p class="text-2xl font-bold text-[#a77d52]">{{ $openJobsCount ?? 0 }}</p>
                            </div>
                        </div>
                        <a href="{{ route('jobs.index') }}" class="inline-block mt-4 text-sm text-[#a77d52] hover:underline">Lihat Semua</a>
                    </div>

                    <div class="p-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-[#a77d52]/10 rounded-xl">
                                <svg class="w-6 h-6 text-[#a77d52]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6M9 16h6M9 8h6m-3-5h-1a2 2 0 00-2 2H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Lamaran Saya</p>
                                <p class="text-2xl font-bold text-[#a77d52]">{{ $myApplicationsCount ?? 0 }}</p>
                            </div>
                        </div>
                        <a href="{{ route('applications.mine') }}" class="inline-block mt-4 text-sm text-[#a77d52] hover:underline">Lihat Detail</a>
                    </div>

                    <div class="p-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-[#a77d52]/10 rounded-xl">
                                <svg class="w-6 h-6 text-[#a77d52]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Notifikasi</p>
                                <p class="text-2xl font-bold text-[#a77d52]">{{ $unreadNotificationsCount ?? 0 }}</p>
                            </div>
                        </div>
                        <a href="{{ route('me.notifications.index') }}" class="inline-block mt-4 text-sm text-[#a77d52] hover:underline">Lihat Semua</a>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Lowongan Terbaru</h3>
                        @if(isset($latestJobs) && $latestJobs->count() > 0)
                            <div class="space-y-4">
                                @foreach($latestJobs->take(5) as $job)
                                    <div class="flex items-center justify-between p-4 transition border rounded-lg hover:bg-slate-50">
                                        <div>
                                            <h4 class="font-medium text-gray-900">{{ $job->title }}</h4>
                                            <p class="text-sm text-gray-500">{{ $job->site->code ?? '' }} - {{ $job->site->name ?? '' }}</p>
                                        </div>
                                        <a href="{{ route('jobs.show', $job) }}" class="text-[#a77d52] hover:underline text-sm">Lihat</a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="py-4 text-center text-gray-500">Belum ada lowongan tersedia.</p>
                        @endif
                        <div class="mt-4 text-center">
                            <a href="{{ route('jobs.index') }}" class="text-[#a77d52] hover:underline text-sm">Lihat Semua Lowongan</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
