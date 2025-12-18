{{-- resources/views/components/popup-runner.blade.php --}}
@php
  use App\Models\Popup;
  use Illuminate\Support\Str;

  // Current request path, normalised (no leading/trailing slash)
  $currentPath = trim(request()->path(), '/');
  $currentPath = $currentPath === '' ? '/' : $currentPath;

  // Get all active pop-ups
  $allActive = Popup::where('is_active', true)
      ->where('is_draft', false)
      ->orderBy('created_at', 'asc')
      ->get();

  // Helper: does this popup apply to the current path?
  $popupsForPage = $allActive->filter(function (Popup $popup) use ($currentPath) {
      $scope      = $popup->target_scope ?? 'all';
      $rawPaths   = (string) $popup->target_paths;
      $lines      = collect(preg_split('/\r\n|\r|\n/', $rawPaths))
                      ->map(fn ($line) => trim($line, " \t\n\r\0\x0B/"))
                      ->filter(); // drop empty

      // All pages
      if ($scope === 'all') {
          return true;
      }

      // Only listed paths
      if ($scope === 'include') {
          if ($lines->isEmpty()) {
              return false;
          }
          foreach ($lines as $pattern) {
              if (Str::is($pattern, $currentPath)) {
                  return true;
              }
          }
          return false;
      }

      // All pages except listed paths
      if ($scope === 'exclude') {
          if ($lines->isEmpty()) {
              return true;
          }
          foreach ($lines as $pattern) {
              if (Str::is($pattern, $currentPath)) {
                  return false;
              }
          }
          return true;
      }

      // Fallback: treat unknown scope as "all"
      return true;
  });
@endphp

