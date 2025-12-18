<footer class="text-[#25282a]" role="contentinfo">

  <section class="relative py-24 bg-gradient-to-b from-[#0b1118] via-[#0b0f14] to-black text-white overflow-hidden">
    <!-- soft ambient accents -->
    <span aria-hidden="true" class="pointer-events-none absolute -top-24 -left-24 h-80 w-80 rounded-full bg-cyan-400/10 blur-3xl"></span>
    <span aria-hidden="true" class="pointer-events-none absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-indigo-400/10 blur-3xl"></span>

    <div class="max-w-[1500px] mx-auto px-6">
      <!-- Header -->
      <header class="flex flex-col md:flex-row md:items-end md:justify-between gap-6" data-aos="fade-up">
        <div>
          <p class="text-xl tracking-[.25em] text-white/70 font-semibold uppercase">Discover</p>
          <h2 class="mt-2 text-4xl md:text-5xl font-semibold leading-tight text-white">Find Your Next Stay</h2>
          <p class="mt-3 text-lg sm:text-base lg:text-xl text-white/80 max-w-xl">
            Browse by vibe, Family, Beach, Couple, or Corporate and explore the Astoria that fits your moment.
          </p>
        </div>

        <!-- Filters -->
        <div class="w-full md:w-auto" data-aos="fade-up" data-aos-delay="100">
          <div id="stay-filters" class="inline-flex flex-wrap items-center gap-2 rounded-2xl bg-white/5 p-2 ring-1 ring-white/10 backdrop-blur">
            <button type="button" class="filter-chip px-4 py-2 rounded-full text-s font-semibold text-white bg-white/20 shadow"
                    data-filter="all" aria-pressed="true">All</button>
            <button type="button" class="filter-chip px-4 py-2 rounded-full text-s font-semibold text-white hover:bg-white/10"
                    data-filter="family" aria-pressed="false">Family</button>
            <button type="button" class="filter-chip px-4 py-2 rounded-full text-s font-semibold text-white hover:bg-white/10"
                    data-filter="beach" aria-pressed="false">Beach</button>
            <button type="button" class="filter-chip px-4 py-2 rounded-full text-s font-semibold text-white hover:bg-white/10"
                    data-filter="couple" aria-pressed="false">Couple</button>
            <button type="button" class="filter-chip px-4 py-2 rounded-full text-s font-semibold text-white hover:bg-white/10"
                    data-filter="corporate" aria-pressed="false">Corporate</button>
          </div>
        </div>
      </header>

      <div class="mt-8 border-b border-white/10"></div>

      <!-- DEFAULT LAYOUT: parent grid (tablet: 2 cols consistent, desktop: 3 cols)
           Wrappers use mobile grid (1 col + gap-6) -> md:contents -> lg:2-col grid row -->
      <div id="stay-default" class="mt-10 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- AB1 -->
        <article class="cta-card group relative h-[460px] md:h-[500px] rounded-2xl overflow-hidden ring-1 ring-white/10 bg-white/5"
                 data-category="beach,couple" data-aos="fade-up" data-aos-delay="0">
          <img src="{{ asset('images/footer/ab1.webp') }}" alt="Astoria Boracay" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-90">
          <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/35 to-transparent"></div>
          <div class="absolute top-4 left-4 flex gap-2">
            <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold ring-1 ring-black text-black backdrop-blur">Station 1</span>
            <span class="inline-flex items-center rounded-full bg-black px-3 py-1 text-xs font-semibold ring-1 ring-white text-white">Couple</span>
            <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold ring-1 ring-black text-black">Beach</span>
          </div>
          <div class="absolute inset-x-0 bottom-0 p-6">
            <h3 class="text-2xl font-semibold">Astoria Boracay</h3>
            <p class="mt-1 text-base font-medium text-white/90">Luxury resort experience along the white sands of Station 1.</p>
            <div class="mt-5 flex items-center justify-between">
              <span class="text-base font-medium text-white/90">Station 1, Boracay</span>
              <a href="https://astoriaboracay.com/" class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2 text-xs font-bold text-black shadow transition hover:translate-y-[-2px]">
                Visit
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/></svg>
              </a>
            </div>
          </div>
        </article>

        <!-- AC3 -->
        <article class="cta-card group relative h-[460px] md:h-[500px] rounded-2xl overflow-hidden ring-1 ring-white/10 bg-white/5"
                 data-category="family,beach,couple" data-aos="fade-up" data-aos-delay="100">
          <img src="{{ asset('images/footer/ac3.webp') }}" alt="Astoria Current" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-90">
          <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-black/10 to-transparent"></div>
          <div class="absolute top-4 left-4 flex gap-2">
            <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold ring-1 ring-black text-black backdrop-blur">Station 3</span>
            <span class="inline-flex items-center rounded-full bg-black px-3 py-1 text-xs font-semibold ring-1 ring-white text-white">Beach</span>
            <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold ring-1 ring-black text-black">Couple</span>
          </div>
          <div class="absolute inset-x-0 bottom-0 p-6">
            <h3 class="text-2xl font-semibold">Astoria Current</h3>
            <p class="mt-1 text-base font-medium text-white/90">A vibrant beachfront resort at Station 3, Boracay.</p>
            <div class="mt-5 flex items-center justify-between">
              <span class="text-base font-medium text-white/90">Station 3, Boracay</span>
              <a href="https://astoriacurrent.com/" class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2 text-xs font-bold text-black shadow transition hover:translate-y-[-2px]">
                Visit
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/></svg>
              </a>
            </div>
          </div>
        </article>

        <!-- APW -->
        <article class="cta-card group relative h-[460px] md:h-[500px] rounded-2xl overflow-hidden ring-1 ring-white/10 bg-white/5"
                 data-category="corporate,beach,family" data-aos="fade-up" data-aos-delay="0">
          <img src="{{ asset('images/footer/apw.webp') }}" alt="Astoria Palawan" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-90">
          <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-black/15 to-transparent"></div>
          <div class="absolute top-4 left-4 flex gap-2">
            <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold ring-1 ring-black text-black backdrop-blur">Puerto Princesa</span>
            <span class="inline-flex items-center rounded-full bg-black px-3 py-1 text-xs font-semibold ring-1 ring-white text-white">Family</span>
            <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold ring-1 ring-black text-black">Beach</span>
          </div>
          <div class="absolute inset-x-0 bottom-0 p-6">
            <h3 class="text-2xl font-semibold">Astoria Palawan</h3>
            <p class="mt-1 text-base font-medium text-white/90">Eco-friendly resort surrounded by lush landscapes.</p>
            <div class="mt-5 flex items-center justify-between">
              <span class="text-base font-medium text-white/90">Puerto Princesa, Palawan</span>
              <a href="https://astoriapalawan.com/" class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2 text-xs font-bold text-black shadow transition hover:translate-y-[-2px]">
                Visit
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/></svg>
              </a>
            </div>
          </div>
        </article>

        <!-- APZ + AGB (mobile=grid with gap-6; md=contents; lg=two cols spanning 3) -->
        <div class="grid grid-cols-1 gap-6 md:contents lg:grid lg:grid-cols-2 lg:gap-6 lg:col-span-3">
          <!-- APZ -->
          <article class="cta-card group relative h-[460px] md:h-[500px] rounded-2xl overflow-hidden ring-1 ring-white/10 bg-white/5"
                   data-category="family,corporate" data-aos="fade-up" data-aos-delay="100">
            <img src="{{ asset('images/footer/apz.webp') }}" alt="Astoria Plaza" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-90">
            <div class="absolute inset-0 bg-gradient-to-t from-black/25 via-black/25 to-transparent"></div>
            <div class="absolute top-4 left-4 flex gap-2">
              <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold ring-1 ring-black text-black backdrop-blur">Ortigas, Pasig</span>
              <span class="inline-flex items-center rounded-full bg-black px-3 py-1 text-xs font-semibold ring-1 ring-white text-white">Corporate</span>
              <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold ring-1 ring-black text-black">Family</span>
            </div>
            <div class="absolute inset-x-0 bottom-0 p-6">
              <h3 class="text-2xl font-semibold">Astoria Plaza</h3>
              <p class="mt-1 text-base font-medium text-white/90">Modern comfort at the heart of Ortigas Business District.</p>
              <div class="mt-5 flex items-center justify-between">
                <span class="text-base font-medium text-white/90">Pasig, Metro Manila</span>
                <a href="https://astoriaplaza.com/" class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2 text-xs font-bold text-black shadow transition hover:translate-y-[-2px]">
                  Visit
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/></svg>
                </a>
              </div>
            </div>
          </article>

          <!-- AGB -->
          <article class="cta-card group relative h-[460px] md:h-[500px] rounded-2xl overflow-hidden ring-1 ring-white/10 bg-white/5"
                   data-category="corporate,couple" data-aos="fade-up" data-aos-delay="200">
            <img src="{{ asset('images/footer/agb.webp') }}" alt="Astoria Greenbelt" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-90">
            <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-black/20 to-transparent"></div>
            <div class="absolute top-4 left-4 flex gap-2">
              <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold ring-1 ring-black text-black backdrop-blur">Greenbelt, Makati</span>
              <span class="inline-flex items-center rounded-full bg-black px-3 py-1 text-xs font-semibold ring-1 ring-white text-white">Corporate</span>
              <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold ring-1 ring-black text-black">Couple</span>
            </div>
            <div class="absolute inset-x-0 bottom-0 p-6">
              <h3 class="text-2xl font-semibold">Astoria Greenbelt</h3>
              <p class="mt-1 text-base font-medium text-white/90">Business meets comfort in Makati’s center.</p>
              <div class="mt-5 flex items-center justify-between">
                <span class="text-base font-medium text-white/90">Makati, Metro Manila</span>
                <a href="https://astoriagreenbelt.com/" class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2 text-xs font-bold text-black shadow transition hover:translate-y-[-2px]">
                  Visit
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/></svg>
                </a>
              </div>
            </div>
          </article>
        </div>

        <!-- ABH — full width ONLY on desktop; on tablet it behaves in 2-col flow -->
        <article class="cta-card group relative h-[460px] md:h-[500px] rounded-2xl overflow-hidden ring-1 ring-white/10 bg-white/5 lg:col-span-3"
                 data-category="beach" data-aos="fade-up" data-aos-delay="0">
          <img src="{{ asset('images/footer/abh.webp') }}" alt="Astoria Bohol" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-90">
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
          <div class="absolute top-4 left-4 flex gap-2">
            <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold ring-1 ring-black text-black backdrop-blur">Baclayon, Bohol</span>
            <span class="inline-flex items-center rounded-full bg-black px-3 py-1 text-xs font-semibold ring-1 ring-white text-white">Beach</span>
            <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold ring-1 ring-black text-black">Family</span>
          </div>
          <div class="absolute inset-x-0 bottom-0 p-6">
            <h3 class="text-2xl font-semibold">Astoria Bohol</h3>
            <p class="mt-1 text-base font-medium text-white/90">A peaceful tropical escape in Baclayon.</p>
            <div class="mt-5 flex items-center justify-between">
              <span class="text-base font-medium text-white/90">Baclayon, Bohol</span>
              <a href="https://astoriabohol.com/" class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2 text-xs font-bold text-black shadow transition hover:translate-y-[-2px]">
                Visit
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/></svg>
              </a>
            </div>
          </div>
        </article>

        <!-- SPO + SPR (mobile=grid 1 col gap-6; md=contents; lg=two cols spanning 3) -->
        <div class="grid grid-cols-1 gap-6 md:contents lg:grid lg:grid-cols-2 lg:gap-6 lg:col-span-3">
          <!-- SPO -->
          <article class="cta-card group relative h-[460px] md:h-[500px] rounded-2xl overflow-hidden ring-1 ring-white/10 bg-white/5"
                   data-category="beach,couple" data-aos="fade-up" data-aos-delay="0">
            <img src="{{ asset('images/footer/spo.webp') }}" alt="Stellar Panglao" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-90">
            <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-black/10 to-transparent"></div>
            <div class="absolute top-4 left-4 flex gap-2">
              <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold ring-1 ring-black text-black backdrop-blur">Panglao, Bohol</span>
              <span class="inline-flex items-center rounded-full bg-black px-3 py-1 text-xs font-semibold ring-1 ring-white text-white">Family</span>
              <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold ring-1 ring-black text-black">Couple</span>
            </div>
            <div class="absolute inset-x-0 bottom-0 p-6">
              <h3 class="text-2xl font-semibold">Stellar Panglao</h3>
              <p class="mt-1 text-base font-medium text-white/90">Sun-drenched stays on Panglao’s shores.</p>
              <div class="mt-5 flex items-center justify-between">
                <span class="text-base font-medium text-white/90">Panglao, Bohol</span>
                <a href="https://stellarpanglao.com/" class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2 text-xs font-bold text-black shadow transition hover:translate-y-[-2px]">
                  Visit
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/></svg>
                </a>
              </div>
            </div>
          </article>

          <!-- SPR -->
          <article class="cta-card group relative h-[460px] md:h-[500px] rounded-2xl overflow-hidden ring-1 ring-white/10 bg-white/5"
                   data-category="family" data-aos="fade-up" data-aos-delay="100">
            <img src="{{ asset('images/footer/spr.webp') }}" alt="Stellar Potter's Ridge" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-90">
            <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-black/15 to-transparent"></div>
            <div class="absolute top-4 left-4 flex gap-2">
              <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold ring-1 ring-black text-black backdrop-blur">Tagaytay</span>
              <span class="inline-flex items-center rounded-full bg-black px-3 py-1 text-xs font-semibold ring-1 ring-white text-white">Family</span>
            </div>
            <div class="absolute inset-x-0 bottom-0 p-6">
              <h3 class="text-2xl font-semibold">Stellar Potter's Ridge</h3>
              <p class="mt-1 text-base font-medium text-white/90">Cool-climate escapes with sweeping views.</p>
              <div class="mt-5 flex items-center justify-between">
                <span class="text-base font-medium text-white/90">Tagaytay</span>
                <a href="https://stellarpottersridge.com/" class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2 text-xs font-bold text-black shadow transition hover:translate-y-[-2px]">
                  Visit
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/></svg>
                </a>
              </div>
            </div>
          </article>
        </div>
      </div>

      <!-- FILTERED LAYOUT (uniform grid; ABH should NOT force full width here) -->
      <div id="stay-results" class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 hidden"></div>
    </div>
  </section>

  <script>
  /**
   * Category filtering:
   * - "All": show unified default grid (tablet=2 cols consistently, desktop=3; ABH full width on lg only)
   * - Specific category: hide default; render matches into a uniform grid
   *   and ensure ABH does NOT span full width when filtered.
   */
  document.addEventListener('DOMContentLoaded', () => {
    const chips = Array.from(document.querySelectorAll('.filter-chip'));
    const defaultWrap = document.getElementById('stay-default');
    const resultsWrap = document.getElementById('stay-results');
    const defaultCards = Array.from(defaultWrap.querySelectorAll('.cta-card')); // only cards, wrappers excluded

    function renderResults(filter) {
      resultsWrap.innerHTML = '';
      defaultCards.forEach(card => {
        const cats = (card.getAttribute('data-category') || '').toLowerCase();
        if (cats.includes(filter)) {
          const clone = card.cloneNode(true);
          clone.classList.remove('lg:col-span-3'); // ABH not full width in filtered grid
          resultsWrap.appendChild(clone);
        }
      });
      if (window.AOS && typeof AOS.refreshHard === 'function') {
        AOS.refreshHard();
      }
    }

    function applyFilter(filter) {
      if (filter === 'all') {
        resultsWrap.classList.add('hidden');
        defaultWrap.classList.remove('hidden');
      } else {
        defaultWrap.classList.add('hidden');
        resultsWrap.classList.remove('hidden');
        renderResults(filter);
      }
    }

    chips.forEach(chip => {
      chip.addEventListener('click', () => {
        const filter = chip.dataset.filter?.toLowerCase() || 'all';
        chips.forEach(c => {
          c.classList.remove('bg-white','text-black','shadow');  // legacy removal
          c.classList.remove('bg-white/20','shadow');            // current active removal
          c.setAttribute('aria-pressed','false');
        });
        chip.classList.add('bg-white/20','shadow');
        chip.setAttribute('aria-pressed','true');
        applyFilter(filter);
      });
    });

    // Init → default patterned layout
    applyFilter('all');
  });
  </script>


  {{-- ================= OUR PROPERTIES ================= --}}
  <section class="bg-[#3f464d] py-8 base:py-10">
    @php
      $logos = [
        ['src' => 'images/Astoria-Plaza.webp',          'alt' => 'Astoria Plaza',                                 'link' => 'https://astoriaplaza.com'],
        ['src' => 'images/Astoria-Greenbelt.webp',      'alt' => 'Astoria Greenbelt',                             'link' => 'https://astoriagreenbelt.com/'],
        ['src' => 'images/Astoria-Boracay.webp',        'alt' => 'Astoria Boracay',                               'link' => 'https://astoriaboracay.com/'],
        ['src' => 'images/Astoria-Current.webp',        'alt' => 'Astoria Current',                               'link' => 'https://astoriacurrent.com/'],
        ['src' => 'images/Astoria-Palawan.webp',        'alt' => 'Astoria Palawan',                               'link' => 'https://astoriapalawan.com/'],
        ['src' => 'images/Astoria-Bohol.webp',          'alt' => 'Astoria Bohol',                                 'link' => 'https://astoriabohol.com/'],
        ['src' => 'images/ACHI-white-300x224.webp',     'alt' => 'Astoria Culinary & Hospitality Institute',      'link' => 'https://astoriaculinaryandhospitalityinstitute.com/'],
        ['src' => 'images/ACES-white-300x253.webp',     'alt' => 'Astoria ACES',                                  'link' => 'https://astoria-aces.com/'],
        ['src' => 'images/SPR-logo-white-300x208.webp', 'alt' => "Stellar Potter's Ridge",                        'link' => 'https://stellarpottersridge.com/'],
        ['src' => 'images/spo-white.webp',              'alt' => 'Stellar Panglao',                               'link' => 'https://stellarpanglao.com/'],
        ['src' => 'images/Waterpark.webp',              'alt' => 'Palawan Waterpark by Astoria',                  'link' => 'https://astoriapalawan.com/waterpark/'],
        ['src' => 'images/Chardonnay.webp',             'alt' => 'Chardonnay by Astoria',                         'link' => 'https://chardonnaybyastoria.com/'],
        ['src' => 'images/Minamisaki.webp',             'alt' => 'Minami Saki by Astoria',                        'link' => 'https://minamisakibyastoria.com/'],
        ['src' => 'images/mng.webp',                    'alt' => 'Mangrove Conference & Convention Center',       'link' => 'https://astoriapalawan.com/meetings-events/'],
      ];
    @endphp

    <div class="max-w-[1600px] mx-auto px-4 base:px-6 lg:px-8 text-white/90">
      <div class="mb-5 flex items-center justify-between gap-3">
        <h6 class="text-base font-semibold tracking-widest uppercase">Our Properties</h6>
        <span class="hidden base:block text-xs opacity-80">Trusted destinations &amp; experiences</span>
      </div>

      {{-- 2×7 on XL, equal-height logo containers + equal logo heights --}}
      <div class="grid grid-cols-2 base:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7 gap-px rounded-xl overflow-hidden bg-white/10">
        @foreach ($logos as $logo)
          <a href="{{ $logo['link'] }}" target="_blank" rel="noopener"
            class="group bg-white/5 flex items-center justify-center h-28 base:h-32 lg:h-40
                    focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60"
            aria-label="{{ $logo['alt'] }}" title="{{ $logo['alt'] }}">
            <img
              src="{{ asset($logo['src']) }}"
              alt="{{ $logo['alt'] }}"
              class="h-14 base:h-16 lg:h-20 w-auto object-contain opacity-85 grayscale
                    group-hover:grayscale-0 group-hover:opacity-100 transition duration-300"
              loading="lazy" decoding="async"
            >
          </a>
        @endforeach
      </div>

    </div>
  </section>

  {{-- ================= MAIN (WHITE) ================= --}}
  <section class="relative bg-white text-[#25282a]">
    <div
      class="w-full max-w-[1600px] mx-auto px-4 base:px-6 lg:px-8 py-12 md:py-14 lg:py-16
             grid grid-cols-1 md:grid-cols-2 xl:grid-cols-12 gap-6 md:gap-8 xl:gap-10">

      {{-- Brand & Map --}}
      <div class="md:col-span-1 xl:col-span-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-7 shadow-base h-full">
          <a href="{{ route('home') }}" aria-label="Astoria Home" class="inline-flex items-center gap-3 mb-5">
            <img
              src="{{ asset('images/abh-logo.webp') }}"
              onerror="this.onerror=null; this.src='{{ asset('images/apz-logo.webp') }}';"
              alt="Astoria Hotels and Resorts"
              class="h-10 md:h-8 w-auto"
              decoding="async" loading="lazy"
            >
            <span class="sr-only">Astoria Bohol</span>
          </a>

          <div class="mt-5 overflow-hidden rounded-xl border border-gray-200">
            <iframe
              src="https://maps.google.com/maps?q=Astoria%20Bohol%&output=embed&iWloc=near"
              class="w-full h-44 base:h-48 md:h-56 xl:h-60 border-0"
              allowfullscreen loading="lazy"
              referrerpolicy="no-referrer-when-downgrade" title="Astoria Bohol Map"></iframe>
          </div>

          <div class="mt-5 flex items-center gap-4 text-lg text-[#CF4520]">
            <a href="https://www.facebook.com/astoriabohol" target="_blank" rel="noopener" class="hover:opacity-80" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.instagram.com/astoriabohol" target="_blank" rel="noopener" class="hover:opacity-80" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="https://www.youtube.com/c/AstoriaHotelsandResorts" target="_blank" rel="noopener" class="hover:opacity-80" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            <a href="https://www.tiktok.com/@theastoriagroup" target="_blank" rel="noopener" class="hover:opacity-80" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
          </div>
        </div>
      </div>

      {{-- Site Menu (mirrors updated header structure) --}}
      <div class="md:col-span-1 xl:col-span-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-7 shadow-base h-full">
          <h6 class="text-lg font-semibold mb-4 text-[#CF4520]">Site Menu</h6>

          {{-- Single-level links that sit at top level in header --}}
          <div class="mt-6 grid grid-cols-1 base:grid-cols-2 gap-3 text-[15px]">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 font-semibold hover:text-[#CF4520]">
              <i class="fa-solid fa-house text-[12px] text-[#CF4520]"></i> Home
            </a>
            <a href="{{ route('lantawan') }}" class="inline-flex items-center gap-2 font-semibold hover:text-[#CF4520]">
              <i class="fa-solid fa-tree-city text-[12px] text-[#CF4520]"></i> Astoria Bohol Lantawan
            </a>
          </div>

          <div class="grid grid-cols-1 base:grid-cols-2 mt-2 gap-6">
            {{-- About + sub-links --}}
            <div>
              <a href="{{ route('about') }}" class="inline-flex items-center gap-2 font-semibold hover:text-[#CF4520]">
                <i class="fa-solid fa-circle-info text-[12px] text-[#CF4520]"></i>
                <span>About</span>
              </a>
              <ul class="mt-3 space-y-2 text-[15px]">
                <li><a href="{{ route('amenities') }}" class="hover:text-[#CF4520]">Amenities</a></li>
                <li><a href="{{ route('explore') }}" class="hover:text-[#CF4520]">Explore</a></li>
                <li><a href="{{ route('faqs') }}"      class="hover:text-[#CF4520]">FAQs</a></li>
                <li><a href="{{ route('blogs') }}"     class="hover:text-[#CF4520]">Blogs</a></li>
                <li><a href="{{ route('promos') }}"    class="hover:text-[#CF4520]">Promos</a></li>
              </ul>
            </div>

            {{-- Accommodations + sub-links --}}
            <div>
              <a href="{{ route('accommodations') }}" class="inline-flex items-center gap-2 font-semibold hover:text-[#CF4520]">
                <i class="fa-solid fa-bed text-[12px] text-[#CF4520]"></i>
                <span>Accommodations</span>
              </a>
              <ul class="mt-3 space-y-2 text-[15px]">
                <li><a href="{{ route('deluxe') }}"    class="hover:text-[#CF4520]">Deluxe Room</a></li>
                <li><a href="{{ route('luxury') }}"   class="hover:text-[#CF4520]">Luxury Room</a></li>
              </ul>
            </div>
          </div>

          {{-- Single-level links that sit at top level in header --}}
          <div class="mt-6 grid grid-cols-1 base:grid-cols-2 gap-3 text-[15px]">
            <a href="{{ url('/food-beverages') }}" class="inline-flex items-center gap-2 font-medium hover:text-[#CF4520]">
              <i class="fa-solid fa-utensils text-[12px] text-[#CF4520]"></i> Dining
            </a>
            <a href="{{ url('/meetings-events') }}" class="inline-flex items-center gap-2 font-medium hover:text-[#CF4520]">
              <i class="fa-solid fa-calendar-days text-[12px] text-[#CF4520]"></i> Meetings &amp; Events
            </a>
            <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 font-medium hover:text-[#CF4520]">
              <i class="fa-solid fa-envelope-open-text text-[12px] text-[#CF4520]"></i> Contact Us
            </a>
          </div>
        </div>
      </div>

      {{-- Get In Touch --}}
      <div class="md:col-span-2 xl:col-span-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-7 shadow-base h-full">
          {{-- VISIT US --}}
          <h6 class="text-lg font-semibold text-[#CF4520]">VISIT US</h6>
          <p class="mt-2 text-base leading-relaxed">
            Barangay Taguihon, Baclayon, Bohol, Philippines 6301
          </p>

          {{-- CONTACT US --}}
          <h6 class="mt-6 text-lg font-semibold text-[#CF4520]">CONTACT US</h6>

          <div class="mt-2 space-y-4 text-base">
            {{-- Reservations Office --}}
            <div>
              <div class="font-semibold">Reservations Office</div>
              <div class="mt-1 leading-relaxed">
                <span class="opacity-80">Email:</span>
                <a href="mailto:rsvn@astoriabohol.com" class="underline underline-offset-4 hover:text-[#CF4520]">
                  rsvn@astoriabohol.com
                </a>
                <br>

                <span class="opacity-80">Landline:</span>
                <a href="tel:+63384114695" class="hover:text-[#CF4520]">(+63 38) 411-4695</a>
                <span class="opacity-70"> | </span>
                <a href="tel:+63253351111" class="hover:text-[#CF4520]">(+63 2) 5335-1111</a>
                <span class="opacity-70">&nbsp;loc. 8745/8746</span>
                <br>

                <span class="opacity-80">Mobile:</span>
                <a href="tel:+639178898275" class="hover:text-[#CF4520]">(+63) 917-889-8275</a>
                <span class="opacity-70"> | </span>
                <a href="tel:+639199113961" class="hover:text-[#CF4520]">(+63) 919-911-3961</a>
                <span class="opacity-70"> | </span>
                <a href="tel:+639175459683" class="hover:text-[#CF4520]">(+63) 917-545-9683</a>
              </div>
            </div>

            {{-- Front Office --}}
            <div>
              <div class="font-semibold">Front Office</div>
              <div class="mt-1 leading-relaxed">
                <span class="opacity-80">Landline:</span>
                <a href="tel:+63384114695" class="hover:text-[#CF4520]">(+63 38) 411-4695</a>
                <br>

                <span class="opacity-80">Mobile:</span>
                <a href="tel:+639285523543" class="hover:text-[#CF4520]">(+63) 928-552-3543</a>
                <span class="opacity-70"> | </span>
                <a href="tel:+639178898275" class="hover:text-[#CF4520]">(+63) 917-889-8275</a>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </section>

  {{-- ================= BOTTOM BAR ================= --}}
  <div class="relative bg-[#3F2021] text-white">
    <div class="max-w-[1600px] mx-auto px-4 base:px-6 lg:px-8 py-4 text-center text-base md:text-[15px]">
      Copyright © {{ date('Y') }} | &nbsp;Astoria Bohol
      <span class="mx-2 opacity-60">|</span>
      <a href="{{ route('privacy-policy') }}" class="underline underline-offset-4 hover:text-[#CF4520]">Privacy Policy</a>
    </div>
    <div class="absolute -top-px left-0 right-0 h-px bg-white/20"></div>
  </div>
</footer>
