

@extends('layouts.app')
@section('title', 'Contact Us | Astoria Bohol')
@section('content')


<section
  id="contact-hero-angled-left-55"
  class="relative w-full overflow-hidden text-[#25282a] bg-white"
  aria-label="About Astoria Hotels and Resorts hero banner"
  x-data="{ loaded:false }"
  x-init="requestAnimationFrame(()=>{ loaded=true })"
  :class="loaded ? 'is-in' : ''"
>
  {{-- DESKTOP BG (angled image) --}}
  <div class="absolute inset-0 right-clip hidden lg:block">
    <img
      src="{{ asset('images/contact-header.webp') }}"
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
        class="w-12 sm:w-40 md:w-24 lg:w-32
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
   
        </p>

        {{-- HEADER (teal) --}}
        <h1 class="fx fx-right font-semibold leading-tight tracking-tight text-3xl sm:text-4xl md:text-5xl mt-2" style="--d:100ms">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#CF4520] via-[#CF4520] to-[#CF4520]/85">
         CONTACT US



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
        Follow your path to paradise





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
        src="{{ asset('images/contact-header.webp') }}"
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



        </p>

        {{-- HEADER (teal) --}}
        <h1 class="fx fx-right font-semibold leading-tight tracking-tight text-5xl lg:text-6xl mt-2" style="--d:360ms">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#CF4520] via-[#CF4520] to-[#CF4520]/80">
         CONTACT US


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
       Follow your path to paradise




        </p>
      </div>
    </div>

    <span aria-hidden="true" class="pointer-events-none absolute inset-0 ring-1 ring-inset ring-white/10"></span>
  </div>

  <style>
    /* Default: no clipping for non-desktop */
    #contact-hero-angled-left-55 .left-clip,
    #contact-hero-angled-left-55 .right-clip {
      clip-path: none;
      -webkit-clip-path: none;
    }

    /* Desktop (lg+): 55° seam */
    @media (min-width: 1024px){
      #contact-hero-angled-left-55 .left-clip{
        clip-path: polygon(0% 0%, 70% 0%, 30% 100%, 0% 100%);
        -webkit-clip-path: polygon(0% 0%, 70% 0%, 30% 100%, 0% 100%);
      }
      #contact-hero-angled-left-55 .right-clip{
        clip-path: polygon(70% 0%, 100% 0%, 100% 100%, 30% 100%);
        -webkit-clip-path: polygon(70% 0%, 100% 0%, 100% 100%, 30% 100%);
      }

    #contact-hero-angled-left-55 .right-clip > img.bg-shift{
        left: 25vw !important;
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
      #contact-hero-angled-left-55 .tablet-stack {
        display: grid !important;
        min-height: 900px !important;
        padding-top: 32px !important;
      }
      #contact-hero-angled-left-55 .desktop-angled,
      #contact-hero-angled-left-55 .right-clip {
        display: none !important;
      }
      #contact-hero-angled-left-55 .left-clip,
      #contact-hero-angled-left-55 .right-clip {
        clip-path: none !important;
        -webkit-clip-path: none !important;
      }
    }
    @media (min-width:1024px) and (max-width:1368px) and (aspect-ratio: 3/4),
           (min-width:1194px) and (max-width:1368px) and (aspect-ratio: 4/3) {
      #contact-hero-angled-left-55 .tablet-stack {
        display: grid !important;
        min-height: 900px !important;
        padding-top: 32px !important;
      }
      #contact-hero-angled-left-55 .desktop-angled,
      #contact-hero-angled-left-55 .right-clip {
        display: none !important;
      }
      #contact-hero-angled-left-55 .left-clip,
      #contact-hero-angled-left-55 .right-clip {
        clip-path: none !important;
        -webkit-clip-path: none !important;
      }
    }

    /* Entrance FX for text */
    #contact-hero-angled-left-55 .fx{
      opacity: .001;
      transform: translateX(-14px);
      will-change: transform, opacity;
      transition:
        opacity 520ms ease,
        transform 640ms cubic-bezier(.22,1,.36,1);
      transition-delay: var(--d, 0ms);
    }
    #contact-hero-angled-left-55.is-in .fx{
      opacity: 1;
      transform: translateX(0);
    }

    /* Slide-in FX for vectors (top + bottom) – from left to right, slower */
    #contact-hero-angled-left-55 .vector-top-fx,
    #contact-hero-angled-left-55 .vector-btm-mobile-fx,
    #contact-hero-angled-left-55 .vector-btm-desktop-fx{
      opacity: 0;
      transform: translateX(-32px);
      will-change: transform, opacity;
      transition:
        opacity 700ms ease,
        transform 950ms cubic-bezier(.16,1,.3,1);
    }

    #contact-hero-angled-left-55.is-in .vector-top-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 260ms;
    }

    #contact-hero-angled-left-55.is-in .vector-btm-mobile-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 320ms;
    }

    #contact-hero-angled-left-55.is-in .vector-btm-desktop-fx{
      opacity: 1;
      transform: translateX(0);
      transition-delay: 320ms;
    }

    /* Extra safety: very small phones (optional tweak if needed) */
    @media (max-width: 400px){
      #contact-hero-angled-left-55 .vector-btm-mobile-fx img{
        width: 6rem;
      }
    }
    @media (max-width: 360px){
      #contact-hero-angled-left-55 .vector-btm-mobile-fx{
        display: none;
      }
    }

    @media (prefers-reduced-motion: reduce){
      #contact-hero-angled-left-55 .fx{
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
      #contact-hero-angled-left-55 img{
        transition: none !important;
        transform: none !important;
      }
      #contact-hero-angled-left-55 .vector-top-fx,
      #contact-hero-angled-left-55 .vector-btm-mobile-fx,
      #contact-hero-angled-left-55 .vector-btm-desktop-fx{
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
    }
  </style>