@if($popupsForPage->isNotEmpty())
  <div id="site-popups-layer" aria-live="polite">
    @foreach($popupsForPage as $popup)
      @php
        // Effective click class (either custom, or auto js-popup-{id})
        $clickClass = $popup->trigger_on_click
          ? ($popup->click_class ?? 'js-popup-'.$popup->id)
          : null;

        // All images (primary + gallery)
        $images = $popup->all_images ?? [];
        $imageCount = count($images);
      @endphp

      {{-- Single pop-up instance --}}
      <div
        x-data="popupRunner({
            id: {{ $popup->id }},
            title: @js($popup->title),
            description: @js($popup->description ?? ''),
            triggerOnClick: {{ $popup->trigger_on_click ? 'true' : 'false' }},
            clickClass: @js($clickClass),
            triggerOnLoad: {{ $popup->trigger_on_load ? 'true' : 'false' }},
            loadDelay: {{ (int)($popup->trigger_load_delay_seconds ?? 0) }},
            triggerOnScroll: {{ $popup->trigger_on_scroll ? 'true' : 'false' }},
            scrollDirection: @js($popup->trigger_scroll_direction ?? 'down'),
            scrollPercent: {{ (int)($popup->trigger_scroll_percent ?? 25) }},
        })"
        x-init="init()"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-[2147483648] flex items-center justify-center bg-black/70 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
      >
        <div class="relative w-full max-w-lg mx-4 bg-white rounded-2xl shadow-xl overflow-hidden">
          {{-- Close button --}}
          <button
            type="button"
            @click="close()"
            class="absolute top-3 right-3 z-10 inline-flex items-center justify-center w-9 h-9 rounded-full bg-black/70 text-white hover:bg-black transition"
            aria-label="Close pop-up"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>

          {{-- IMAGES: static for 1, carousel for 2+ --}}
          @if($imageCount === 1)
            <div class="w-full bg-slate-100 overflow-hidden flex items-center justify-center">
              <img
                src="{{ $images[0] }}"
                alt="{{ $popup->title }}"
                class="block w-full h-auto object-contain max-h-[70vh]"
                loading="lazy"
                decoding="async"
              >
            </div>
          @elseif($imageCount > 1)
            <div
              class="relative w-full bg-slate-100 overflow-hidden"
              data-popup-carousel="{{ $popup->id }}"
            >
              <div class="w-full">
                @foreach($images as $idx => $url)
                  <div
                    class="popup-slide @if($idx !== 0) hidden @endif"
                    data-popup-slide="{{ $idx }}"
                  >
                    <div class="flex items-center justify-center">
                      <img
                        src="{{ $url }}"
                        alt="{{ $popup->title }} - Image {{ $idx + 1 }}"
                        class="block w-full h-auto object-contain max-h-[70vh]"
                        loading="lazy"
                        decoding="async"
                      >
                    </div>
                  </div>
                @endforeach
              </div>

              {{-- Prev / Next arrows --}}
              <button
                type="button"
                class="absolute left-3 top-1/2 -translate-y-1/2 inline-flex items-center justify-center w-9 h-9 rounded-full bg-black/60 text-white hover:bg-black/80"
                data-popup-prev
              >
                <span class="sr-only">Previous image</span>
                <i class="fa-solid fa-chevron-left text-xs"></i>
              </button>

              <button
                type="button"
                class="absolute right-3 top-1/2 -translate-y-1/2 inline-flex items-center justify-center w-9 h-9 rounded-full bg-black/60 text-white hover:bg-black/80"
                data-popup-next
              >
                <span class="sr-only">Next image</span>
                <i class="fa-solid fa-chevron-right text-xs"></i>
              </button>

              {{-- Dots --}}
              <div class="absolute bottom-3 inset-x-0 flex items-center justify-center gap-1.5">
                @foreach($images as $idx => $url)
                  <button
                    type="button"
                    class="w-2.5 h-2.5 rounded-full border border-white/60 bg-white/40"
                    data-popup-dot="{{ $idx }}"
                  >
                    <span class="sr-only">Go to image {{ $idx + 1 }}</span>
                  </button>
                @endforeach
              </div>
            </div>
          @endif

          {{-- Content --}}
          <div class="p-3 space-y-3">
            <h2 class="text-lg font-bold text-slate-900">
              {{ $popup->title }}
            </h2>

            {{-- (description optional) --}}
            {{-- 
            @if($popup->description)
              <p class="text-sm text-slate-700">
                {{ $popup->description }}
              </p>
            @endif
            --}}

            {{-- CTAs --}}
            <div class="flex flex-wrap gap-2 pt-1">
              @foreach([1,2,3] as $i)
                @php
                  $lbl = $popup->{"cta{$i}_label"};
                  $url = $popup->{"cta{$i}_url"};
                @endphp
                @if($lbl && $url)
                  <a href="{{ $url }}"
                     class="inline-flex items-center gap-1.5 rounded-full bg-gray-600 text-white px-4 py-2 text-xs font-semibold hover:bg-gray-700"
                     @click="close()">
                    {{ $lbl }}
                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                  </a>
                @endif
              @endforeach
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Front-end engine: triggers + per-tab close --}}
  <script>
    function popupRunner(config){
      return {
        open: false,
        hasFired: false,
        // per-tab key; we'll use sessionStorage instead of localStorage
        storageKey: 'ab-popup-' + config.id + '-closed',

        init(){
          // Respect "closed" state for the current tab.
          // Persists across refresh, but resets when the tab/window is closed.
          try {
            const closed = sessionStorage.getItem(this.storageKey);
            if (closed === '1') {
              return;
            }
          } catch (e) {}

          // Click trigger
          if (config.triggerOnClick && config.clickClass) {
            this.bindClick(config.clickClass);
          }

          // Page-load trigger
          if (config.triggerOnLoad) {
            const delay = (config.loadDelay || 0) * 1000;
            setTimeout(() => this.show(), delay);
          }

          // Scroll trigger
          if (config.triggerOnScroll) {
            this.bindScroll(config.scrollDirection || 'down', config.scrollPercent || 25);
          }
        },

        bindClick(className){
          const self = this;

          function attach(){
            document.querySelectorAll('.' + className).forEach(function(el){
              el.addEventListener('click', function(e){
                e.preventDefault();
                self.show();
              }, { passive: false });
            });
          }

          // Attach now + re-attach for dynamically injected elements
          attach();
          const obs = new MutationObserver(attach);
          obs.observe(document.body, { childList: true, subtree: true });
        },

        bindScroll(direction, percent){
          const self = this;
          let lastY = window.scrollY || window.pageYOffset || 0;
          let maxReached = 0;

          function onScroll(){
            const y   = window.scrollY || window.pageYOffset || 0;
            const doc = document.documentElement;
            const max = doc.scrollHeight - window.innerHeight;
            const pct = max > 0 ? (y / max) * 100 : 0;
            maxReached = Math.max(maxReached, pct);

            if (self.hasFired) {
              window.removeEventListener('scroll', onScroll);
              return;
            }

            if (direction === 'down') {
              // Show once user has scrolled down to X%
              if (pct >= percent) {
                self.show();
                window.removeEventListener('scroll', onScroll);
              }
            } else {
              // "Up" pattern: user scrolls beyond threshold, then starts going up
              if (maxReached >= percent && y < lastY) {
                self.show();
                window.removeEventListener('scroll', onScroll);
              }
            }

            lastY = y;
          }

          window.addEventListener('scroll', onScroll, { passive: true });
        },

        show(){
          if (this.hasFired) return;
          this.open = true;
          this.hasFired = true;
        },

        close(){
          this.open = false;
          // Mark closed for this tab only
          try {
            sessionStorage.setItem(this.storageKey, '1');
          } catch (e) {}
        }
      }
    }

    // Lightweight carousel handler for any multi-image popup
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('[data-popup-carousel]').forEach(function (carousel) {
        const slides = carousel.querySelectorAll('[data-popup-slide]');
        const prevBtn = carousel.querySelector('[data-popup-prev]');
        const nextBtn = carousel.querySelector('[data-popup-next]');
        const dots    = carousel.querySelectorAll('[data-popup-dot]');

        if (!slides.length) return;

        let activeIdx = 0;

        function goTo(idx) {
          const total = slides.length;
          activeIdx = ((idx % total) + total) % total;

          slides.forEach(function (slide, i) {
            if (i === activeIdx) {
              slide.classList.remove('hidden');
            } else {
              slide.classList.add('hidden');
            }
          });

          dots.forEach(function (dot, i) {
            if (i === activeIdx) {
              dot.classList.add('bg-white', 'border-white');
              dot.classList.remove('bg-white/40', 'border-white/60');
            } else {
              dot.classList.remove('bg-white', 'border-white');
              dot.classList.add('bg-white/40', 'border-white/60');
            }
          });
        }

        prevBtn && prevBtn.addEventListener('click', function () {
          goTo(activeIdx - 1);
        });

        nextBtn && nextBtn.addEventListener('click', function () {
          goTo(activeIdx + 1);
        });

        dots.forEach(function (dot, i) {
          dot.addEventListener('click', function () {
            goTo(i);
          });
        });

        // init
        goTo(0);
      });
    });
  </script>
@endif
