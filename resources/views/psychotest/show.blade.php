@extends('layouts.karir', [ 'title' => 'Psikotes' ])


@section('content')
<div x-data="psiko()" x-init="init({{ $attempt->test->duration_minutes }})" class="space-y-6">
    <div class="card">
        <div class="card-body flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">Psikotes: {{ $attempt->test->name }}</h1>
                <p class="text-slate-600">Durasi: {{ $attempt->test->duration_minutes }} menit</p>
            </div>
            <div class="text-right">
                <div class="text-xs text-slate-500">Sisa waktu</div>
                <div class="text-2xl font-semibold" :class="{ 'text-red-600': seconds<60 }">@{{ mm }}:@{{ ss }}</div>
            </div>
        </div>
    </div>


    <form method="POST" action="{{ route('psychotest.submit', $attempt) }}" x-ref="form">@csrf
        @foreach($attempt->test->questions->sortBy('order_no') as $q)
        <div class="card">
            <div class="card-body space-y-3">
                <div class="text-sm text-slate-500">No. {{ $q->order_no + 1 }}</div>
                <div class="font-medium text-slate-900">{!! nl2br(e($q->question)) !!}</div>
                @if($q->type==='mcq')
                <div class="grid gap-2">
                    @foreach(($q->options ?? []) as $opt)
                    <label class="flex items-center gap-2">
                        <input class="rounded-md border-slate-300" type="radio" name="answers[{{ $q->id }}]" value="{{ $opt }}">
                        <span class="text-slate-700">{{ $opt }}</span>
                    </label>
                    @endforeach
                </div>
                @elseif($q->type==='truefalse')
                <div class="flex gap-4">
                    <label class="flex items-center gap-2"><input type="radio" name="answers[{{ $q->id }}]" value="true"> <span>Benar</span></label>
                    <label class="flex items-center gap-2"><input type="radio" name="answers[{{ $q->id }}]" value="false"> <span>Salah</span></label>
                </div>
                @endif
            </div>
        </div>
        @endforeach


        <div class="flex items-center justify-end gap-3">
            <button type="button" class="btn btn-outline" @click="confirmSubmit()">Kumpulkan Jawaban</button>
            <button type="submit" class="hidden" x-ref="submitReal"></button>
        </div>
    </form>
</div>


<script>
    function psiko() {
        return {
            seconds: 0,
            mm: '00',
            ss: '00',
            timer: null,
            init(min) {
                this.seconds = (parseInt(min) || 30) * 60;
                this.tick();
                this.timer = setInterval(() => this.tick(), 1000);
            },
            tick() {
                if (this.seconds <= 0) {
                    clearInterval(this.timer);
                    this.$refs.form.submit();
                    return;
                }
                this.seconds--;
                const m = Math.floor(this.seconds / 60),
                    s = this.seconds % 60;
                this.mm = ('' + m).padStart(2, '0');
                this.ss = ('' + s).padStart(2, '0');
            },
            confirmSubmit() {
                if (confirm('Kumpulkan jawaban sekarang?')) this.$refs.form.submit();
            }
        }
    }
</script>
@endsection