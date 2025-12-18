{{-- resources/views/popup/show.blade.php --}}
@php
  /** @var \App\Models\Popup|null $popup */

  $images = $popup?->all_images ?? [];
  $imageCount = count($images);
@endphp

@if($popup)
  <div
    data-popup-root
    data-popup-id="{{ $popup->id }}"
    data-popup-on-load="{{ $popup->trigger_on_load ? 1 : 0 }}"
    data-popup-load-delay="{{ (int)($popup->trigger_load_delay_seconds ?? 0) }}"
    data-popup-on-scroll="{{ $popup->trigger_on_scroll ? 1 : 0 }}"
    data-popup-scroll-dir="{{ $popup->trigger_scroll_direction ?? 'down' }}"
    data-popup-scroll-percent="{{ (int)($popup->trigger_scroll_percent ?? 0) }}"
    data-popup-click-class="{{ $popup->click_class }}"
    class="fixed inset-0 z-[2147483647] flex items-center justify-center bg-black/60 px-4 py-6 hidden opacity-0 pointer-events-none transition-opacity duration-200"
  >
    <div
      data-popup-panel
      class="relative w-full max-w-3xl max-h-[90vh] rounded-2xl bg-white shadow-2xl overflow-hidden flex flex-col"
    >
      {{-- Close button --}}
      <button
        type="button"
        class="absolute right-3 top-3 z-10 text-slate-200 hover:text-white drop-shadow"
        data-popup-close
      >
        <span class="sr-only">Close</span>
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>

      {{-- IMAGES: static for 1, carousel for 2+ --}}
      @if($imageCount === 1)
        <div class="w-full bg-slate-100 overflow-hidden flex items-center justify-center">
          <img
            src="{{ $images[0] }}"
            alt="{{ $popup->title }}"
            class="block w-full h-auto max-h-[70vh] object-contain"
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
                    class="block w-full h-auto max-h-[70vh] object-contain"
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

      {{-- CONTENT: scrollable if needed --}}
      <div class="p-5 space-y-3 overflow-y-auto">
        <h2 class="text-lg font-semibold text-slate-900">
          {{ $popup->title }}
        </h2>

        @if($popup->description)
          <p class="text-sm text-slate-700">
            {{ $popup->description }}
          </p>
        @endif

        <div class="flex flex-wrap gap-2 pt-2">
          @if($popup->cta1_label && $popup->cta1_url)
            <a href="{{ $popup->cta1_url }}"
               class="inline-flex items-center justify-center rounded-lg bg-emerald-600 text-white px-3 py-2 text-sm font-semibold hover:bg-emerald-700"
               data-popup-close>
              {{ $popup->cta1_label }}
            </a>
          @endif

          @if($popup->cta2_label && $popup->cta2_url)
            <a href="{{ $popup->cta2_url }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50"
               data-popup-close>
              {{ $popup->cta2_label }}
            </a>
          @endif

          @if($popup->cta3_label && $popup->cta3_url)
            <a href="{{ $popup->cta3_url }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50"
               data-popup-close>
              {{ $popup->cta3_label }}
            </a>
          @endif
        </div>

        <button
          type="button"
          class="mt-3 text-xs text-slate-500 underline"
          data-popup-close
        >
          Close and don’t show again while this tab is open
        </button>
      </div>
    </div>
  </div>

  {{-- Lightweight runner for this single popup --}}
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const root = document.querySelector('[data-popup-root]');
      if (!root) return;

      const popupId      = root.getAttribute('data-popup-id');
      const onLoad       = root.getAttribute('data-popup-on-load') === '1';
      const loadDelaySec = parseInt(root.getAttribute('data-popup-load-delay') || '0', 10);
      const onScroll     = root.getAttribute('data-popup-on-scroll') === '1';
      const scrollDir    = root.getAttribute('data-popup-scroll-dir') || 'down';
      const scrollPct    = parseInt(root.getAttribute('data-popup-scroll-percent') || '25', 10);
      const clickClass   = root.getAttribute('data-popup-click-class') || '';

      const panel        = root.querySelector('[data-popup-panel]');
      const closeEls     = root.querySelectorAll('[data-popup-close]');
      const storageKey   = 'ab-popup-' + popupId + '-closed-tab';

      let hasFired = false;

      function showPopup() {
        if (hasFired) return;

        // Respect per-tab "closed" flag
        try {
          if (sessionStorage.getItem(storageKey) === '1') {
            return;
          }
        } catch (e) {}

        hasFired = true;
        root.classList.remove('hidden', 'pointer-events-none');
        requestAnimationFrame(() => {
          root.classList.remove('opacity-0');
        });
      }

      function hidePopup() {
        root.classList.add('opacity-0');
        setTimeout(() => {
          root.classList.add('hidden', 'pointer-events-none');
        }, 180);

        try {
          sessionStorage.setItem(storageKey, '1');
        } catch (e) {}
      }

      // Close handlers
      closeEls.forEach(el => {
        el.addEventListener('click', function () {
          hidePopup();
        });
      });

      // ESC key closes
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
          hidePopup();
        }
      });

      // Carousel (if present)
      const carousel = root.querySelector('[data-popup-carousel]');
      if (carousel) {
        const slides = carousel.querySelectorAll('[data-popup-slide]');
        const prevBtn = carousel.querySelector('[data-popup-prev]');
        const nextBtn = carousel.querySelector('[data-popup-next]');
        const dots    = carousel.querySelectorAll('[data-popup-dot]');
        let activeIdx = 0;

        function goTo(idx) {
          if (!slides.length) return;
          const total = slides.length;
          activeIdx = ((idx % total) + total) % total;

          slides.forEach((el, i) => {
            if (i === activeIdx) {
              el.classList.remove('hidden');
            } else {
              el.classList.add('hidden');
            }
          });

          dots.forEach((dot, i) => {
            if (i === activeIdx) {
              dot.classList.add('bg-white', 'border-white');
              dot.classList.remove('bg-white/40', 'border-white/60');
            } else {
              dot.classList.remove('bg-white', 'border-white');
              dot.classList.add('bg-white/40', 'border-white/60');
            }
          });
        }

        prevBtn && prevBtn.addEventListener('click', () => goTo(activeIdx - 1));
        nextBtn && nextBtn.addEventListener('click', () => goTo(activeIdx + 1));
        dots.forEach((dot, i) => {
          dot.addEventListener('click', () => goTo(i));
        });

        goTo(0);
      }

      // Trigger: on page load
      if (onLoad) {
        const delayMs = Math.max(0, loadDelaySec) * 1000;
        setTimeout(showPopup, delayMs);
      }

      // Trigger: on scroll
      if (onScroll) {
        let lastY = window.scrollY || window.pageYOffset || 0;
        let maxReached = 0;

        function onScrollHandler() {
          const y   = window.scrollY || window.pageYOffset || 0;
          const doc = document.documentElement;
          const max = doc.scrollHeight - window.innerHeight;
          const pct = max > 0 ? (y / max) * 100 : 0;
          maxReached = Math.max(maxReached, pct);

          if (hasFired) {
            window.removeEventListener('scroll', onScrollHandler);
            return;
          }

          if (scrollDir === 'down') {
            if (pct >= scrollPct) {
              showPopup();
              window.removeEventListener('scroll', onScrollHandler);
            }
          } else {
            // up: only after they’ve scrolled past threshold and started going up
            if (maxReached >= scrollPct && y < lastY) {
              showPopup();
              window.removeEventListener('scroll', onScrollHandler);
            }
          }

          lastY = y;
        }

        window.addEventListener('scroll', onScrollHandler, { passive: true });
      }

      // Trigger: on click of special class
      if (clickClass) {
        function attachClickTriggers() {
          document.querySelectorAll('.' + clickClass).forEach(el => {
            if (el.__popupBound) return;
            el.__popupBound = true;
            el.addEventListener('click', function (e) {
              e.preventDefault();
              showPopup();
            }, { passive: false });
          });
        }

        attachClickTriggers();

        // re-attach for dynamically added elements
        const observer = new MutationObserver(attachClickTriggers);
        observer.observe(document.body, { childList: true, subtree: true });
      }

      // If NO triggers are configured, default to show immediately
      if (!onLoad && !onScroll && !clickClass) {
        showPopup();
      }
    });
  </script>
@endif
