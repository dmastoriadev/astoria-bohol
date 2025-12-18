@extends('layouts.app')
@section('title', 'FAQs | Astoria Bohol')
@section('content')

<section
  id="faqs-hero-angled-left-55"
  class="relative w-full overflow-hidden text-[#25282a] bg-white"
  aria-label="About Astoria Hotels and Resorts hero banner"
  x-data="{ loaded:false }"
  x-init="requestAnimationFrame(()=>{ loaded=true })"
  :class="loaded ? 'is-in' : ''"
>
  {{-- DESKTOP BG (angled image) --}}
  <div class="absolute inset-0 right-clip hidden lg:block">
    <img
      src="{{ asset('images/faqs-header.webp') }}"
      alt="Astoria Hotels and Resorts"
      class="absolute inset-0 h-full w-full object-cover bg-shift will-change-transform transition-transform duration-700 lg:hover:scale-[1.03]"
      loading="eager"
      fetchpriority="high"
    />
    <div
      class="absolute inset-0 pointer-events-none"
      style="background: linear-gradient(135deg, rgba(0,0,0,0.35) 0%, rgba(0,0,0,0.0) 45%);"
    ></div>
  </div>

  {{-- TOP-LEFT HEADER VECTOR (all breakpoints, under content, animated) --}}
  <div aria-hidden="true" class="pointer-events-none absolute inset-0 z-[6]">
    <div
      class="vector-top-fx absolute
             top-[60px] sm:top-[50px] md:top-[60px] lg:top-[90px]
             left-3"
    >
      <img
        src="{{ asset('images/header-top.webp') }}"
        alt=""
        class="w-12 sm:w-40 md:w-24 lg:w-36
               max-w-none select-none
               -translate-x-12%] sm:-translate-x-[10%] md:-translate-x-[8%] lg:-translate-x-[5%]"
        loading="lazy"
        decoding="async"
        draggable="false"
      >
    </div>
  </div>

  {{-- BOTTOM-LEFT VECTOR: DESKTOP VERSION (behind content, animated) --}}
  <div aria-hidden="true" class="pointer-events-none absolute inset-0 z-[1] hidden lg:block">
    <div class="vector-btm-desktop-fx absolute left-3 bottom-5">
      <img
        src="{{ asset('images/header-btm.webp') }}"
        alt=""
        class="w-12 lg:w-56
               max-w-none select-none
               -translate-x-[4%]
               lg:-translate-y-[6%] xl:-translate-y-[10%] 2xl:-translate-y-[0%]"
        loading="lazy"
        decoding="async"
        draggable="false"
      >
    </div>
  </div>

  {{-- MOBILE & TABLET (3 rows: text / vector / image) --}}
  <div class="tablet-stack relative pt-[120px] z-10 grid lg:hidden min-h-[860px] md:min-h-[900px] grid-rows-[auto_auto_minmax(0,1fr)]">
    {{-- Row 1: Content (centered, with bottom space reserved) --}}
    <div class="relative px-6 pt-10 pb-10 sm:pb-12 md:pb-14 flex items-center justify-center overflow-visible">
      <div class="w-full max-w-3xl text-center relative z-10">
        {{-- SUB HEADING (orange) --}}
        <p class="fx fx-right text-sm sm:text-base font-semibold tracking-[.28em] uppercase" style="--d:40ms; color:#3F2021;">
         FAQs
        </p>

        {{-- HEADER (teal) --}}
        <h1 class="fx fx-right font-semibold leading-tight tracking-tight text-3xl sm:text-4xl md:text-5xl mt-2" style="--d:100ms">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#CF4520] via-[#CF4520] to-[#CF4520]/85">
            FREQUENTLY ASKED QUESTIONS

          </span>
        </h1>

        {{-- Divider (orange) --}}
        <div
          class="fx fx-right mx-auto my-5 h-0.5 w-16 rounded-full"
          style="--d:160ms; background-color:#3F2021;"
          aria-hidden="true"
        ></div>

        {{-- DESCRIPTION (dark) --}}
        <p class="fx fx-right text-base sm:text-lg md:text-xl font-medium text-[#25282a]/90" style="--d:220ms">



        </p>
      </div>
    </div>

    {{-- Row 2: Vector row (sits at the seam, overlaps slightly using -mt) --}}
    <div class="relative -mt-6 sm:-mt-8 md:-mt-10 pb-2 flex items-start justify-start">
      <div class="vector-btm-mobile-fx pl-0">
        <img
          src="{{ asset('images/header-btm.webp') }}"
          alt=""
          class="w-28 sm:w-32 md:w-40
                 max-w-none select-none
                 -translate-x-[0%]
                 opacity-95"
          loading="lazy"
          decoding="async"
          draggable="false"
        >
      </div>
    </div>

    {{-- Row 3: Image (full width, no gaps) --}}
    <div class="relative">
      <img
        src="{{ asset('images/faqs-header.webp') }}"
        alt="Astoria Bohol"
        class="absolute inset-0 h-full w-full object-cover"
        loading="lazy"
      />
    </div>
  </div>

  {{-- DESKTOP (lg+): angled split, viewport height --}}
  <div class="desktop-angled relative z-10 left-clip hidden lg:flex min-h-[100svh] items-center">
    <div class="content-wrap w-full text-left px-6 lg:pl-24 lg:pr-10">
      <div class="max-w-2xl">
        {{-- SUB HEADING (orange) --}}
        <p class="fx fx-right text-sm md:text-base font-semibold tracking-[.28em] uppercase" style="--d:300ms; color:#3F2021;">
        FAQs



        </p>

        {{-- HEADER (teal) --}}
        <h1 class="fx fx-right font-semibold leading-tight tracking-tight text-5xl lg:text-6xl mt-2" style="--d:360ms">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#CF4520] via-[#CF4520] to-[#CF4520]/80">
          FREQUENTLY ASKED QUESTIONS
          </span>
        </h1>

        {{-- Divider (orange) --}}
        <div
          class="fx fx-right my-5 h-1 w-16 rounded-full"
          style="--d:440ms; background-color:#3F2021;"
          aria-hidden="true"
        ></div>

        {{-- DESCRIPTION (dark) --}}
        <p class="fx fx-right font-medium pr-[100px] text-[#25282a]/90 text-2xl" style="--d:520ms">

        </p>
      </div>
    </div>

    <span aria-hidden="true" class="pointer-events-none absolute inset-0 ring-1 ring-inset ring-white/10"></span>
  </div>

  <style>
    /* Default: no clipping for non-desktop */
    #faqs-hero-angled-left-55 .left-clip,
    #faqs-hero-angled-left-55 .right-clip {
      clip-path: none;
      -webkit-clip-path: none;
    }

    /* Desktop (lg+): 55° seam */
    @media (min-width: 1024px){
      #faqs-hero-angled-left-55 .left-clip{
        clip-path: polygon(0% 0%, 70% 0%, 30% 100%, 0% 100%);
        -webkit-clip-path: polygon(0% 0%, 70% 0%, 30% 100%, 0% 100%);
      }
      #faqs-hero-angled-left-55 .right-clip{
        clip-path: polygon(70% 0%, 100% 0%, 100% 100%, 30% 100%);
        -webkit-clip-path: polygon(70% 0%, 100% 0%, 100% 100%, 30% 100%);
      }

    #faqs-hero-angled-left-55 .right-clip > img.bg-shift{
        left: 10vw !important;
        right: auto !important;
        width: calc(100% + 12vw) !important;
        height: 100% !important;
        object-fit: cover !important;
        object-position: left center !important;
        transform: translateZ(0);
      }
    }

    /* iPad Pro/tablet landscapes: force tablet stack (show stack, hide desktop) */
    @media (min-width:1024px) and (max-width:1368px) and (hover:none) and (pointer:coarse) {
      #faqs-hero-angled-left-55 .tablet-stack {
        display: grid !important;
        min-height: 900px !important;
        padding-top: 32px !important;
      }
      #faqs-hero-angled-left-55 .desktop-angled,
      #faqs-hero-angled-left-55 .right-clip {
        display: none !important;
      }
      #faqs-hero-angled-left-55 .left-clip,
      #faqs-hero-angled-left-55 .right-clip {
        clip-path: none !important;
        -webkit-clip-path: none !important;
      }
    }
    @media (min-width:1024px) and (max-width:1368px) and (aspect-ratio: 3/4),
           (min-width:1194px) and (max-width:1368px) and (aspect-ratio: 4/3) {
      #faqs-hero-angled-left-55 .tablet-stack {
        display: grid !important;
        min-height: 900px !important;
        padding-top: 32px !important;
      }
      #faqs-hero-angled-left-55 .desktop-angled,
      #faqs-hero-angled-left-55 .right-clip {
        display: none !important;
      }
      #faqs-hero-angled-left-55 .left-clip,
      #faqs-hero-angled-left-55 .right-clip {
        clip-path: none !important;
        -webkit-clip-path: none !important;
      }
    }

    /* Entrance FX for text */
    #faqs-hero-angled-left-55 .fx{
      opacity: .001;
      transform: translateX(-14px);
      will-change: transform, opacity;
      transition:
        opacity 520ms ease,
        transform 640ms cubic-bezier(.22,1,.36,1);
      transition-delay: var(--d, 0ms);
    }
    #faqs-hero-angled-left-55.is-in .fx{
      opacity: 1;
      transform: translateX(0);
    }

    /* Slide-in FX for vectors (top + bottom) – from left to right, slower */
    #faqs-hero-angled-left-55 .vector-top-fx,
    #faqs-hero-angled-left-55 .vector-btm-mobile-fx,
    #faqs-hero-angled-left-55 .vector-btm-desktop-fx{
      opacity: 0;
      transform: translateX(-32px);
      will-change: transform, opacity;
      transition:
        opacity 700ms ease,
        transform 950ms cubic-bezier(.16,1,.3,1);
    }

    #faqs-hero-angled-left-55.is-in .vector-top-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 260ms;
    }

    #faqs-hero-angled-left-55.is-in .vector-btm-mobile-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 320ms;
    }

    #faqs-hero-angled-left-55.is-in .vector-btm-desktop-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 320ms;
    }

    /* Extra safety: very small phones (optional tweak if needed) */
    @media (max-width: 400px){
      #faqs-hero-angled-left-55 .vector-btm-mobile-fx img{
        width: 6rem;
      }
    }
    @media (max-width: 360px){
      #faqs-hero-angled-left-55 .vector-btm-mobile-fx{
        display: none;
      }
    }

    @media (prefers-reduced-motion: reduce){
      #faqs-hero-angled-left-55 .fx{
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
      #faqs-hero-angled-left-55 img{
        transition: none !important;
        transform: none !important;
      }
      #faqs-hero-angled-left-55 .vector-top-fx,
      #faqs-hero-angled-left-55 .vector-btm-mobile-fx,
      #faqs-hero-angled-left-55 .vector-btm-desktop-fx{
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
    }
  </style>
