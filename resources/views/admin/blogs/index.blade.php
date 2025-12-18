@extends('admin.layout')
@section('page_title','Blogs')
@section('content')
  <a href="{{ route('admin.blogs.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white">New Article</a>
  <div class="mt-4 rounded-xl border bg-white p-6 text-gray-600">Blog list coming soon.</div>
@endsection
