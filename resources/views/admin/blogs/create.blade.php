@extends('admin.layout')

@section('page_title','Add Blog')

@section('content')
@if(session('info'))
  <div class="mb-4 rounded-lg bg-amber-50 text-amber-900 px-4 py-2 border border-amber-200">
    {{ session('info') }}
  </div>
@endif

<form
  x-data="blogForm()"
  x-init="init()"
  method="POST"
  action="{{ route('admin.blogs.store') }}"
  enctype="multipart/form-data"
  class="grid lg:grid-cols-3 gap-6"
>
  @csrf

  {{-- LEFT: Main fields --}}
  <div class="lg:col-span-2 space-y-6">
    {{-- Title + Slug --}}
    <div class="rounded-xl border bg-white p-5">
      <label class="block text-sm font-semibold mb-1">Article Title</label>
      <input type="text" name="title" x-model="title" @input="autoSlug()" class="w-full rounded-lg border-gray-300" placeholder="Enter title" required>

      <div class="mt-4">
        <label class="block text-sm font-semibold mb-1">Slug</label>
        <input type="text" name="slug" x-model="slug" class="w-full rounded-lg border-gray-300" placeholder="auto-generated if left blank">
      </div>
    </div>

    {{-- Excerpt --}}
    <div class="rounded-xl border bg-white p-5">
      <label class="block text-sm font-semibold mb-2">Excerpt</label>
      <textarea name="excerpt" rows="3" class="w-full rounded-lg border-gray-300" placeholder="Short summary (optional)"></textarea>
    </div>

    {{-- Body (WYSIWYG coming in Step 2) --}}
    <div class="rounded-xl border bg-white p-5">
      <div class="flex items-center justify-between">
        <label class="block text-sm font-semibold">Body (HTML, images, embeds)</label>
        <span class="text-xs text-gray-500">We’ll enable a WordPress-like editor in Step 2</span>
      </div>
      <textarea id="body" name="body" rows="14" class="w-full rounded-lg border-gray-300" placeholder="Write your article here..." required></textarea>
    </div>
  </div>

  {{-- RIGHT: Sidebar fields --}}
  <aside class="space-y-6">
    {{-- Thumbnail --}}
    <div class="rounded-xl border bg-white p-5">
      <label class="block text-sm font-semibold mb-2">Thumbnail</label>
      <div class="aspect-[16/9] bg-gray-100 rounded-lg overflow-hidden mb-3">
        <img x-show="thumbUrl" :src="thumbUrl" class="w-full h-full object-cover" alt="">
      </div>
      <input type="file" name="featured_image" @change="previewThumb" accept="image/*" class="w-full rounded-lg border-gray-300">
      <p class="text-xs text-gray-500 mt-2">JPG/PNG/WebP/GIF up to 4MB.</p>
    </div>

    {{-- Categories (client-side add/remove for now) --}}
    <div class="rounded-xl border bg-white p-5">
      <label class="block text-sm font-semibold mb-2">Category</label>
      <select name="categories[]" multiple class="w-full rounded-lg border-gray-300 min-h-[120px]" x-ref="catSelect">
        {{-- We’ll load real categories + server add/delete in Step 3 --}}
      </select>

      <div class="mt-3 flex gap-2">
        <input x-model="newCategory" type="text" class="flex-1 rounded-lg border-gray-300" placeholder="New category name">
        <button type="button" @click="addCategoryLocal" class="px-3 py-2 rounded-lg bg-slate-900 text-white">Add</button>
      </div>
      <div class="mt-2">
        <button type="button" @click="removeSelectedCategories" class="text-sm text-red-600 hover:underline">Delete selected</button>
      </div>
    </div>

    {{-- Hashtags --}}
    <div class="rounded-xl border bg-white p-5">
      <label class="block text-sm font-semibold mb-2">Hashtags (comma-separated)</label>
      <input type="text" name="tags" class="w-full rounded-lg border-gray-300" placeholder="e.g. beach, family, palawan">
    </div>

    {{-- Scheduling --}}
    <div class="rounded-xl border bg-white p-5 space-y-4">
      <label class="block text-sm font-semibold">Scheduling</label>

      <div class="space-y-2">
        <label class="inline-flex items-center gap-2">
          <input type="radio" name="publish_mode" value="now" class="rounded" checked>
          <span class="text-sm">Publish now</span>
        </label>
        <label class="inline-flex items-center gap-2">
          <input type="radio" name="publish_mode" value="schedule" class="rounded">
          <span class="text-sm">Schedule</span>
        </label>
      </div>

      <div class="grid gap-3">
        <div>
          <label class="block text-xs font-semibold mb-1">Publish at</label>
          <input type="datetime-local" name="published_at" class="w-full rounded-lg border-gray-300">
        </div>
        <div>
          <label class="block text-xs font-semibold mb-1">End date (expires)</label>
          <input type="datetime-local" name="expires_at" class="w-full rounded-lg border-gray-300">
        </div>
      </div>
    </div>

    {{-- Publish / Draft --}}
    <div class="rounded-xl border bg-white p-5">
      <div class="flex gap-2">
        <button type="submit" name="action" value="draft" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">Save Draft</button>
        <button type="submit" name="action" value="publish" class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Publish</button>
      </div>
      <p class="text-xs text-gray-500 mt-2">Buttons work but won’t save yet (Step 2 wires backend).</p>
    </div>
  </aside>
</form>

<script>
function blogForm(){
  return {
    title: '',
    slug: '',
    thumbUrl: null,
    newCategory: '',

    init(){ /* reserved */ },

    autoSlug(){
      if(this.slug?.length) return;
      this.slug = this.slugify(this.title);
    },
    slugify(s) {
      return (s ?? '')
        .toString()
        .normalize('NFKD')
        .replace(/[\u0300-\u036f]/g,'')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g,'-')
        .replace(/(^-|-$)/g,'');
    },
    previewThumb(e){
      const [file] = e.target.files || [];
      this.thumbUrl = file ? URL.createObjectURL(file) : null;
    },

    // Client-side category add/remove (temporary)
    addCategoryLocal(){
      const name = this.newCategory.trim();
      if(!name) return;
      const sel = this.$refs.catSelect;
      const exists = Array.from(sel.options).some(o => o.text.toLowerCase() === name.toLowerCase());
      if(exists){ this.newCategory=''; return; }
      const opt = new Option(name, `local:${name}`, true, true);
      sel.add(opt);
      this.newCategory = '';
    },
    removeSelectedCategories(){
      const sel = this.$refs.catSelect;
      Array.from(sel.selectedOptions).forEach(o => o.remove());
    }
  }
}
</script>
@endsection