</section>




{{-- Astoria Plaza — Inquiry (Heading + HubSpot form + Map) --}}
<style>
  /* one-time fade-up that persists */
  @keyframes fadeUp { from {opacity:0;transform:translateY(14px) scale(.98)} to {opacity:1;transform:translateY(0) scale(1)} }
  .reveal{opacity:0;transform:translateY(14px) scale(.98);will-change:opacity,transform}
  .reveal.in{animation:fadeUp .7s cubic-bezier(.22,.8,.25,1) forwards;animation-delay:var(--d,0ms)}
  @media (prefers-reduced-motion: reduce){
    .reveal,.reveal.in{animation:none!important;opacity:1!important;transform:none!important}
  }
</style>

<section id="ab1-contact-inquiry" class="relative w-full bg-gray-50 py-12 md:py-16">
  <div class="max-w-7xl mx-auto px-6">
    {{-- Heading (optional text, left blank for now) --}}
    <div class="reveal mb-6 md:mb-8" style="--d:0ms" data-reveal>
      {{-- You can drop a small title/description here if you want --}}
    </div>

    {{-- Grid: Form (left) + Map (right) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
      {{-- HubSpot form card --}}
      <div class="reveal" style="--d:120ms" data-reveal>
        <div class="rounded-2xl bg-white shadow ring-1 ring-gray-200 p-4 md:p-5">
          {{-- HubSpot target --}}
          <div id="hbspt-form-apz" class="min-h-[320px]"></div>

          {{-- Optional: fallback link if script is blocked --}}
          <noscript>
            <p class="mt-4 text-sm text-gray-600">
              JavaScript is required to load the form. You can reach us at
              <a class="text-[#00a88f] underline" href="mailto:info@astoriapalawan.com">our official email</a>.
            </p>
          </noscript>
        </div>
      </div>

      {{-- Map card – now pointing to Astoria Bohol --}}
      <div class="reveal" style="--d:200ms" data-reveal>
        <div class="rounded-2xl overflow-hidden bg-white shadow ring-1 ring-gray-200">
          <div class="relative w-full pt-[62%]">
            <iframe
              loading="lazy"
              title="Astoria Bohol Location"
              class="absolute inset-0 w-full h-full border-0"
              src="https://maps.google.com/maps?q=Astoria%Bohol=m&z=14&output=embed&iwloc=near">
            </iframe>
          </div>
        </div>
      </div>
    </div>
 
  </div>


</section>



<script>
  // One-time reveal on view (global for any [data-reveal], runs after DOM is ready)
  document.addEventListener('DOMContentLoaded', function(){
    const els = document.querySelectorAll('[data-reveal]');
    if (!els.length) return;

    if (!('IntersectionObserver' in window)) {
      els.forEach(el => el.classList.add('in'));
      return;
    }

    const io = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('in');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });

    els.forEach(el => io.observe(el));
  });
