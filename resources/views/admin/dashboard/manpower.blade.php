@extends('layouts.karir', [ 'title' => 'Admin · Manpower Dashboard' ])


@section('content')
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    <div class="card">
        <div class="card-body">
            <div class="text-slate-600">Open Jobs</div>
            <div class="text-2xl font-semibold">{{ $openJobs }}</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="text-slate-600">Active Applicants</div>
            <div class="text-2xl font-semibold">{{ $activeApps }}</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="text-slate-600">Headcount Budget</div>
            <div class="text-2xl font-semibold">{{ $budget }}</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="text-slate-600">Fulfillment</div>
            <div class="text-2xl font-semibold">{{ $fulfillment }}%</div>
        </div>
    </div>
</div>


<div class="card">
    <div class="card-body">
        <h2 class="font-semibold text-slate-900 mb-4">Pipeline by Stage</h2>
        <canvas id="byStageChart"></canvas>
    </div>
</div>


<script>
    const stageData = @json($byStage);
    const labels = Object.keys(stageData);
    const values = Object.values(stageData);


    const ctx = document.getElementById('byStageChart');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Applications',
                data: values
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endsection