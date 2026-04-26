<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        $user = auth()->user();
        $role = $user->role ?? 'pelamar';
        $isAdmin = in_array($role, ['superadmin', 'hr', 'admin']);
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($isAdmin)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="text-center py-8">
                            <div class="text-6xl mb-4">👔</div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Admin Dashboard</h3>
                            <p class="text-gray-600 mb-6"> Anda akan diarahkan ke halaman admin...</p>
                            <a href="{{ route('admin.dashboard.manpower') }}" 
                               class="inline-flex items-center px-4 py-2 bg-[#a77d52] text-white rounded-lg hover:opacity-90 transition">
                                Buka Dashboard Admin
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
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
                        <a href="{{ route('jobs.index') }}" class="mt-4 inline-block text-sm text-[#a77d52] hover:underline">Lihat Semua →</a>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
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
                        <a href="{{ route('applications.mine') }}" class="mt-4 inline-block text-sm text-[#a77d52] hover:underline">Lihat Detail →</a>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
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
                        <a href="{{ route('me.notifications.index') }}" class="mt-4 inline-block text-sm text-[#a77d52] hover:underline">Lihat Semua →</a>
                    </div>
                </div>

                <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Lowongan Terbaru</h3>
                        @if(isset($latestJobs) && $latestJobs->count() > 0)
                            <div class="space-y-4">
                                @foreach($latestJobs->take(5) as $job)
                                    <div class="flex items-center justify-between p-4 border rounded-lg hover:bg-slate-50 transition">
                                        <div>
                                            <h4 class="font-medium text-gray-900">{{ $job->title }}</h4>
                                            <p class="text-sm text-gray-500">{{ $job->site->code ?? '' }} • {{ $job->site->name ?? '' }}</p>
                                        </div>
                                        <a href="{{ route('jobs.show', $job) }}" class="text-[#a77d52] hover:underline text-sm">Lihat</a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">Belum ada lowongan tersedia.</p>
                        @endif
                        <div class="mt-4 text-center">
                            <a href="{{ route('jobs.index') }}" class="text-[#a77d52] hover:underline text-sm">Lihat Semua Lowongan →</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>