</section>


@php
  $faqs = [
    [
      'q' => 'Are you accepting guests?',
      'a' => <<<'HTML'
<p>Yes, we are accepting guests here at Astoria Bohol. Our rooms are exclusive for members of Astoria Vacation and Leisure Club, Inc. (AVLCI). Non-members may coordinate with our Reservations Office for further inquiries and availability of rooms through the following:</p>
<p>
  &ndash; Email: <a href="mailto:rsvn@astoriabohol.com">rsvn@astoriabohol.com</a><br>
  &ndash; Landline: <a href="tel:+63384114695">(+63 38) 411-4695</a> | <a href="tel:+63253351111">(+63 2) 5335-1111</a> loc. 8745/8746<br>
  &ndash; Mobile: <a href="tel:+639178898275">(+63) 917-889-8275</a> | <a href="tel:+639199113961">(+63) 919-911-3961</a> | <a href="tel:+639175459683">(+63) 917-545-9683</a>
</p>
<p>Announcements will also be posted here on our website and our social media accounts once we can officially accommodate non-members.</p>
HTML
    ],
    [
      'q' => 'Where are you located?',
      'a' => <<<'HTML'
<p>Astoria Bohol is located at Barangay Taguihon, Baclayon, Bohol, 6301 Philippines.</p>
HTML
    ],
    [
      'q' => 'How many rooms do you have?',
      'a' => <<<'HTML'
<p>Astoria Bohol’s main resort has 8 rooms, while Astoria Bohol Lantawan has 2 villas for occupancy.</p>
HTML
    ],
    [
      'q' => 'What time is your check-in?',
      'a' => <<<'HTML'
<p>Our standard check-in time is 4:00 PM.</p>
HTML
    ],
    [
      'q' => 'What time is your check-out?',
      'a' => <<<'HTML'
<p>Our standard check-out time is 11:00 AM.</p>
HTML
    ],
    [
      'q' => 'Is Wi-Fi available at the resort/in the room?',
      'a' => <<<'HTML'
<p>Yes, guests are provided with complimentary Wi-Fi access good for 4 devices only.</p>
HTML
    ],
    [
      'q' => 'Do you offer airport transfers?',
      'a' => <<<'HTML'
<p>Yes, we do. Should you require airport transfers, arrangements can be made through the Front Desk. We recommend leaving the resort at least 2 hours prior to your flight’s scheduled departure time.</p>
HTML
    ],
    [
      'q' => 'Is Pamana Café open and available to non-members?',
      'a' => <<<'HTML'
<p>Pamana Café is open to both members and non-members. It operates from 6:00 AM until 10:00 PM.</p>
HTML
    ],
    [
      'q' => 'Are guests allowed to cook in the room?',
      'a' => <<<'HTML'
<p>Members and guests are not allowed to cook inside the rooms.</p>
HTML
    ],
    [
      'q' => 'Is the function room of Astoria Bohol Lantawan available for guests?',
      'a' => <<<'HTML'
<p>For events, Astoria Bohol Lantawan Events Hall is open for members and non-members. It can accommodate a maximum number of 60 guests.</p>
HTML
    ],
    [
      'q' => 'Are pets allowed in the resort?',
      'a' => <<<'HTML'
<p>As much as we love pets, they are not allowed in the property.</p>
HTML
    ],
    [
      'q' => 'Do you offer spa services?',
      'a' => <<<'HTML'
<p>Yes, Astoria Bohol offers spa services. However, an appointment is needed at least 2 hours before.</p>
HTML
    ],
    [
      'q' => 'Do you offer other amenities for outdoor activities?',
      'a' => <<<'HTML'
<p>For a more pleasurable stay at Astoria Bohol, we offer crystal and ordinary kayaks, fat bike/beach bikes, water bikes, beach volleyball, stand-up paddle boards, board games, and children’s beach toys.</p>
<p>Please note that water activities are usable only during high tide. All activities are available daily.</p>
HTML
    ],
    [
      'q' => 'Are non-members allowed to have access to the beach?',
      'a' => <<<'HTML'
<p>Yes, non-members may have access to the beach and its activities upon dining in our restaurant, Pamana.</p>
HTML
    ],
    [
      'q' => 'Are non-members allowed to use the infinity pool and other resort facilities?',
      'a' => <<<'HTML'
<p>The resort and its facilities, including the infinity pool, are strictly exclusive for checked-in members of Astoria Vacation and Leisure Club, Inc. (AVLCI) only.</p>
HTML
    ],
    [
      'q' => 'What are the tourist spots I can visit in the area?',
      'a' => <<<'HTML'
<p>The province of Baclayon is home to many attractions, including the Baclayon Church and Museum, as well as the Blood Compact Shrine. It is located just 10 minutes away from Bohol’s capital, Tagbilaran City, which also boasts of world-class attractions.</p>
HTML
    ],
    [
      'q' => 'Do you offer tours?',
      'a' => <<<'HTML'
<p>Yes, we do offer tours. For more information, kindly email our team at <a href="mailto:fos@astoriabohol.com">fos@astoriabohol.com</a>.</p>
HTML
    ],
    [
      'q' => 'I have a few queries I need to have answered via call or email. How can I contact your Reservations Office?',
      'a' => <<<'HTML'
<p>You may reach our Reservations Office at:</p>
<p>
  &ndash; Email: <a href="mailto:rsvn@astoriabohol.com">rsvn@astoriabohol.com</a><br>
  &ndash; Landline: <a href="tel:+63384114695">(+63 38) 411-4695</a> | <a href="tel:+63253351111">(+63 2) 5335-1111</a> loc. 8745/8746<br>
  &ndash; Mobile: <a href="tel:+639178898275">(+63) 917-889-8275</a> | <a href="tel:+639199113961">(+63) 919-911-3961</a> | <a href="tel:+639175459683">(+63) 917-545-9683</a>
</p>
HTML
    ],
  ];
