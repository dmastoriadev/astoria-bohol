{{-- resources/views/admin/partials/category-manager.blade.php --}}
@php
  // Expect: $categories (Collection of {id,name,slug})
  // Optional: $current (selected category id)
  $cats = collect($categories ?? [])->map(fn($c)=> (object)[
    'id'   => $c->id,
    'name' => $c->name,
    'slug' => $c->slug,
  ])->values();
  $currentId = old('article_category_id', $current ?? null);

  // We’ll use this as a template to build delete URLs via JS
  $destroyUrlTpl = route('admin.categories.destroy', ['category' => '__ID__']);
@endphp

<div
  x-data="catMgr({
    cats: @json($cats),
    selectedId: @json($currentId),
    destroyUrlTpl: @json($destroyUrlTpl),
    storeUrl: @json(route('admin.categories.store')),
    csrf: @json(csrf_token()),
  })"
  x-cloak
  class="rounded-xl border bg-white p-5 space-y-5"
>
  {{-- Reuse existing --}}
  <div>
    <label class="block text-sm font-semibold mb-1">Category</label>
    <select name="article_category_id" x-model="selectedId" class="w-full rounded-lg border-gray-300">
      <option value="">— None —</option>
      <template x-for="c in cats" :key="c.id">
        <option :value="String(c.id)" x-text="c.name"></option>
      </template>
    </select>
  </div>

  <div class="border-t pt-4 space-y-3">
    <div class="flex items-center justify-between">
      <label class="block text-sm font-semibold">Manage Categories</label>
      <span class="text-xs text-gray-500">Add or remove categories</span>
    </div>

    {{-- Add new (name only) --}}
    <div class="flex items-center gap-2">
      <input type="text"
             x-model.trim="newName"
             @keydown.enter.prevent="createCategory()"
             class="w-full rounded-lg border-gray-300"
             placeholder="e.g. News, Updates">
      <button type="button"
              @click="createCategory()"
              :disabled="!canCreate || loading"
              class="px-3 py-2 rounded-lg text-white"
              :class="(!canCreate || loading) ? 'bg-gray-400 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700'">
        Add
      </button>
    </div>

    {{-- Flash message --}}
    <template x-if="flash">
      <p class="text-xs"
         :class="flashType==='error' ? 'text-red-600' : 'text-emerald-700'"
         x-text="flash"></p>
    </template>

    {{-- Existing list with delete --}}
    <div class="mt-2 grid gap-2">
      <template x-for="c in cats" :key="c.id">
        <div class="flex items-center justify-between rounded-lg border px-3 py-2">
          <div class="text-sm font-medium" x-text="c.name"></div>
          <button type="button"
                  @click="deleteCategory(c.id, c.name)"
                  :disabled="loading"
                  class="inline-flex items-center gap-1.5 text-sm rounded border px-2 py-1 hover:bg-red-50 text-red-600">
            <i class="fa-regular fa-trash-can"></i> Remove
          </button>
        </div>
      </template>
      <p class="text-xs text-gray-500" x-show="!cats.length">No categories yet.</p>
    </div>
  </div>
</div>

@push('scripts')
<script>
(() => {
  // Small helper that tries JSON; if it fails, returns null.
  async function tryJson(res) {
    try { return await res.json(); } catch (_) { return null; }
  }

  window.catMgr = function catMgr(opts){
    return {
      // ----- state -----
      cats: Array.isArray(opts.cats) ? opts.cats.slice() : [],
      selectedId: opts.selectedId ? String(opts.selectedId) : '',
      newName: '',
      loading: false,
      flash: '',
      flashType: 'success',
      storeUrl: opts.storeUrl,
      destroyUrlTpl: opts.destroyUrlTpl,
      csrf: opts.csrf,

      // ----- computed -----
      get canCreate(){
        const name = (this.newName || '').trim().toLowerCase();
        if (!name) return false;
        return !this.cats.some(c => (c.name || '').trim().toLowerCase() === name);
      },

      // ----- actions -----
      async createCategory(){
        if (!this.canCreate || this.loading) return;
        this.loading = true; this.flash = '';

        try {
          const res = await fetch(this.storeUrl, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': this.csrf,
              'Accept': 'application/json',
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ name: this.newName })
          });

          if (res.ok) {
            const data = await tryJson(res);
            if (data && data.id) {
              this.cats.push({ id: data.id, name: data.name, slug: data.slug || '' });
              this.selectedId = String(data.id);
              this.newName = '';
              this.flash = 'Category created.';
              this.flashType = 'success';
            } else {
              // Fallback if controller redirects instead of JSON
              window.location.reload();
            }
          } else {
            const text = (await res.text()).slice(0, 200);
            this.flash = text || 'Failed to create category.';
            this.flashType = 'error';
          }
        } catch (e) {
          this.flash = 'Network error. Please try again.';
          this.flashType = 'error';
        } finally {
          this.loading = false;
        }
      },

      async deleteCategory(id, name){
        if (this.loading) return;
        if (!confirm(`Delete category “${name}”? Any articles using it will be set to “None”.`)) return;

        this.loading = true; this.flash = '';

        try {
          const url = this.destroyUrlTpl.replace('__ID__', id);
          const form = new FormData();
          form.append('_token', this.csrf);
          form.append('_method', 'DELETE');

          const res = await fetch(url, { method: 'POST', body: form });

          if (res.ok) {
            this.cats = this.cats.filter(c => String(c.id) !== String(id));
            if (String(this.selectedId) === String(id)) this.selectedId = '';
            this.flash = 'Category removed.';
            this.flashType = 'success';
          } else {
            const text = (await res.text()).slice(0, 200);
            this.flash = text || 'Failed to delete category.';
            this.flashType = 'error';
          }
        } catch (e) {
          this.flash = 'Network error. Please try again.';
          this.flashType = 'error';
        } finally {
          this.loading = false;
        }
      }
    };
  };
})();
</script>
@endpush
