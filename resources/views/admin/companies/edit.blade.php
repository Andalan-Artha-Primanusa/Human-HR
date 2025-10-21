@extends('layouts.app', ['title' => 'Edit Company'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
  <h1 class="text-2xl font-semibold">Edit Company</h1>
  <form method="post" action="{{ route('admin.companies.update', $record) }}" class="rounded-2xl border p-6 bg-white">
    @method('PUT')
    @include('admin.companies._form')
  </form>
</div>
@endsection