</script>



{{-- Updated HubSpot embed (new portalId + formId + region) --}}
<script charset="utf-8" type="text/javascript" src="//js-na2.hsforms.net/forms/embed/v2.js"></script>
<script>
  hbspt.forms.create({
    portalId: "21911373",
    formId: "eed685a3-d8e9-49dd-a476-bee132f94a9d",
    region: "na2",
    target: "#hbspt-form-apz"
  });
</script>


{{-- ===================== AVLCI & NON-AVLCI BOOKING CHANNELS ===================== --}}
<section id="membership-booking" class="relative bg-[#f5f7fb] py-14 md:py-18 lg:py-20">
  <div class="mx-auto max-w-[1200px] px-4 sm:px-6 lg:px-8">
    <div class="reveal" data-reveal style="--d:260ms">
      {{-- Section Header --}}


      <div class="text-center max-w-3xl mx-auto mb-10 md:mb-12">
        <p
          class="fx fx-right uppercase tracking-[.28em] text-[11px] sm:text-xs font-semibold text-[#63666A]"
          style="--d:40ms"
        >
          RESERVATIONS &amp; MEMBER SERVICES
        </p>
        <h2
          class="fx fx-right mt-2 text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-semibold tracking-tight text-[#13294B]"
          style="--d:110ms"
        >

          Booking Channels for AVLCI &amp; Non-AVLCI Guests
        </h2>
        <div
          class="fx fx-right mt-4 h-1 w-16 mx-auto rounded-full bg-[#63666A]"
          style="--d:180ms"
          aria-hidden="true"
        ></div>
      </div>

      <div class="grid gap-8 lg:grid-cols-1 lg:items-start">
        {{-- ========== LEFT: FOR AVLCI MEMBERS ========== --}}
        <div class="fx fx-right" style="--d:220ms">
          <div class="h-full rounded-3xl bg-white border border-[#13294B]/15 shadow-sm p-6 sm:p-7 lg:p-8">
            <div class="inline-flex items-center gap-2 rounded-full bg-[#13294B]/10 px-3 py-1 text-[11px] sm:text-xs lg:text-sm font-semibold tracking-[.18em] uppercase text-[#63666A] mb-4">

              <i class="fa-solid fa-id-card text-[#13294B]"></i>
              For AVLCI Members
            </div>

            <p class="text-[15px] leading-7 md:text-base md:leading-8 lg:text-lg lg:leading-8 text-[#25282a]/90 mb-4">

              To make a reservation using your Astoria Vacation and Leisure Club, Inc. (AVLCI) membership,
              you may contact their Member Services Department for direct assistance.
            </p>

            <div class="space-y-3 text-[14.5px] leading-7 md:text-base md:leading-7 lg:text-lg lg:leading-8 text-[#25282a]/90">

              <div class="flex items-start gap-3">
                <i class="fa-solid fa-envelope mt-1 text-[#13294B]"></i>
                <div>
                  <p class="font-semibold text-[#63666A]">E-mail</p>
                  <p>
                    <a href="mailto:astoriaplaza@avlci.com" class="hover:underline">astoriaplaza@avlci.com</a><br>
                    <a href="mailto:astoriapalawan@avlci.com" class="hover:underline">astoriapalawan@avlci.com</a>
                  </p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <i class="fa-solid fa-phone mt-1 text-[#13294B]"></i>
                <div>
                  <p class="font-semibold text-[#63666A]">Landline</p>
                  <p>
                    <a href="tel:+63253351111" class="hover:underline">(+63 2) 5335-1111</a>
                    <span class="text-[#25282a]/75"> (press 1 or 2)</span>
                  </p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <i class="fa-brands fa-google mt-1 text-[#13294B]"></i>
                <div>
                  <p class="font-semibold text-[#63666A]">Mobile (Globe)</p>
                  <p>
                    <a href="tel:+639175318574" class="hover:underline">(+63) 917-531-8574</a><br>
                    <a href="tel:+639175459683" class="hover:underline">(+63) 917-545-9683</a>
                  </p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <i class="fa-brands fa-square-whatsapp mt-1 text-[#13294B]"></i>
                <div>
                  <p class="font-semibold text-[#63666A]">Mobile (Smart)</p>
                  <p>
                    <a href="tel:+639088727893" class="hover:underline">(+63) 908-872-7893</a><br>
                    <a href="tel:+639199113961" class="hover:underline">(+63) 919-911-3961</a>
                  </p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <i class="fa-brands fa-viber mt-1 text-[#13294B]"></i>
                <div>
                  <p class="font-semibold text-[#63666A]">Viber</p>
                  <p>
                    <a href="tel:+639199113959" class="hover:underline">(+63) 919-911-3959</a>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- ========== RIGHT: FOR NON-AVLCI MEMBERS ========== --}}
        <div class="fx fx-right" style="--d:260ms">
          <div class="h-full rounded-3xl bg-white border border-[#63666A]/10 shadow-sm p-6 sm:p-7 lg:p-8">
            <div class="inline-flex items-center gap-2 rounded-full bg-[#63666A]/10 px-3 py-1 text-[11px] sm:text-xs lg:text-sm font-semibold tracking-[.18em] uppercase text-[#63666A] mb-4">

              <i class="fa-solid fa-user-check text-[#63666A]"></i>
              For Non-AVLCI Members
            </div>

            <p class="text-[15px] leading-7 md:text-base md:leading-8 lg:text-lg lg:leading-8 text-[#25282a]/90 mb-4">

              To finalize your booking, you may contact our Reservations Team at any of the Astoria properties below:
            </p>

           <div class="space-y-4 text-[14.5px] leading-7 md:text-base md:leading-7 lg:text-lg lg:leading-8 text-[#25282a]/90">

              {{-- Astoria Plaza --}}
              <div class="rounded-2xl border border-gray-200/80 bg-white/80 p-4">
                <p class="font-semibold text-[#13294B] text-sm lg:text-xl ">Astoria Plaza</p>
                <p class="text-[13px] lg:text-xl mt-1 text-[#25282a]/80">
                  15 J. Escriva Drive, Ortigas Business District, Pasig City 1600
                </p>
                <p class="mt-2">
                  <span class="font-semibold text-[#63666A]">Reservations Office:</span>
                  <a href="tel:+639178898277" class="hover:underline"> 0917-889-8277</a>
                </p>
                <p>
                  <span class="font-semibold text-[#63666A]">Email:</span>
                  <a href="mailto:rsvn@astoriaplaza.com" class="hover:underline"> rsvn@astoriaplaza.com</a>
                </p>
              </div>

              {{-- Astoria Greenbelt --}}
              <div class="rounded-2xl border border-gray-200/80 bg-white/80 p-4">
                <p class="font-semibold text-[#13294B] text-sm lg:text-xl">Astoria Greenbelt</p>
                <p class="text-[13px] lg:text-xl mt-1 text-[#25282a]/80">
                  914 Arnaiz Avenue, San Lorenzo, Makati City, Philippines 1223
                </p>
                <p class="mt-2">
                  <span class="font-semibold text-[#63666A]">Reservations Office:</span>
                  <a href="tel:+639199113946" class="hover:underline"> 0919-911-3946</a>
                </p>
                <p>
                  <span class="font-semibold text-[#63666A]">Email:</span>
                  <a href="mailto:rsvn@astoriagreenbelt.com" class="hover:underline"> rsvn@astoriagreenbelt.com</a>
                </p>
              </div>

              {{-- Astoria Plaza --}}
              <div class="rounded-2xl border border-gray-200/80 bg-white/80 p-4">
                <p class="font-semibold text-[#13294B] text-sm lg:text-xl">Astoria Boracay</p>
                <p class="text-[13px] lg:text-xl mt-1 text-[#25282a]/80">
                  Station 1 Boracay Island, Barangay Balabag, Malay, 5608 Aklan
                </p>
                <p class="mt-2">
                  <span class="font-semibold text-[#63666A]">Reservations Office:</span>
                  <a href="tel:+639088727922" class="hover:underline"> 0908-872-7922</a>
                </p>
                <p>
                  <span class="font-semibold text-[#63666A]">Email:</span>
                  <a href="mailto:reservations@astoriaboracay.com" class="hover:underline">
                    reservations@astoriaboracay.com
                  </a>
                </p>
              </div>

              {{-- Astoria Current --}}
              <div class="rounded-2xl border border-gray-200/80 bg-white/80 p-4">
                <p class="font-semibold text-[#13294B] text-sm lg:text-xl">Astoria Current</p>
                <p class="text-[13px] lg:text-xl mt-1 text-[#25282a]/80">
                  Sitio Mangayad, Brgy. Manoc Manoc, Station 3, Boracay Island, Malay, Aklan 5608, Philippines
                </p>
                <p class="mt-2">
                  <span class="font-semibold text-[#63666A]">Reservations:</span>
                  <a href="tel:+63286871111" class="hover:underline"> (02) 8687 1111 local 8731</a>,
                  <a href="tel:+639989681265" class="hover:underline"> 0998-968-1265</a>
                </p>
                <p>
                  <span class="font-semibold text-[#63666A]">Front Office:</span>
                  <a href="tel:+639088741702" class="hover:underline"> 0908-874-1702</a>
                </p>
                <p>
                  <span class="font-semibold text-[#63666A]">Email:</span>
                  <a href="mailto:rsvn@astoriacurrent.com" class="hover:underline">
                    rsvn@astoriacurrent.com
                  </a>
                </p>
              </div>

              {{-- Astoria Palawan --}}
              <div class="rounded-2xl border border-gray-200/80 bg-white/80 p-4">
                <p class="font-semibold text-[#13294B] text-sm lg:text-xl">Astoria Palawan</p>
                <p class="text-[13px]  lg:text-xl mt-1 text-[#25282a]/80">
                  Kilometer 62, North National Hwy, Brgy. San Rafael, Puerto Princesa City, Palawan 5300
                </p>
                <p class="mt-2">
                  <span class="font-semibold text-[#63666A]">Reservations Office:</span>
                  <a href="tel:+639989613419" class="hover:underline"> 0998-961-3419</a>
                </p>
                <p>
                  <span class="font-semibold text-[#63666A]">Sales Office:</span>
                  <a href="tel:+639175854435" class="hover:underline"> 0917-585-4435</a> |
                  <a href="tel:+639985976831" class="hover:underline"> 0998-597-6831</a> |
                  <a href="tel:+639989613422" class="hover:underline"> 0998-961-3422</a>
                </p>
                <p>
                  <span class="font-semibold text-[#63666A]">Email:</span>
                  <a href="mailto:reservations@astoriapalawan.com" class="hover:underline">
                    reservations@astoriapalawan.com
                  </a>
                </p>
              </div>

              {{-- Astoria Bohol --}}
              <div class="rounded-2xl border border-gray-200/80 bg-white/80 p-4">
                <p class="font-semibold text-[#13294B] text-sm lg:text-xl">Astoria Bohol</p>
                <p class="text-[13px] lg:text-xl mt-1 text-[#25282a]/80">
                  Barangay Taguihon, Baclayon, Bohol 6301 Philippines
                </p>
                <p class="mt-2">
                  <span class="font-semibold text-[#63666A]">Reservations Office:</span>
                  <a href="tel:+63384114695" class="hover:underline"> (+63 38) 411-4695</a> and
                  <a href="tel:+639178898275" class="hover:underline"> (+63) 917-889-8275</a>
                </p>
                <p>
                  <span class="font-semibold text-[#63666A]">Email:</span>
                  <a href="mailto:rsvn@astoriabohol.com" class="hover:underline">
                    rsvn@astoriabohol.com
                  </a>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div> {{-- /grid --}}
    </div> {{-- /reveal --}}
  </div>
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
