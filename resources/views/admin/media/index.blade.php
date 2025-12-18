{{-- resources/views/admin/media/index.blade.php --}}
@extends('admin.layout')
@section('page_title', 'Library')

@push('head')
  <style>[x-cloak]{ display:none !important; }</style>
  <style>
    .media-grid img { -webkit-user-drag: none; user-drag: none; }
    /* SVG sizing that won’t get purged by Tailwind */
    .media-grid .file-svg    { width: 56px; height: 56px; display: block; }
    .media-grid .file-svg-lg { width: 72px; height: 72px; display: block; }

    .media-grid .delete-btn { position:absolute; top:.5rem; right:.5rem; z-index:20; }
    .media-grid .select-cb { position:absolute; top:.5rem; left:.5rem; z-index:20; }

    /* Simple spinner for overlay */
    .spin-border{
      border-radius:9999px;
      border-width:3px;
      border-style:solid;
      border-top-color:transparent;
      animation:spin 0.8s linear infinite;
    }
    @keyframes spin{
      to{ transform:rotate(360deg); }
    }
  </style>

  {{-- Font Awesome (no integrity to avoid SRI mismatch) --}}
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        referrerpolicy="no-referrer" />

  {{-- Toast hub + Library component --}}
  <script>
  // --- Minimal self-contained toast hub (TOP-RIGHT, bigger, de-duped) ---
  window.toastHub = function () {
    return {
      toasts: [], _lastMsg: '', _lastType: '', _lastAt: 0,
      init(){
        window.__toastHubPush = (msg, type='success') => this.push(msg, type);
        window.addEventListener('notify', (e) => {
          const d = e.detail || {};
          this.push(d.message || String(d), d.type || 'success');
        });
      },
      push(msg, type='success'){
        const now = Date.now();
        if (this._lastMsg === msg && this._lastType === type && (now - this._lastAt) < 400) return;
        this._lastMsg = msg; this._lastType = type; this._lastAt = now;
        const id = now + Math.random(); this.toasts.push({ id, msg, type });
        setTimeout(() => this.dismiss(id), 4600);
      },
      dismiss(id){ this.toasts = this.toasts.filter(t => t.id !== id); },
      // success uses your brand color #105702
      tone(t){ return ({
        success: 'bg-[#105702] text-white',
        danger:  'bg-red-600 text-white',
        error:   'bg-red-600 text-white',
        info:    'bg-slate-700 text-white',
        warning: 'bg-amber-600 text-white',
      })[t] || 'bg-slate-700 text-white'; },
      icon(t){ return ({
        success:'fa-circle-check',
        danger:'fa-trash-can',
        error:'fa-circle-xmark',
        info:'fa-circle-info',
        warning:'fa-triangle-exclamation'
      })[t] || 'fa-bell'; },
    }
  }

  // --- Library page component (automatic WebP + resize on upload) ---
  window.libraryPage = function(opts = {}) {
    return {
      loading:false, errorMsg:'', q:'', files:[],
      _internalDrag:false,
      fetchUrl:  opts.fetchUrl  || '',
      uploadUrl: opts.uploadUrl || null,
      deleteUrl: opts.deleteUrl || null,
      updateUrl: opts.updateUrl || null,

      // paging/view-more
      visibleLimit:16, moreStep:32,

      // filters/sort
      filterMode:'all',   // all | images | files
      sortMode:'newest',  // newest | oldest

      // back to top
      showBackTop:false,

      // modal state
      showModal:false,
      active:null,
      form:{ name:'', alt:'' },
      isSaving:false,

      // selection state
      selectedKeys: [],

      // upload overlay state
      isUploading:false,
      uploadPhase:'',
      uploadTotal:0,
      uploadDone:0,
      uploadInternalProgress:0,

      // delete overlay state
      isDeleting:false,
      deleteTotal:0,
      deleteDone:0,

      // automatic conversion config (no UI)
      webpQuality:82,        // 1..100 (mapped to 0..1)
      resizeMaxDim:2560,     // longest edge cap
      skipAnimatedGif:true,  // preserve GIF animation by skipping conversion
      _webpSupported: null,

      // ---- filename helpers
      sanitizeBaseName(str){
        const s = (str || '').trim()
          .replace(/\.[^.]+$/,'')
          .replace(/[\s]+/g,'-')
          .replace(/[^A-Za-z0-9._-]+/g,'')
          .replace(/-+/g,'-')
          .replace(/^[-_.]+|[-_.]+$/g,'');
        return s || 'file';
      },
      currentExt(f){
        const u = (f?.full || f?.url || f?.path || '').split('?')[0].toLowerCase();
        const m = u.match(/\.([a-z0-9]+)(?:$|[#?])/i);
        return (m?.[1] || '').toLowerCase();
      },
      buildTargetName(f, desiredName){
        const base = this.sanitizeBaseName(desiredName);
        const ext  = this.currentExt(f);
        return ext ? `${base}.${ext}` : base;   // enforce same extension
      },
      namesEqualCaseInsensitive(a,b){ return String(a||'').toLowerCase() === String(b||'').toLowerCase(); },

      async _detectWebp(){
        if (this._webpSupported !== null) return this._webpSupported;
        try{
          const c = document.createElement('canvas');
          if (!c.getContext) return (this._webpSupported = false);
          this._webpSupported = c.toDataURL('image/webp').startsWith('data:image/webp');
          return this._webpSupported;
        }catch(_){ return (this._webpSupported = false); }
      },

      init(){
        this.load();
        this.$watch('q', ()=> { this.resetLimiter(); this.$nextTick(()=>this.updateMaster()); });
        this.$watch('filterMode', ()=> { this.resetLimiter(); this.$nextTick(()=>this.updateMaster()); });
        this.$watch('sortMode', ()=> { this.resetLimiter(); this.$nextTick(()=>this.updateMaster()); });
        this.$watch('visibleLimit', ()=> this.$nextTick(()=>this.updateMaster()));
        this.$watch('files', ()=> this.$nextTick(()=>this.updateMaster()));
        this.$watch('selectedKeys', ()=> this.$nextTick(()=>this.updateMaster()));

        this._onScroll = () => { this.showBackTop = window.scrollY > 320; };
        window.addEventListener('scroll', this._onScroll, { passive:true });
        this._onScroll();

        // paste-to-upload
        window.addEventListener('paste', (e)=>{
          const items = e.clipboardData?.items || [];
          const blobs = [];
          for (const it of items) { if (it.kind === 'file') blobs.push(it.getAsFile()); }
          if (blobs.length) { e.preventDefault(); this.upload(blobs); }
        }, { passive:false });
      },
      destroy(){
        if (this._onScroll) window.removeEventListener('scroll', this._onScroll);
      },

      scrollTop(){
        try { window.scrollTo({top:0, behavior:'smooth'}); }
        catch { window.scrollTo(0,0); }
      },

      // ---------- helpers ----------
      notify(msg, type='success'){
        if (typeof window.__toastHubPush === 'function') window.__toastHubPush(msg, type);
        else {
          try { window.dispatchEvent(new CustomEvent('notify', { detail:{ type, message: msg } })); }
          catch {}
        }
      },
      joinNames(list, cap=3){
        const arr = (list || []).filter(Boolean); if (!arr.length) return '';
        if (arr.length <= cap) return arr.join(', ');
        return `${arr.slice(0,cap).join(', ')} + ${arr.length-cap} more`;
      },

      resetLimiter(){ this.visibleLimit = 16; this.moreStep = 32; },
      showMore(){
        const total = this.filtered().length;
        this.visibleLimit = Math.min(total, this.visibleLimit + this.moreStep);
        this.moreStep = this.moreStep * 2;
        this.$nextTick(()=>this.updateMaster());
      },
      canShowMore(){ return this.visibleLimit < this.filtered().length; },

      // cache-bust new uploads so previews update instantly
      bust(u){
        if(!u) return u;
        const sep = u.includes('?') ? '&' : '?';
        return u + sep + 'v=' + Date.now();
      },

      toAbs(u){
        if(!u) return '';
        if(/^https?:\/\//i.test(u)) return u;
        if(u.startsWith('//'))      return location.protocol + u;
        if(u.startsWith('/'))       return location.origin + u;
        if(u.startsWith('storage/'))return location.origin + '/' + u;
        if(u.startsWith('public/')) return location.origin + '/storage/' + u.slice(7);
        return location.origin + '/storage/' + u.replace(/^public\//,'');
      },

      normalize(list){
        return (list || []).map((f,i)=>{
          const name   = f?.name || f?.filename || f?.original_name || '';
          const fullR  = f?.url ?? f?.full ?? f?.original ?? f?.location ?? f?.path ?? f?.filepath ?? '';
          const thmbR  = f?.thumb ?? f?.thumbnail ?? f?.thumbnail_url ?? f?.preview ?? f?.small ?? '';
          const mime   = f?.mime || f?.mimetype || f?.content_type || '';
          const full   = this.toAbs(String(fullR));
          const thumb  = this.toAbs(String(thmbR || fullR));
          const alt    = f?.alt ?? f?.alt_text ?? f?.altText ?? f?.description ?? '';

          const original_url   = this.toAbs(String(
            f?.original_url || f?.download_url || f?.source_url || f?.file_url || f?.document_url || ''
          ));
          const extension_hint = (f?.extension || f?.ext || f?.file_extension || f?.original_extension || '').toString();

          return {
            ...f,
            name, mime, full, thumb, alt,
            original_url, extension_hint,
            broken:false, __deleting:false,
            __key: f?.id ?? f?.uuid ?? f?.path ?? f?.url ?? `idx_${i}`,
            __pos: i,
          };
        });
      },

      async load(){
        this.loading = true; this.errorMsg = '';
        try{
          const url = this.fetchUrl + (this.fetchUrl.includes('?') ? '&' : '?') + 't=' + Date.now();
          const res = await fetch(url, {
            headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' },
            credentials:'same-origin',
            cache: 'no-store'
          });

          if (!res.ok){ this.errorMsg = `Library fetch failed (${res.status}).`; this.files=[]; return; }
          const ct = (res.headers.get('content-type') || '').toLowerCase();
          if (!ct.includes('application/json')){ this.errorMsg = 'Expected JSON but got HTML (wrong route?).'; this.files=[]; return; }

          const data = await res.json();
          const raw = Array.isArray(data) ? data
                   : Array.isArray(data?.files) ? data.files
                   : Array.isArray(data?.data)  ? data.data
                   : Array.isArray(data?.items) ? data.items
                   : Array.isArray(data?.payload) ? data.payload
                   : [];
          this.files = this.normalize(raw);
          this.resetLimiter();
          this.$nextTick(()=>this.updateMaster());
        } catch(e){
          this.errorMsg = 'Network error while loading Library.'; this.files=[];
        } finally { this.loading = false; }
      },

      // ---- type helpers
      ext(f){
        const DOCS   = ['pdf','doc','docx','rtf','odt','xls','xlsx','csv','ods','ppt','pptx','odp'];
        const IMAGES = ['png','jpg','jpeg','gif','webp','bmp','svg','avif'];
        const ARCH   = ['zip','rar','7z','tar','gz'];
        const VIDEO  = ['mp4','webm','mov','avi','mkv'];
        const AUDIO  = ['mp3','wav','ogg','m4a','flac'];

        // 1) MIME hints
        const mimes = [];
        for (const [k,v] of Object.entries(f || {})) {
          if (typeof v === 'string' && /mime/i.test(k)) mimes.push(v.toLowerCase());
        }
        if (mimes.some(x => x.includes('pdf')))                              return 'pdf';
        if (mimes.some(x => x.includes('word')))                             return 'docx';
        if (mimes.some(x => x.includes('excel') || x.includes('sheet')))     return 'xlsx';
        if (mimes.some(x => x.includes('powerpoint') || x.includes('presentation'))) return 'pptx';

        // 2) parse extensions with weights
        const preferKey   = k => /original|download|source|file|document|name|filename/i.test(k);
        const penalizeKey = k => /thumb|thumbnail|small|preview|resize|converted|medium|large/i.test(k);

        const candidates = [];
        const push = (ext, w) => { if (ext) candidates.push({ ext: ext.toLowerCase(), w }); };

        for (const [k,v] of Object.entries(f || {})) {
          if (typeof v !== 'string' || !v) continue;
          const key  = k.toLowerCase();
          const base = v.toLowerCase().split(/[?#]/)[0];

          let w = 0;
          if (preferKey(key))   w += 6;
          if (penalizeKey(key)) w -= 6;
          if (/(^|\/)(thumb|thumbnail|preview|small|resize)[\/_.-]/.test(base)) w -= 4;

          const all = [...base.matchAll(/\.([a-z0-9]{2,7})(?=$|[\/._-])/g)];
          all.forEach((m, idx) => {
            const ext = m[1];
            const posBonus = Math.max(0, 4 - idx);
            const typeBonus =
              (DOCS.includes(ext)  ? 40 :
               ARCH.includes(ext)  ? 30 :
               VIDEO.includes(ext) ? 20 :
               AUDIO.includes(ext) ? 20 :
               IMAGES.includes(ext) ? 0 : 10);
            push(ext, w + posBonus + typeBonus);
          });
        }

        const best = candidates.reduce((acc,c)=>((!acc[c.ext] || c.w>acc[c.ext])&&(acc[c.ext]=c.w),acc),{});
        const order = ['pdf','docx','doc','rtf','xlsx','xls','csv','pptx','ppt',
                       'zip','rar','7z','tar','gz','mp4','webm','mov','avi','mkv',
                       'mp3','wav','ogg','m4a','flac','png','jpg','jpeg','gif','webp','bmp','svg','avif'];

        let top = ''; let topW = -Infinity;
        for (const [ext,w] of Object.entries(best)) {
          if (w > topW || (w === topW && order.indexOf(ext) < order.indexOf(top))) {
            top = ext; topW = w;
          }
        }
        return top;
      },

      isImage(f){
        const e = this.ext(f);
        if (['pdf','doc','docx','odt','rtf','xls','xlsx','ods','csv','ppt','pptx','odp','zip','rar','7z','tar','gz'].includes(e)) {
          return false;
        }
        const m = (f?.mime || '').toLowerCase();
        if (m.startsWith?.('image/')) return true;
        return ['png','jpg','jpeg','gif','webp','bmp','svg','avif'].includes(e);
      },

      // ---- robust date extractor for sorting
      fileDateMs(f){
        const keys = [
          'created_at','updated_at','uploaded_at','modified_at','date','datetime','timestamp','time',
          'createdAt','updatedAt','uploadedAt','modifiedAt','lastModified','last_modified',
          'mtime','mtime_ms','ctime','ctime_ms','timeCreated','time_updated','time_created'
        ];
        for (const k of keys) {
          if (f && f[k] != null) {
            const v = f[k];
            if (typeof v === 'number') return v > 1e12 ? v : v * 1000;
            const t = Date.parse(v);
            if (!Number.isNaN(t)) return t;
          }
        }
        return 0;
      },

      // ---- filtering + sorting
      filtered(){
        const k = this.q.trim().toLowerCase();
        let arr = this.files;

        if (k) {
          arr = arr.filter(f =>
            (f.name||'').toLowerCase().includes(k) ||
            (f.full||'').toLowerCase().includes(k) ||
            (f.thumb||'').toLowerCase().includes(k) ||
            (f.alt||'').toLowerCase().includes(k)
          );
        }
        if (this.filterMode === 'images') {
          arr = arr.filter(f => this.isImage(f));
        } else if (this.filterMode === 'files') {
          arr = arr.filter(f => !this.isImage(f));
        }

        const newestFirst = this.sortMode === 'newest';
        return arr.slice().sort((a,b) => {
          const ta = this.fileDateMs(a), tb = this.fileDateMs(b);
          const hasA = ta > 0, hasB = tb > 0;

          if (hasA && hasB) return newestFirst ? (tb - ta) : (ta - tb);
          if (hasA && !hasB) return -1;
          if (!hasA && hasB) return 1;

          // stable fallback by original order
          return newestFirst ? (a.__pos - b.__pos) : (b.__pos - a.__pos);
        });
      },
      visible(){ return this.filtered().slice(0, this.visibleLimit); },

      // ---- selection helpers ----
      pageKeys(){
        return this.visible().map(f => String(f.__key));
      },
      isSelectedKey(k){
        return this.selectedKeys.includes(String(k));
      },
      toggleAll(checked){
        const keys = this.pageKeys();
        if (checked) {
          const set = new Set(this.selectedKeys);
          keys.forEach(k => set.add(String(k)));
          this.selectedKeys = Array.from(set);
        } else {
          this.selectedKeys = this.selectedKeys.filter(k => !keys.includes(k));
        }
        this.$nextTick(()=>this.updateMaster());
      },
      onRowToggle(e, key){
        const k = String(key);
        if (e?.target?.checked) {
          if (!this.selectedKeys.includes(k)) this.selectedKeys.push(k);
        } else {
          this.selectedKeys = this.selectedKeys.filter(v => v !== k);
        }
        this.$nextTick(()=>this.updateMaster());
      },
      clearSelection(){
        this.selectedKeys = [];
        this.$nextTick(()=>this.updateMaster());
      },
      updateMaster(){
        const master = this.$refs.master;
        if (!master) return;
        const keys = this.pageKeys();
        const total = keys.length;
        const selectedOnPage = keys.filter(k => this.selectedKeys.includes(k)).length;
        master.checked = (total > 0 && selectedOnPage === total);
        master.indeterminate = (selectedOnPage > 0 && selectedOnPage < total);
      },
      getSelectedItems(){
        const set = new Set(this.selectedKeys);
        return this.files.filter(f => set.has(String(f.__key)));
      },

      // ---- actions
      bestIdentity(f){
        const payload = {};
        if (f?.id) payload.id = f.id;
        else if (f?.uuid) payload.uuid = f.uuid;
        else if (f?.path) payload.path = f.path;
        else if (f?.url || f?.full) payload.url = f.url || f.full;
        return payload;
      },

      openEditor(f){
        this.active = f;
        this.form.name = f?.name || '';
        this.form.alt  = this.isImage(f) ? (f?.alt || '') : '';  // only for images
        this.showModal = true;

        this.$nextTick(() => {
          const el = document.getElementById('media-name-input');
          if (el) el.focus();
        });
      },

      async saveMeta(){
        if (!this.active) return;
        this.isSaving = true;

        const desired    = (this.form.name || '').trim();
        const targetName = this.buildTargetName(this.active, desired || this.active.name || '');
        const willRename = !this.namesEqualCaseInsensitive(targetName, this.active.name || '');

        const applyLocal = (payload = {}) => {
          if (payload.name) this.active.name = payload.name;
          if (typeof payload.alt !== 'undefined') this.active.alt = payload.alt;
          if (payload.full)  this.active.full  = this.bust(this.toAbs(payload.full));
          if (payload.thumb) this.active.thumb = this.bust(this.toAbs(payload.thumb));
          if (payload.path)  this.active.path  = payload.path;
          if (payload.url)   this.active.url   = payload.url;
        };

        try{
          if (!this.updateUrl) {
            applyLocal({ name: targetName, alt: this.form.alt });
            this.notify('Saved (local only — update endpoint not configured).','info');
            this.showModal = false; return;
          }

          const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
          const form  = new FormData();
          const idPayload = this.bestIdentity(this.active);
          Object.entries(idPayload).forEach(([k,v]) => form.append(k, v));

          form.append('name', targetName);
          if (willRename) {
            form.append('rename', '1');
            form.append('target_name', targetName);
          }
          if (this.isImage(this.active)) {
            form.append('alt', this.form.alt || '');
          }

          form.append('_method','PATCH');
          if (token) form.append('_token', token);

          const r = await fetch(this.updateUrl, {
            method: 'POST',
            body: form,
            credentials:'same-origin',
            headers:{ 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest', ...(token ? {'X-CSRF-TOKEN': token} : {}) }
          });

          if (r.ok) {
            let d = null;
            try { d = await r.json(); } catch {}

            if (d) {
              let newPath = d.path;
              if (!newPath && d.url) {
                try {
                  const u = new URL(d.url, location.origin);
                  newPath = u.pathname.replace(/^\/?storage\//,'');
                } catch {}
              }
              applyLocal({
                name:  typeof d.name  !== 'undefined' ? d.name  : targetName,
                alt:   typeof d.alt   !== 'undefined' ? d.alt   : this.form.alt,
                full:  d.url || d.full,
                thumb: d.thumb || d.thumbnail || d.preview,
                path:  newPath,
                url:   d.url
              });
            } else {
              applyLocal({ name: targetName, alt: this.form.alt });
            }

            this.notify(willRename ? 'File renamed.' : 'Metadata saved.','success');
            this.showModal = false;

          } else {
            let msg = `Save failed (${r.status}).`;
            try {
              const ct = r.headers.get('content-type') || '';
              if (ct.includes('application/json')) {
                const jd = await r.json();
                if (jd?.message) msg = jd.message;
              } else {
                const txt = await r.text();
                if (txt) msg = txt.slice(0, 300);
              }
            } catch {}
            this.notify(msg, 'error');
          }
        } catch(e){
          this.notify('Network error while saving.','error');
        } finally {
          this.isSaving = false;
        }
      },

      faIcon(f){
        const e = this.ext(f);
        const m = (f?.mime || '').toLowerCase().split(';')[0].trim();

        if (m.includes('pdf') || e === 'pdf') return 'fa-solid fa-file-pdf';
        if (m.includes('word') || ['doc','docx','odt','rtf'].includes(e)) return 'fa-solid fa-file-word';
        if (m.includes('sheet') || m.includes('excel') || ['xls','xlsx','ods','csv'].includes(e)) return 'fa-solid fa-file-excel';
        if (m.includes('presentation') || m.includes('powerpoint') || ['ppt','pptx','odp'].includes(e)) return 'fa-solid fa-file-powerpoint';
        if ((m.startsWith('video/') && m !== 'video/quicktime') || ['mp4','webm','mov','avi','mkv'].includes(e)) return 'fa-solid fa-file-video';
        if (m.startsWith('audio/') || ['mp3','wav','ogg','m4a','flac'].includes(e)) return 'fa-solid fa-file-audio';
        if (['zip','rar','7z','tar','gz'].includes(e)) return 'fa-solid fa-file-zipper';
        if (['js','ts','json','html','css','php','py','java','rb','go','c','cpp','cs','sh','yml','yaml','xml','md','markdown','txt','log'].includes(e)) return 'fa-solid fa-file-code';
        return 'fa-solid fa-file';
      },

      async remove(f){
        if (!this.deleteUrl){ this.notify('Delete endpoint not configured.','error'); return; }
        if (!confirm(`Permanently delete "${f?.name || f?.full || 'this file'}"? This cannot be undone.`)) return;

        this.isDeleting  = true;
        this.deleteTotal = 1;
        this.deleteDone  = 0;

        await this._deleteSingle(f, true);
        this.deleteDone = 1;

        setTimeout(() => {
          this.isDeleting  = false;
          this.deleteTotal = 0;
          this.deleteDone  = 0;
        }, 400);
      },

      async _deleteSingle(f, showToast){
        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const form  = new FormData();
        const p     = this.bestIdentity(f);
        Object.entries(p).forEach(([k,v])=> form.append(k, v));
        form.append('_method','DELETE');
        if (token) form.append('_token', token);

        f.__deleting = true;
        try{
          const r = await fetch(this.deleteUrl, {
            method:'POST',
            body: form,
            credentials:'same-origin',
            headers:{ 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest', ...(token ? {'X-CSRF-TOKEN': token} : {}) }
          });

          if (r.status === 204 || r.ok) {
            const name = f?.name || f?.full || 'File';
            this.files = this.files.filter(x => x !== f);
            this.selectedKeys = this.selectedKeys.filter(k => k !== String(f.__key));
            if (showToast) this.notify(`Deleted: ${name}`, 'danger');
            return { ok:true };
          }

          let msg = `Delete failed (${r.status}).`;
          try {
            const ct = r.headers.get('content-type') || '';
            if (ct.includes('application/json')) {
              const d = await r.json();
              if (d?.message) msg = d.message;
            } else {
              const t = await r.text();
              if (t) msg = t.slice(0, 500);
            }
          } catch {}
          if (showToast) this.notify(msg, 'error');
          f.__deleting = false;
          return { ok:false, msg };

        } catch(e){
          if (showToast) this.notify('Network error while deleting.','error');
          f.__deleting = false;
          return { ok:false, msg:'Network error' };
        } finally {
          this.$nextTick(()=>this.updateMaster());
        }
      },

      async bulkDelete(){
        if (!this.deleteUrl){ this.notify('Delete endpoint not configured.','error'); return; }
        const items = this.getSelectedItems();
        if (!items.length){ this.notify('No items selected.','warning'); return; }

        if (!confirm(`Permanently delete ${items.length} selected file(s)? This cannot be undone.`)) return;

        this.isDeleting  = true;
        this.deleteTotal = items.length;
        this.deleteDone  = 0;

        let ok=0, fail=0;
        for (const f of items) {
          const res = await this._deleteSingle(f, false);
          if (res.ok) ok++; else fail++;
          this.deleteDone++;
        }

        if (ok && !fail) this.notify(`Deleted ${ok} item(s).`, 'danger');
        else if (ok && fail) this.notify(`Deleted ${ok}, ${fail} failed.`, 'warning');
        else this.notify(`Delete failed for all selected.`, 'error');

        this.$nextTick(()=>this.updateMaster());

        setTimeout(() => {
          this.isDeleting  = false;
          this.deleteTotal = 0;
          this.deleteDone  = 0;
        }, 400);
      },

      async copy(url){
        (async()=>{
          try{
            if(navigator.clipboard && window.isSecureContext){ await navigator.clipboard.writeText(url); }
            else{
              const ta=document.createElement('textarea'); ta.value=url;
              ta.style.position='fixed'; ta.style.left='-9999px';
              document.body.appendChild(ta); ta.select(); document.execCommand('copy'); ta.remove();
            }
            this.notify('URL copied to clipboard.','success');
          }catch{
            this.notify('Copy failed.','error');
          }
        })();
      },

      // ========= Automatic client-side WebP convert + resize =========
      _isVector(file){
        return (file?.type || '').toLowerCase() === 'image/svg+xml' || /\.svg$/i.test(file?.name||'');
      },
      _likelyAnimatedGif(file){
        return this.skipAnimatedGif && ((file?.type||'') === 'image/gif' || /\.gif$/i.test(file?.name||''));
      },
      async _fileToImage(file){
        try{
          if (window.createImageBitmap) {
            const bmp = await createImageBitmap(file);
            return { bitmap:bmp, w:bmp.width, h:bmp.height, release:()=>bmp.close && bmp.close() };
          }
        }catch(_){}
        const url = URL.createObjectURL(file);
        try{
          const img = new Image();
          img.decoding = 'async';
          img.loading  = 'eager';
          await new Promise((res, rej)=>{ img.onload = res; img.onerror = rej; img.src = url; });
          return { img, w:img.naturalWidth, h:img.naturalHeight, release:()=>URL.revokeObjectURL(url) };
        }catch(e){
          URL.revokeObjectURL(url);
          throw e;
        }
      },
      _targetSize(w,h){
        const maxD = Math.max(64, parseInt(this.resizeMaxDim||2560,10));
        const long = Math.max(w,h);
        if (long <= maxD) return { tw:w, th:h, scale:1 };
        const scale = maxD / long;
        return { tw: Math.round(w*scale), th: Math.round(h*scale), scale };
      },
      _quality01(){
        let q = Number(this.webpQuality);
        if (!Number.isFinite(q)) q = 82;
        q = Math.min(100, Math.max(1, Math.round(q)));
        return q / 100;
      },
      async _toWebpBlob(drawable, w, h){
        const { tw, th } = this._targetSize(w,h);
        const c = document.createElement('canvas');
        c.width = Math.max(1, tw);
        c.height = Math.max(1, th);
        const ctx = c.getContext('2d', { alpha: true, desynchronized: true });
        if (!ctx) throw new Error('Canvas 2D not available');

        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        if (drawable.bitmap) ctx.drawImage(drawable.bitmap, 0, 0, w, h, 0, 0, tw, th);
        else ctx.drawImage(drawable.img, 0, 0, w, h, 0, 0, tw, th);

        const blob = await new Promise(res => c.toBlob(res, 'image/webp', this._quality01()));
        if (!blob) throw new Error('WebP encoding failed');
        return blob;
      },
      async _convertIfNeeded(file){
        // Always attempt WebP conversion for raster images (auto, no UI)
        if (!file || !(file instanceof Blob)) return { out:file, changed:false, note:null };
        const type = (file.type || '').toLowerCase();
        const isImage = type.startsWith('image/');
        if (!isImage) return { out:file, changed:false, note:null };
        if (this._isVector(file)) return { out:file, changed:false, note:'svg-skip' };
        if (this._likelyAnimatedGif(file)) return { out:file, changed:false, note:'gif-skip' };

        const supported = await this._detectWebp();
        if (!supported) return { out:file, changed:false, note:'no-webp-support' };

        try{
          const src = await this._fileToImage(file);
          try{
            const blob = await this._toWebpBlob(src, src.w, src.h);
            const base = this.sanitizeBaseName(file.name || 'image');
            const webp = new File([blob], `${base}.webp`, { type:'image/webp', lastModified: Date.now() });
            return { out:webp, changed:true, note:'converted' };
          } finally {
            src.release && src.release();
          }
        }catch(e){
          return { out:file, changed:false, note:'convert-failed' };
        }
      },

      async upload(fileList){
        if (!this.uploadUrl) return;
        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

        const originals = Array.from(fileList || []);
        if (!originals.length) return;

        const totalFiles = originals.length;

        // init overlay
        this.isUploading = true;
        this.uploadPhase = 'Preparing files…';
        this.uploadTotal = totalFiles;
        this.uploadDone  = 0;
        this.uploadInternalProgress = 0;

        const prepared = [];
        let ok = 0, fail = 0;
        const okNames = [];

        const updateGlobal = (fileIndex, perFileProgress) => {
          // perFileProgress is 0..100 within that file
          const base   = (fileIndex / totalFiles) * 100;
          const seg    = perFileProgress / totalFiles;
          const value  = base + seg;
          this.uploadInternalProgress = Math.min(100, Math.max(0, value));
        };

        // 1) PREP / COMPRESS (0 → 40 per file)
        for (let i = 0; i < totalFiles; i++) {
          const original = originals[i];
          this.uploadPhase = totalFiles > 1
            ? ('Preparing file ' + (i+1) + ' of ' + totalFiles + '…')
            : 'Preparing file…';

          try {
            const res = await this._convertIfNeeded(original);
            const out = res?.out || original;
            prepared.push({ original, out, changed: !!res?.changed });
          } catch (e) {
            prepared.push({ original, out:null, changed:false, error:true });
            fail++;
          } finally {
            // at least move to 40% of that file's segment
            updateGlobal(i, 40);
          }
        }

        // 2) REAL UPLOAD (40 → 100 per file, using XHR progress)
        for (let i = 0; i < prepared.length; i++) {
          const entry = prepared[i];
          const { original, out, changed } = entry;
          if (!out) continue;

          this.uploadPhase = totalFiles > 1
            ? ('Uploading file ' + (i+1) + ' of ' + totalFiles + '…')
            : 'Uploading file…';

          try {
            const form = new FormData();
            form.append('file', out);
            if (token) form.append('_token', token);

            const d = await new Promise((resolve, reject) => {
              const xhr = new XMLHttpRequest();
              xhr.open('POST', this.uploadUrl, true);
              xhr.responseType = 'json';
              xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
              if (token) xhr.setRequestHeader('X-CSRF-TOKEN', token);

              xhr.upload.onprogress = (e) => {
                if (!e) return;
                let ratio = 0;
                if (e.lengthComputable && e.total) {
                  ratio = e.loaded / e.total;
                } else if (out.size) {
                  ratio = Math.min(1, (e.loaded || 0) / out.size);
                }
                const perFile = 40 + 60 * Math.max(0, Math.min(1, ratio));
                updateGlobal(i, perFile);
              };

              xhr.onload = () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                  resolve(xhr.response || null);
                } else {
                  reject(new Error('HTTP ' + xhr.status));
                }
              };

              xhr.onerror = () => reject(new Error('Network error'));
              xhr.send(form);
            });

            // ensure this file hits 100% of its segment
            updateGlobal(i, 100);
            this.uploadDone++;

            const uploadedAt = new Date().toISOString();

            const item = this.normalize([{
              url:   d?.location || d?.url || d?.path || '',
              thumb: d?.thumb || d?.thumbnail || d?.preview || '',
              name:  d?.name || out.name || original.name,
              mime:  d?.mime || out.type || original.type || '',
              alt:   d?.alt  || '',
              uploaded_at: uploadedAt,
              path:  d?.path || ''
            }])[0];

            if (item?.full) {
              item.full  = this.bust(item.full);
              item.thumb = this.bust(item.thumb);

              this.files.unshift(item);
              ok++; okNames.push(item.name || out.name || original.name);
              if (changed) this.notify(`Converted → WebP & uploaded: ${item.name}`, 'success');
              this.resetLimiter();
            } else {
              fail++;
            }
          } catch (e) {
            fail++;
          }
        }

        // finish
        this.uploadPhase = 'Finalizing…';
        this.uploadInternalProgress = 100;

        if (ok && !fail) {
          this.notify(ok === 1 ? `Uploaded: ${okNames[0]}` : `Uploaded ${ok} files.`, 'success');
        } else if (ok && fail) {
          this.notify(`Uploaded ${ok}, ${fail} failed.`, 'warning');
        } else if (!ok && fail) {
          this.notify(`All uploads failed.`, 'error');
        }

        this.$nextTick(()=>this.updateMaster());

        setTimeout(() => {
          this.isUploading = false;
          this.uploadPhase = '';
          this.uploadTotal = 0;
          this.uploadDone  = 0;
          this.uploadInternalProgress = 0;
        }, 400);
      },

      // Trigger hidden file input when clicking "Upload"
      browse(){
        const input = this.$refs.uploadInput || document.getElementById('library-upload');
        if (input) {
          input.click();
        } else {
          this.notify('Upload input not found.','error');
        }
      },

      fileKind(f){
        const e = this.ext(f);
        const m = (f?.mime || '').toLowerCase();
        if (m.includes('pdf') || e === 'pdf') return 'pdf';
        if (m.includes('word') || ['doc','docx','odt','rtf'].includes(e)) return 'word';
        if (m.includes('sheet') || m.includes('excel') || ['xls','xlsx','ods','csv'].includes(e)) return 'excel';
        if (m.includes('presentation') || m.includes('powerpoint') || ['ppt','pptx','odp'].includes(e)) return 'powerpoint';
        if (['zip','rar','7z','tar','gz'].includes(e)) return 'zip';
        if (m.startsWith('video/') || ['mp4','webm','mov','avi','mkv'].includes(e)) return 'video';
        if (m.startsWith('audio/') || ['mp3','wav','ogg','m4a','flac'].includes(e)) return 'audio';
        if (['js','ts','json','html','css','php','py','java','rb','go','c','cpp','cs','sh','yml','yaml','xml','md','markdown','txt','log'].includes(e)) return 'code';
        return 'file';
      },
      fileIconSvgByFile(f, big=false){ return this.fileIconSvg(this.fileKind(f), big); },
      fileIconSvg(kind='file', big=false){
        const meta = {
          pdf:        { label:'PDF',  color:'#ef4444' },
          word:       { label:'DOC',  color:'#2563eb' },
          excel:      { label:'XLS',  color:'#16a34a' },
          powerpoint: { label:'PPT',  color:'#f97316' },
          zip:        { label:'ZIP',  color:'#64748b' },
          video:      { label:'VID',  color:'#a855f7' },
          audio:      { label:'AUD',  color:'#0ea5e9' },
          code:       { label:'CODE', color:'#334155' },
          file:       { label:'FILE', color:'#94a3b8' },
        }[kind] || { label:'FILE', color:'#94a3b8' };

        const cls = big ? 'file-svg-lg' : 'file-svg';

        return `
    <svg class="${cls}" viewBox="0 0 24 24" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
      <path d="M6 2h7l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z" fill="#e5e7eb"/>
      <path d="M13 2v5h5" fill="#cbd5e1"/>
      <rect x="4" y="14.5" width="16" height="5.5" rx="1.2" fill="${meta.color}"/>
      <text x="12" y="18.7" text-anchor="middle" font-size="5.5" font-family="ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial" font-weight="700" fill="#ffffff">${meta.label}</text>
    </svg>`;
      },

      // DnD
      onDragOver(e){ e.preventDefault(); },
      onDrop(e){
        e.preventDefault();
        if (this._internalDrag) { this._internalDrag = false; return; }
        const files = e.dataTransfer?.files;
        if (files?.length) this.upload(files);
      },
      startInternalDrag(){ this._internalDrag = true; },
      endInternalDrag(){ this._internalDrag = false; },
    }
  }
  </script>

  <script>
    // Build URLs inline (no dependency on later @php blocks)
    window.__LIB_OPTS = @js([
      'fetchUrl'  => \Illuminate\Support\Facades\Route::has('admin.media.list')
                      ? route('admin.media.list') : url('/admin/media/list'),
      'uploadUrl' => \Illuminate\Support\Facades\Route::has('admin.media.upload')
                      ? route('admin.media.upload') : null,
      'deleteUrl' => \Illuminate\Support\Facades\Route::has('admin.media.delete')
                      ? route('admin.media.delete') : null,
      'updateUrl' => \Illuminate\Support\Facades\Route::has('admin.media.update')
                      ? route('admin.media.update') : null,
    ]);

    // Register Alpine components regardless of load order
    function __registerAlpineComponents(){
      if (!window.Alpine) return;
      Alpine.data('toastHub',    () => (window.toastHub ? window.toastHub() : {}));
      Alpine.data('libraryPage', () => (window.libraryPage ? window.libraryPage(window.__LIB_OPTS) : {}));
    }
    if (window.Alpine) { __registerAlpineComponents(); }
    document.addEventListener('alpine:init', __registerAlpineComponents);
  </script>
@endpush


@section('content')
<div x-data="libraryPage" x-cloak class="space-y-6">
  <div class="flex items-center justify-between flex-wrap gap-3">
    <div class="flex items-center gap-3">
      <h1 class="text-xl font-bold">Library</h1>
      <span class="text-xs text-slate-500">Upload, reuse & manage media</span>
    </div>

    <div class="flex items-center gap-2 flex-wrap">
      {{-- Master select-all (visible items) --}}
      <label class="inline-flex items-center gap-2 mr-2">
        <input type="checkbox" x-ref="master"
               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
               @change="toggleAll($event.target.checked)"
               :disabled="visible().length === 0"
               aria-label="Select all visible items">
        <span class="text-sm">Select all (visible)</span>
        <span class="text-xs rounded-full px-2 py-0.5 border bg-white"
              x-text="getSelectedItems().length"></span>
      </label>

      {{-- Bulk actions --}}
      <button type="button" class="rounded-lg border px-3 py-2 hover:bg-gray-50 disabled:opacity-50"
              :disabled="getSelectedItems().length===0"
              @click="bulkDelete()">
        <i class="fa-regular fa-trash-can mr-1"></i> Delete selected
      </button>

      <button type="button" class="rounded-lg border px-3 py-2 hover:bg-gray-50 disabled:opacity-50"
              :disabled="getSelectedItems().length===0"
              @click="clearSelection()">
        <i class="fa-regular fa-square-minus mr-1"></i> Clear
      </button>

      <span class="h-6 w-px bg-slate-200 mx-1"></span>

      <input id="library-search" type="search" x-model="q"
             placeholder="Filter by name, alt or URL…" class="rounded-lg border px-3 py-2 w-56 md:w-72">

      {{-- Filter & Sort --}}
      <select x-model="filterMode" class="rounded-lg border px-3 py-2">
        <option value="all">All</option>
        <option value="images">Images only</option>
        <option value="files">Files only</option>
      </select>

      <select x-model="sortMode" class="rounded-lg border px-3 py-2">
        <option value="newest">Newest → Oldest</option>
        <option value="oldest">Oldest → Newest</option>
      </select>

      <input id="library-upload" type="file" class="hidden"
             x-ref="uploadInput"
             accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.csv,.ppt,.pptx,.zip"
             multiple
             x-on:change="upload($event.target.files); $event.target.value=''">

      <button type="button" class="rounded-lg border px-3 py-2 hover:bg-gray-50"
              x-on:click="browse()">
        <i class="fa-solid fa-cloud-arrow-up mr-1"></i> Upload
      </button>
    </div>
  </div>

  <div class="rounded-xl border bg-white p-4 min-h-[40vh]"
       x-on:dragover.prevent="onDragOver($event)"
       x-on:drop="onDrop($event)">

    <p class="text-xs text-slate-500 mb-3">
      Tip: drag &amp; drop or paste images here to upload. Images are automatically converted to WebP (quality 82) and resized (max side 2560&nbsp;px) before uploading. Animated GIFs and SVGs are preserved.
    </p>

    <template x-if="loading">
      <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        <template x-for="i in 8" :key="'skel_'+i">
          <div class="rounded-xl border bg-white overflow-hidden">
            <div class="relative bg-gray-100 animate-pulse">
              <div class="w-full" style="padding-bottom:56.25%"></div>
            </div>
            <div class="p-3 space-y-2">
              <div class="h-3 bg-gray-100 rounded w-3/4"></div>
              <div class="h-3 bg-gray-100 rounded w-1/2"></div>
            </div>
          </div>
        </template>
      </div>
    </template>

    <template x-if="errorMsg">
      <div class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"
           x-text="errorMsg"></div>
    </template>

    <div class="media-grid grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
         :class="{ 'pointer-events-none': showModal }"
         x-show="!loading">
      <template x-for="f in visible()" :key="f.__key">
        <div class="rounded-xl border overflow-hidden bg-white flex flex-col"
             draggable="false"
             x-on:dragstart.stop="startInternalDrag()"
             x-on:dragend="endInternalDrag()">
          <div class="relative overflow-hidden bg-gray-100">
            <div class="w-full" style="padding-bottom:56.25%"></div>

            {{-- Select checkbox (top-left) --}}
            <label class="select-cb inline-flex items-center gap-1 bg-white/90 px-1.5 py-1 rounded shadow">
              <input type="checkbox"
                     class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                     :value="f.__key"
                     :checked="isSelectedKey(f.__key)"
                     @change.stop="onRowToggle($event, f.__key)"
                     :aria-label="`Select ${f?.name || 'file'}`">
            </label>

            {{-- Delete "X" (top-right) --}}
            <button
              type="button"
              class="delete-btn rounded-full bg-red-600 text-white p-1 shadow hover:bg-red-700 disabled:opacity-60 pointer-events-auto"
              title="Delete"
              :disabled="f.__deleting"
              x-on:click.stop="remove(f)"
            >
              <i class="fa-solid fa-spinner fa-spin text-[10px]" x-show="f.__deleting"></i>
              <svg x-show="!f.__deleting" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                   class="h-3 w-3" aria-hidden="true" focusable="false">
                <path fill="currentColor"
                      d="M18.3 5.7a1 1 0 0 0-1.4-1.4L12 9.17 7.1 4.3A1 1 0 0 0 5.7 5.7L10.59 10.6 5.7 15.5a1 1 0 1 0 1.4 1.4L12 12l4.9 4.9a1 1 0 0 0 1.4-1.4L13.41 10.6z"/>
              </svg>
            </button>

            <template x-if="isImage(f) && !f.broken">
              <img
                :src="f.thumb || f.full"
                :alt="f.alt || f.name || 'Image'"
                loading="lazy"
                decoding="async"
                draggable="false"
                class="absolute inset-0 w-full h-full object-cover object-center"
                x-on:error="f.broken = true"
              >
            </template>

            <template x-if="!isImage(f) || f.broken">
              <div class="absolute inset-0 grid place-items-center text-slate-500">
                <div x-html="fileIconSvgByFile(f)"></div>
              </div>
            </template>

          </div>

          <div class="p-3 space-y-1">
            <div class="text-sm font-medium truncate" x-text="f.name || '—'"></div>
            <div class="text-xs text-slate-500 truncate" x-text="f.full"></div>
            <template x-if="isImage(f) && f.alt">
              <div class="text-xs text-slate-500 truncate" x-text="'Alt: ' + f.alt"></div>
            </template>

            <div class="mt-2 flex items-center gap-2 flex-wrap">
              {{-- Open (edit modal) --}}
              <button type="button"
                      class="inline-flex items-center gap-1 rounded border px-2 py-1 text-sm hover:bg-gray-50"
                      x-on:click="openEditor(f)">
                <i class="fa-regular fa-pen-to-square"></i> Open
              </button>

              {{-- View in new tab --}}
              <a :href="f.full" target="_blank"
                 class="inline-flex items-center gap-1 rounded border px-2 py-1 text-sm hover:bg-gray-50">
                <i class="fa-regular fa-eye"></i> View
              </a>

              <button type="button"
                      class="inline-flex items-center gap-1 rounded border px-2 py-1 text-sm text-[#105702] border-[#105702]/30 hover:bg-[#105702]/10"
                      x-on:click="copy(f.full)">
                <i class="fa-regular fa-copy"></i> Copy URL
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>

    {{-- View more / no more --}}
    <div class="mt-4 flex justify-center" x-show="!loading">
      <template x-if="canShowMore()">
        <button type="button"
                class="rounded-lg border px-4 py-2 text-sm hover:bg-gray-50"
                x-on:click="showMore()">
          Click to view more
        </button>
      </template>
      <template x-if="!canShowMore() && filtered().length > 0">
        <div class="text-sm text-slate-500">There is no image to load</div>
      </template>
    </div>

    <div x-show="!loading && !filtered().length && !errorMsg"
         class="py-12 text-center text-slate-600">No files found.</div>
  </div>

  {{-- FULL-SCREEN overlay (upload OR delete) teleported to BODY --}}
  <template x-teleport="body">
    <div
      x-show="isUploading || isDeleting"
      x-cloak
      x-transition.opacity
      class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/60 backdrop-blur-sm"
    >
      <div class="w-[88vw] max-w-sm rounded-2xl bg-white shadow-2xl px-6 py-5 flex flex-col items-center gap-3 text-center">
        <div class="spin-border h-10 w-10 border-[#105702] border-opacity-80"></div>

        {{-- Main label --}}
        <p class="text-sm font-medium text-[#25282a]"
           x-text="isUploading
             ? (uploadTotal > 1
                 ? ('Uploading ' + uploadDone + ' of ' + uploadTotal + ' file' + (uploadTotal>1?'s':'') + '…')
                 : (uploadPhase || 'Uploading file…'))
             : (deleteTotal > 1
                 ? ('Deleting ' + deleteDone + ' of ' + deleteTotal + ' file' + (deleteTotal>1?'s':'') + '…')
                 : 'Deleting file…')"></p>

        {{-- Secondary label (percent) --}}
        <p x-show="isUploading"
           class="text-xs font-semibold text-[#105702]"
           x-text="uploadTotal ? (Math.round(uploadInternalProgress) + '% complete') : 'Preparing…'"></p>

        <p x-show="isDeleting"
           class="text-xs font-semibold text-[#105702]"
           x-text="deleteTotal ? (Math.round((deleteDone / deleteTotal) * 100) + '% complete') : 'Working…'"></p>

        {{-- Progress bar --}}
        <div class="w-full mt-1">
          <div class="w-full h-1.5 rounded-full bg-slate-200/80 overflow-hidden">
            <div class="h-full bg-[#105702] transition-[width] duration-200"
                 :style="isUploading
                   ? ('width: ' + Math.round(uploadInternalProgress) + '%')
                   : (deleteTotal ? ('width: ' + Math.round((deleteDone / deleteTotal) * 100) + '%') : 'width: 0%')"></div>
          </div>
        </div>

        <p class="text-xs text-slate-500 mt-1" x-show="isUploading">
          Please keep this tab open. Images are converted to WebP and resized before saving.
        </p>
        <p class="text-xs text-slate-500 mt-1" x-show="isDeleting">
          Please keep this tab open while media is being removed.
        </p>
      </div>
    </div>
  </template>

  {{-- Back to top --}}
  <button x-show="showBackTop"
          x-on:click="scrollTop()"
          class="fixed bottom-5 right-5 rounded-full shadow-lg border bg-white px-4 py-3 text-sm hover:bg-gray-50"
          aria-label="Back to top">
    <i class="fa-solid fa-arrow-up-long mr-1"></i> Top
  </button>

  {{-- Edit Modal --}}
  <div x-show="showModal"
       x-transition.opacity
       x-on:keydown.escape.prevent.stop="showModal=false"
       class="fixed inset-0 z-[200] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40" x-on:click.self="showModal=false"></div>

    <div class="relative z-10 w-[92vw] max-w-3xl rounded-2xl bg-white shadow-2xl overflow-hidden">
      <div class="flex items-center justify-between px-5 py-3 border-b">
        <h2 class="font-semibold">Edit Media</h2>
        <button class="p-2 rounded hover:bg-gray-50" x-on:click="showModal=false" aria-label="Close">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="grid md:grid-cols-2 gap-6 p-5">
        {{-- Preview --}}
        <div class="space-y-3">
          <div class="rounded-xl border overflow-hidden bg-gray-100">
            <div class="relative" style="padding-bottom:66%;">
              <template x-if="active && isImage(active) && !active.broken">
                <img :src="active.thumb || active.full"
                     :alt="form.alt || form.name || 'Image'"
                     class="absolute inset-0 w-full h-full object-cover object-center"
                     loading="lazy" decoding="async"
                     x-on:error="active.broken = true">
              </template>
              <template x-if="!active || !isImage(active) || active.broken">
                <div class="absolute inset-0 grid place-items-center text-slate-500">
                  <div x-html="active ? fileIconSvgByFile(active, true) : fileIconSvg('file', true)"></div>
                </div>
              </template>
            </div>
          </div>

          <template x-if="active">
            <a :href="active.full" target="_blank"
               class="inline-flex items-center gap-2 rounded border px-3 py-2 text-sm hover:bg-gray-50">
              <i class="fa-regular fa-eye"></i> Open in new tab
            </a>
          </template>
        </div>

        {{-- Form --}}
        <div class="space-y-4">
          <div>
            <label for="media-name-input" class="block text-sm font-medium mb-1">File name</label>
            <input id="media-name-input" type="text" x-model="form.name"
                   class="w-full rounded-lg border px-3 py-2" placeholder="filename.ext">
            <p class="text-xs text-slate-500 mt-1">This will rename the actual file on disk (we keep the original extension).</p>
          </div>

          <template x-if="active && isImage(active)">
            <div>
              <label for="media-alt-input" class="block text-sm font-medium mb-1">Alt text</label>
              <textarea id="media-alt-input" rows="3" x-model="form.alt"
                        class="w-full rounded-lg border px-3 py-2" placeholder="Describe the image for accessibility"></textarea>
              <p class="text-xs text-slate-500 mt-1">Used by screen readers and as a fallback if the image can’t be loaded.</p>
            </div>
          </template>

          <div>
            <label class="block text-sm font-medium mb-1">Direct URL</label>
            <div class="flex">
              <input type="text" :value="active && active.full ? active.full : ''" readonly class="flex-1 rounded-l-lg border px-3 py-2">
              <button type="button" class="rounded-r-lg border border-l-0 px-3 py-2 hover:bg-gray-50"
                      x-on:click="copy(active.full)">
                <i class="fa-regular fa-copy"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-end gap-2 px-5 py-4 border-t">
        <button class="rounded-lg border px-4 py-2 hover:bg-gray-50" x-on:click="showModal=false">Cancel</button>
        <button class="rounded-lg bg-[#105702] text-white px-4 py-2 hover:opacity-90 disabled:opacity-60"
                :disabled="isSaving"
                x-on:click="saveMeta()">
          <i class="fa-solid" :class="isSaving ? 'fa-spinner fa-spin' : 'fa-floppy-disk'"></i>
          <span x-text="isSaving ? 'Saving…' : 'Save changes'"></span>
        </button>
      </div>
    </div>
  </div>

  {{-- Page-local toast container (TOP-RIGHT, bigger) --}}
  <div x-data="toastHub"
       class="fixed top-4 right-4 z-50 space-y-3 w-[92vw] sm:w-96"
       aria-live="polite" aria-atomic="true">
    <template x-for="t in toasts" :key="t.id">
      <div
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-2 opacity-0"
        class="rounded-xl shadow-xl px-4 py-3 flex items-start gap-3"
        :class="tone(t.type)"
        role="status"
      >
        <i class="fa-solid text-xl leading-none mt-0.5" :class="icon(t.type)"></i>
        <div class="text-base leading-snug" x-text="t.msg"></div>
        <button class="ml-auto opacity-90 hover:opacity-100"
                title="Dismiss" x-on:click="dismiss(t.id)">
          <i class="fa-solid fa-xmark text-lg"></i>
        </button>
      </div>
    </template>
  </div>
</div>
@endsection
