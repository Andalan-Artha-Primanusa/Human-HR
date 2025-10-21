@extends('layouts.app', ['title' => 'Create Company'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
  <h1 class="text-2xl font-semibold">Create Company</h1>
  <form method="post" action="{{ route('admin.companies.store') }}" class="rounded-2xl border p-6 bg-white">
    @include('admin.companies._form')
  </form>
</div>
@endsection