@endphp



<section x-data="faqSearch()" x-init="init(@js($faqs))" class="max-w-6xl mx-auto px-6 py-16">
  <h1 class="text-3xl md:text-4xl font-semibold text-center text-[#1c355e] mb-10">
    Frequently Asked Questions
  </h1>

  <div class="mb-6 relative">
    <input
      x-model="q"
      type="text"
      placeholder="Search questions…"
      class="w-full rounded-xl border border-gray-300 px-4 py-3 pr-10 focus:outline-none focus:ring-4 focus:ring-[#1c355e]/10"
    />
    <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor">
      <path d="M21 21 15.803 15.803m2.031-5.228a7.259 7.259 0 1 1-14.518 0 7.259 7.259 0 0 1 14.518 0Z"/>
    </svg>
  </div>

  <div x-show="filtered.length === 0" class="text-center text-gray-500 py-10">
    No results for “<span x-text="q"></span>”.
  </div>

  <template x-for="item in filtered" :key="item.id">
    <div class="mb-4 rounded-2xl border border-gray-200 bg-white shadow-sm">
      <button
        @click="toggle(item.id)"
        class="w-full px-6 py-5 text-left flex justify-between items-start"
        :aria-expanded="isOpen(item.id)"
      >
        <h3 class="font-semibold text-gray-900" x-html="hl(item.q)"></h3>
        <svg x-show="!isOpen(item.id)" class="h-5 w-5 text-gray-600" viewBox="0 0 24 24" fill="currentColor">
          <path d="M11 11V5a1 1 0 1 1 2 0v6h6a1 1 0 1 1 0 2h-6v6a1 1 0 1 1-2 0v-6H5a1 1 0 1 1 0-2h6z"/>
        </svg>
        <svg x-show="isOpen(item.id)" class="h-5 w-5 text-gray-700 rotate-180" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 15a1 1 0 0 1-.7-.29l-6-6a1 1 0 1 1 1.4-1.42L12 12.59l5.3-5.3a1 1 0 1 1 1.4 1.42l-6 6A1 1 0 0 1 12 15z"/>
        </svg>
      </button>

      <!-- Smooth dropdown (grid height animation) -->
      <div
        class="grid transition-all duration-300 ease-out"
        :style="isOpen(item.id) ? 'grid-template-rows:1fr; opacity:1' : 'grid-template-rows:0fr; opacity:0'"
      >
        <div class="overflow-hidden">
          <div class="px-6 pb-6 text-gray-700" x-html="hl(item.a)"></div>
        </div>
      </div>
    </div>
  </template>

  <script>
    function faqSearch() {
      return {
        all: [],
        q: '',
        filtered: [],
        openMap: {},

        init(items) {
          this.all = items.map((it, idx) => ({ id: idx, ...it }));
          this.filtered = this.all.slice();
          this.all.forEach((it, idx) => this.openMap[it.id] = (idx === 0));
          this.$watch('q', (val) => this.onSearch(val));
        },

        isOpen(id) { return !!this.openMap[id]; },
        setOpen(id, val) { this.openMap[id] = !!val; },
        toggle(id) { this.setOpen(id, !this.isOpen(id)); },

        strip(html) {
          return String(html).replace(/<[^>]+>/g, ' ').replace(/&nbsp;/g, ' ').trim();
        },

        onSearch(val) {
          const term = (val || '').toString().trim().toLowerCase();

          if (!term) {
            this.filtered = this.all.slice();
            this.all.forEach((it, idx) => this.setOpen(it.id, idx === 0));
            return;
          }

          this.filtered = this.all.filter(i => {
            const hay = (i.q + ' ' + this.strip(i.a)).toLowerCase();
            return hay.includes(term);
          });

          this.all.forEach(it => this.setOpen(it.id, false));
          this.filtered.forEach(it => this.setOpen(it.id, true));

          this.$nextTick(() => {
            const first = document.querySelector('mark');
            first?.scrollIntoView({ behavior: 'smooth', block: 'center' });
          });
        },

        get regex() {
          if (!this.q) return null;
          const esc = this.q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
          return new RegExp(esc, 'gi');
        },
        hl(str) {
          if (!this.q || !this.regex) return str;
          return String(str).replace(this.regex, m => `<mark class="bg-yellow-200">${m}</mark>`);
        }
      }
    }
  </script>
</section>


{{-- ===== Full-width decorative vector ===== --}}
<section id="footer-vector" class="relative w-full overflow-hidden pt-5" aria-hidden="true">
  <img
    src="{{ asset('images/footer-vector.webp') }}"
    alt=""
    class="block w-full h-auto select-none pointer-events-none"
    loading="lazy"
    decoding="async"
  >
</section>
@endsection
