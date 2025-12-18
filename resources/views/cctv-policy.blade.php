{{-- resources/views/cctv-policy.blade.php --}}
@extends('layouts.app')

@section('title', 'CCTV Privacy Policy | Astoria Boracay')

@section('content')
@php
  use Illuminate\Support\Carbon;
  $tz = 'Asia/Manila';
  $updatedAt = $updatedAt ?? null;
  try { $updatedDisplay = $updatedAt ? Carbon::parse($updatedAt)->timezone($tz)->format('F j, Y') : null; }
  catch (\Throwable $e) { $updatedDisplay = null; }
@endphp

<a id="top" class="sr-only" aria-hidden="true"></a>

{{-- Page-scoped styles (fallbacks + anchor offset + TOC tweaks + PRINT FIXES) --}}
<style>
  /* Smooth scroll fallback */
  :root { scroll-behavior: smooth; }

  /* Content heading fallbacks */
  .content-rich h2{font-weight:700;margin:2rem 0 .75rem;color:#111827;line-height:1.25;}
  .content-rich h3{font-weight:700;margin:1.5rem 0 .5rem;color:#111827;line-height:1.3;}
  .content-rich p{margin:1rem 0;color:#374151;}
  .content-rich ul{list-style:disc;margin:1rem 0;padding-left:1.25rem;}
  .content-rich ol{list-style:decimal;margin:1rem 0;padding-left:1.25rem;}
  .content-rich a{color:#CF4520;text-decoration:underline;}
  @media (min-width: 1024px){ .content-rich h2{font-size:1.875rem} .content-rich h3{font-size:1.25rem} }
  @media (max-width: 1023.98px){ .content-rich h2{font-size:1.5rem} .content-rich h3{font-size:1.125rem} }

  /* Ensure anchors stop below sticky header/nav */
  .content-rich h2[id], .content-rich h3[id] { scroll-margin-top: 104px; }
  #contact-dpo { scroll-margin-top: 104px; }
  @media (min-width: 1024px){
    .content-rich h2[id], .content-rich h3[id] { scroll-margin-top: 120px; }
    #contact-dpo { scroll-margin-top: 120px; }
  }

  /* TOC visual stability */
  .toc-link { will-change: color, background-color; }

  /* Screen/print helpers */
  .print-only { display: none; }

  /* ---------------- PRINT FIXES: no blank trailing pages, simple single-column flow ---------------- */
  @page { size: A4; margin: 10mm; }
  @media print {
    html, body { height: auto !important; width: auto !important; overflow: visible !important; margin: 0 !important; padding: 0 !important; }

    /* Hide app chrome */
    header, nav, footer, .site-header, .app-header, .navbar, .site-nav, .site-logo, .site-footer, .app-footer { display: none !important; }

    /* Hide decorative/interactive stuff */
    .decor-blob, .hero-bg, .hero-actions, .toc-desktop, details, .backdrop-blur { display: none !important; }

    /* Reset layout engines that often trigger blank pages in print */
    .grid, .flex { display: block !important; }
    .sticky, .fixed { position: static !important; top: auto !important; }
    [class*="min-h-"], [class*="min-h\\["], [class*="h-screen"], [class*="min-h-screen"] { min-height: 0 !important; height: auto !important; }
    [class*="px-"], [class*="py-"], [class*="pt-"], [class*="pb-"] { padding-left: 0 !important; padding-right: 0 !important; }
    .max-w-7xl, .mx-auto { max-width: 100% !important; width: 100% !important; margin-left: 0 !important; margin-right: 0 !important; }
    * {
      transform: none !important;
      filter: none !important;
      background: transparent !important;
      box-shadow: none !important;
      /* clear stray break rules */
      break-before: auto !important;
      break-after: auto !important;
      break-inside: auto !important;
      page-break-before: auto !important;
      page-break-after: auto !important;
      page-break-inside: auto !important;
    }

    /* Flatten hero to simple heading + centered seal */
    .hero-section, .hero-inner, .hero-media { padding: 0 !important; margin: 0 0 8pt 0 !important; min-height: auto !important; }
    .hero-title, .hero-legal, .hero-updated { color: #111 !important; }
    .hero-title { margin: 0 0 6pt 0 !important; }

    .hero-seal {
      display: block !important;
      margin: 8pt auto 10pt auto !important;
      max-height: 80mm !important;
      max-width: 170mm !important;
      width: auto !important;
      height: auto !important;
      object-fit: contain !important;
    }

    /* Single-column content + reduced gaps to avoid extra pages */
    .content-section, .dpo-section { padding: 0 !important; margin: 0 0 8pt 0 !important; }
    .content-section .grid, .dpo-section .grid { gap: 0 !important; }

    /* Keep text crisp and reduce vertical rhythm */
    body, .content-rich, .content-rich * { color: #111 !important; }
    a { color: #000 !important; text-decoration: underline !important; }
    .content-rich p { margin: 6pt 0 !important; }
    .content-rich h2 { margin: 14pt 0 8pt !important; page-break-after: avoid; }
    .content-rich h3 { margin: 10pt 0 6pt !important; page-break-after: avoid; }

    /* Avoid images/tables slicing */
    img, figure, table { page-break-inside: avoid !important; break-inside: avoid !important; }

    /* Make sure the last printed block eats leftover whitespace */
    .print-tail-guard { height: 1px !important; margin: 0 !important; padding: 0 !important; line-height: 0 !important; }
  }
</style>

{{-- WRAPPER prevents any accidental horizontal overflow from the hero --}}
<div class="overflow-x-hidden" x-data="{ zoomOpen:false }" @keydown.window.escape="zoomOpen = false">

  {{-- HERO / HEADER — CCTV legal panel with zoomable seal --}}
  <section
    class="hero-section relative isolate overflow-hidden bg-[#3F2021]"
    aria-labelledby="legal-title"
  >
    {{-- Gradient background based on #63666A --}}
    <div class="hero-bg absolute inset-0 bg--[#63666A]"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 pt-24 pb-14 md:pt-28 md:pb-16">
      <div class="grid lg:grid-cols-12 gap-10 lg:gap-12 items-center">
        {{-- LEFT: main text --}}
        <div class="lg:col-span-7 flex flex-col justify-center min-w-0">
          <div class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/50 px-3 py-1 mb-4 backdrop-blur">
            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white text-[#CF4520] text-xs font-bold">
              §
            </span>
            <span class="text-[0.70rem] md:text-xs font-semibold tracking-[.26em] text-white/90 uppercase">
              Privacy &amp; Guest Data
            </span>
          </div>

          <h1
            id="legal-title"
            class="hero-title text-3xl sm:text-4xl md:text-5xl lg:text-[2.9rem] font-extrabold tracking-tight text-white"
          >
            CCTV Privacy Policy
          </h1>

          {!! $updatedDisplay
            ? '<p class="hero-updated mt-3 text-sm md:text-[0.95rem] text-white/90">Last updated: '
              . e($updatedDisplay) . ' (' . e($tz) . ')</p>'
            : ''
          !!}

          <p class="mt-4 max-w-xl text-sm md:text-base text-emerald-50/95 leading-relaxed">
            This CCTV Policy describes the CCTV system and explains the safeguards we have put in place
            to protect personal data of any individuals who may be captured by the CCTV system cameras.
          </p>

          <div class="hero-actions mt-7 flex flex-wrap items-center gap-3">
            {{-- COPY CTA (primary, #CF4520) --}}
            <button
              type="button"
              data-copy
              class="inline-flex items-center gap-2 rounded-full border border-transparent bg-[#CF4520] px-4 py-2 text-xs md:text-sm font-semibold text-white shadow-sm hover:bg-[#CF4520] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-[#CF4520] focus:ring-[#CF4520]"
              title="Copy page link"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                <path d="M10 6a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-2v-2h2V6h-6v2h-2V6Zm-6 6a2 2 0 0 1 2-2h2v2H6v6h6v-2h2v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6Zm5-1h6v2H9v-2Zm0-3h3v2H9V8Zm0 6h3v2H9v-2Z"/>
              </svg>
              <span data-copy-default>Copy link</span>
              <span data-copy-done class="hidden">Copied!</span>
            </button>

            {{-- CONTACT CTA (outline using #CF4520) --}}
            <a
              href="#contact-dpo"
              class="inline-flex items-center gap-2 rounded-full border border-[#CF4520] bg-white/10 px-4 py-2 text-xs md:text-sm font-semibold text-white hover:bg-[#CF4520] hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-[#CF4520] focus:ring-[#CF4520]"
              title="Contact our Data Privacy Officer"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 6H4a2 2 0 0 0-2 2v.2l10 5.8 10-5.8V8a2 2 0 0 0-2-2Zm0 4.1-9.4 5.5a1 1 0 0 1-1.2 0L4 10.1V18a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7.9Z"/>
              </svg>
              Contact DPO
            </a>
          </div>
        </div>

        {{-- RIGHT: zoomable seal card --}}
        <div class="hero-media lg:col-span-5">
          <div class="h-full w-full flex items-center justify-center">
            <figure class="relative w-full max-w-lg">
              <button
                type="button"
                @click="zoomOpen = true"
                class="group block w-full text-left focus:outline-none"
              >
                <div class="rounded-[2rem] border border-white/35 bg-black/10 backdrop-blur-md px-6 py-8 md:px-7 md:py-9 shadow-[0_22px_70px_rgba(0,0,0,0.7)]">
                  <div class="flex flex-col items-center text-center gap-4">
                    <img
                      src="{{ asset('images/apw-corseal.webp') }}"
                      alt="National Privacy Commission DPO/DPS Registered Seal"
                      class="hero-seal block w-full h-auto max-h-[360px] md:max-h-[420px] object-contain drop-shadow-md transition-transform duration-200 group-hover:scale-[1.03] cursor-zoom-in"
                      loading="eager"
                    >
                    <p class="text-[0.8rem] text-emerald-50/95 leading-relaxed">
                      Tap or click to view a larger version of the National Privacy Commission
                      registration seal for Astoria Hotels and Resorts.
                    </p>
                  </div>
                </div>
              </button>
            </figure>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ZOOM MODAL --}}
  <div
    x-show="zoomOpen"
    class="fixed inset-0 z-[999] flex items-center justify-center bg-black/80 px-4"
    style="display: none;"
    @click="zoomOpen = false"
  >
    <div
      class="relative max-w-4xl w-full"
      @click.stop
    >
      {{-- Close button --}}
      <button
        type="button"
        @click="zoomOpen = false"
        class="absolute -top-3 -right-3 z-10 inline-flex h-9 w-9 items-center justify-center rounded-full bg-black/80 text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-white"
      >
        <span class="sr-only">Close</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
          <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </button>

      <div class="overflow-hidden rounded-2xl bg-black">
        <img
          src="{{ asset('images/apw-corseal.webp') }}"
          alt="National Privacy Commission DPO/DPS Registered Seal - enlarged"
          class="block w-full h-auto max-h-[80vh] object-contain"
        >
      </div>
    </div>
  </div>

  {{-- CONTENT + TOC --}}
  <section class="content-section relative bg-white" x-data="policyPage()">
    <div class="max-w-7xl mx-auto px-6 py-12 md:py-16">
      <div class="grid lg:grid-cols-12 gap-10">

        {{-- TOC (desktop) + Mobile drawer --}}
        <aside class="lg:col-span-4 order-first lg:order-none">
          <details class="lg:hidden mb-4 rounded-xl border border-gray-200 bg-white">
            <summary class="list-none px-4 py-3 cursor-pointer flex items-center justify-between">
              <span class="text-xl font-semibold text-gray-800">Jump to section</span>
              <span class="text-gray-500">▾</span>
            </summary>
            <nav class="px-4 pb-4">
              <ul class="space-y-2 leading-6" x-ref="tocMobile"></ul>
            </nav>
          </details>

          <nav class="toc-desktop sticky top-24 hidden lg:block rounded-2xl border border-gray-200 bg-gray-50/70 backdrop-blur p-4">
            <p class="text-xl font-semibold uppercase tracking-wide text-gray-500 mb-3">On this page</p>
            <ul class="space-y-1.5 leading-6" x-ref="tocDesktop"></ul>
          </nav>
        </aside>

        {{-- ARTICLE (your content) --}}
        <article class="lg:col-span-8 content-rich" x-ref="article">
@verbatim
Millennium Properties and Brokerage Incorporated (MPBI) has installed and operates a Closed-Circuit Television (CCTV) system at its premises which are monitored and recorded on a 24-hour basis.


<br><br>
This Policy describes and explains the safeguards we have put in place to protect personal data of any individuals who may be captured by the CCTV.
<br><br>
The collection, use, disclosure, storage and other processing of personal data by the CCTV system is for purposes of protecting of the security of our Properties, facilities, networks, systems and providing and managing a safe environment for our customers, visitors, personnel and other individuals, including without limitation:
<br><br>
• deterring crime and anti-social behavior

• assisting in the detection, investigation, and prosecution of offenses
<br>
• for the purposes where recordings may resolve any facts of the dispute
<br>
• for the taking and defense of litigation.

<h2 id="cctv-system-description">CCTV System Description</h2>
The cameras operate full time (24 hours a day, 7 days a week).
<br><br>
There are three (3) types of CCTV cameras installed in the premises of the properties: fixed, PTZ and fish-eye. Fixed cameras do not allow operators to zoom in on or otherwise follow individuals.
<br><br>
Another type of cameras is installed inside the vans and cars which are called Dash Cam which is used for recording videos of accidents to prove fault. Dash Cam also records voices but its sole purpose is to support any proven accidents.
<br><br>
The cameras record movement detected in the area under surveillance as well as the time, date and location of the footage.
<br><br>
The cameras currently installed in the premises do not conduct sound recording however considerations to this policy will apply to new cameras with built-in sound recording that will be installed in the future.
<h2 id="location-of-cameras">Location of Cameras</h2>
The placement or siting of CCTV cameras shall not unreasonably intrude on the privacy of individuals.
<br><br>
The CCTV system provides coverage over the following areas:
<br><br>
• entrances and exists to the properties including emergency exits
<br>
• lobbies, waiting areas, including loading and unloading bays
<br>
• corridors and hallways
<br>
• elevators and stairs within the properties
<br>
• dining areas, gyms and other facilities
<br>
• warehouse or store houses
<br>
• front desk, cashiering and other offices
<br>
• other premises or areas as required by the business
<br><br>
Our CCTV systems do not infringe on restricted areas (i.e. toilets/restrooms or other similar places).
<br><br>
Upon installation, all equipment is tested to ensure that only the designated areas are monitored.

<h2 id="third-parties">Third-party Links / Sites</h2>
We are not responsible for the privacy policies and procedures of third-party sites that may link to our websites, or we may link to as part of past or present business relationships or initiatives. Please review the privacy policies of any linked sites you visit before using or providing information to any of those sites.

<h2 id="notices">Notices</h2>
The public shall be notified of CCTV surveillance operations through signage installed near the areas monitored, digital signages and websites.
<br><br>
CCTV Privacy Policy are posted in our websites for public to view.

<h2 id="authorized-personnel">Authorized Personnel</h2>
Footage captured and recorded by the CCTV is accessible only by authorized personnel of the Security Department.

<h2 id="video-quality">Video Quality</h2>
The images and/or footage produced by the equipment will as far as possible be of a quality that is effective for the purpose(s) for which they are intended.


<h2 id="security-measures">Security Measures</h2>
The CCTV System is used as a complement to other security measures implemented to safeguard the security of our facilities, networks and systems and the safety of our customers, visitors, personnel and other individuals. The CCTV System enhances other access control and physical intrusion prevention systems within our premises.
<br><br>
Access to the images recorded by our CCTV System is restricted and carefully controlled.
<br><br>
The security of the IT systems containing the CCTV footage is safeguarded through technical, organizational, and physical means.
<br><br>
Footage recorded by our CCTV cameras are encrypted and otherwise stored in a secure manner to protect their confidentiality, integrity and availability. The digital perimeter of the IT infrastructure is protected under our Privacy Policy and IT Security Policy.
<br><br>
The CCTV monitoring and storing equipment is kept in a segregated, secure area to which only authorized personnel with security clearance are granted access.
<br><br>
All persons with access to the CCTV System are under binding/contractual confidentiality commitments.
<br><br>
Security protocols have been put in place to ensure that only authorized personnel are allowed to view, monitor and otherwise process CCTV footage.
<br><br>
Upon installation, all equipment is tested to ensure that suitable quality pictures are available in live and play back mode. All CCTV equipment is maintained regularly.

<h2 id="access-requests">Procedure for Access Requests</h2>
Data subject who request access to CCTV footage must appear in person and fill out a Review of CCTV Recording Form to be submitted as a formal request. Aside from personal details of the data subject requesting for review, a copy of Identification and sufficient details to identify the section of footage will be collected to complete the request.
<br><br>
Upon receipt of the Form, it is referred to the Security Department and the Data Privacy Officer who will determine whether disclosure is appropriate. After the final evaluation, recommendations will be discussed with upper management for final approval.
<br><br>
Our response in allowing access to CCTV footage will depend on other considerations on the ease of access to the footage and the need to protect another people’s privacy.

<h2 id="disclosure-transfer">Disclosure/Transfer</h2>
Access granted to CCTV footage is tiered: either by viewing or providing a copy, the latter option being allowed only when proportional to the purpose of the request. At all times, the footage to be disclosed, either by viewing or providing a copy, are only those that are necessary and not excessive to the purpose for which they are being disclosed.
<br><br>
CCTV footage may be disclosed in the following instances:
<br><br>
• Law enforcement and criminal investigations. On requests for CCTV footage to be disclosed in relation to a criminal investigation, the company shall require the law enforcement officer or the requesting party to provide sufficient proof as to the occurrence of a crime and the investigation thereof as well as proof of authority of the law enforcement officer before release of the CCTV footage.
<br>
• Court Order. Requests for disclosure and use of CCTV footage and images by virtue of a lawful order of a court of competent authority is allowed, taking into consideration the pertinent rules on issuance of subpoena.
<br>
• Administrative investigations. Use of CCTV footage for purposes of an administrative investigation may be allowed. The requesting party must provide sufficient proof of the investigation being conducted or the pending complaint before an administrative body.
<br>
• Other third-party requests. Third-party access requests for CCTV footage and images is evaluated on a case-to-case basis with due regard to the rights to privacy of individuals, and applicable provisions of relevant laws.
<br><br>
We shall act on the requests within fifteen (15) working days after the approval of the request and submission of required additional supporting documents if ever is requested by the Security Department. We may charge a reasonable fee for providing a copy of the CCTV footage to cover administrative costs as may be applicable.

<h2 id="retention">Retention Period</h2>
CCTV recordings are generally maintained for at least thirty (30) calendar days and are thereafter overwritten making recovery impossible, except that they may be kept for a longer duration in certain instances including:
<br><br>
• where there is legal basis
<br>
• where we are legally required to do so, or
<br>
• where the footage otherwise has investigative value (such as where a security incident occurs) and the recordings are stored as necessary for the duration of the investigation, the prosecution of the incident or the exercise, enforcement or defense of any legal claims.
<br><br>
The foregoing list is not exhaustive and there may be other circumstances where the CCTV is retained for longer than thirty (30) calendar days as is justified under the circumstances.
<br><br>
Where an incident or suspected incident has been identified under the appropriate procedures mentioned in this Policy, the pertinent portion of the CCTV footage is to be retained for that incident.

<h2 id="regular-review">Regular Review</h2>
MPBI undertakes yearly periodic reviews of the CCTV System and the associated internal policies, procedures, protocols and processes. These reviews will be used to assess the continued need for the CCTV System, as well as the adequacy, necessity and proportionality of the CCTV System.


<br><br>
Last updated on 17 February 2025

@endverbatim
        </article>
      </div>
    </div>
  </section>

  {{-- DPO CONTACT SECTION (before footer) --}}
  <section id="contact-dpo" class="dpo-section bg-emerald-50/60 border-t border-emerald-100">
    <div class="max-w-7xl mx-auto px-6 py-10 md:py-14">
      <div class="rounded-2xl bg-white shadow-sm ring-1 ring-emerald-100 p-6 md:p-8">
        <div class="grid md:grid-cols-3 gap-6">
          <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Contacting Us</h2>
            <p class="mt-1 text-sm text-gray-600">If you have any questions about this Privacy Statement, please contact us by email.</p>
          </div>
          <div class="md:col-span-2">
            {{-- Visible ONLY in print: DPO portrait --}}
            <div class="print-only mb-4">
              <img
                src="{{ $dpoImage ?? asset('images/apw-corseal.webp') }}"
                alt="Data Privacy Officer"
                style="width:120px;height:120px;object-fit:cover;border-radius:9999px;"
                loading="eager"
              >
            </div>
            <address class="not-italic text-gray-800 leading-7">
              <div class="font-semibold">Data Privacy Officer</div>
              <div>Astoria Hotels and Resorts</div>
              <div>#15 J. Escriva Drive, Ortigas Business District</div>
              <div>Pasig City, Philippines</div>
              <div class="mt-2">
                <a href="mailto:dpo@astoria.com.ph" class="inline-flex items-center gap-2 text-[#CF4520] font-semibold hover:underline">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M20 6H4a2 2 0 0 0-2 2v.2l10 5.8 10-5.8V8a2 2 0 0 0-2-2Zm0 4.1-9.4 5.5a1 1 0 0 1-1.2 0L4 10.1V18a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7.9Z"/></svg>
                  dpo@astoria.com.ph
                </a>
              </div>
            </address>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- tiny guard to eat any leftover whitespace so printer doesn't add a blank final sheet --}}
  <div class="print-tail-guard"></div>

</div> {{-- /overflow-x-hidden wrapper --}}

{{-- Scroll-spy + TOC builder (blink-free highlight) --}}
<script>
function policyPage() {
  return {
    activeId: null,
    init() {
      const article = this.$refs.article;
      if (!article) return;
      const heads = [...article.querySelectorAll('h2[id], h3[id]')];

      const makeItem = (h) => {
        const a = document.createElement('a');
        a.href = '#' + h.id;
        a.textContent = h.textContent;
        a.className = 'toc-link block text-base font-semibold text-gray-700/90 hover:text-[#CF4520] rounded-md px-2 py-1 transition-colors';
        a.setAttribute('data-target', h.id);
        a.setAttribute('aria-current', 'false');
        return a;
      };
      const fill = (ul) => {
        if (!ul) return;
        ul.innerHTML = '';
        heads.forEach(h => {
          const li = document.createElement('li');
          li.appendChild(makeItem(h));
          ul.appendChild(li);
        });
      };
      fill(this.$refs.tocDesktop);
      fill(this.$refs.tocMobile);

      [this.$refs.tocDesktop, this.$refs.tocMobile].forEach(ul => {
        if (!ul) return;
        ul.addEventListener('click', (e) => {
          const a = e.target.closest('a[href^="#"]');
          if (!a) return;
          const id = a.getAttribute('href').slice(1);
          const target = document.getElementById(id);
          if (target) {
            e.preventDefault();
            if (history.pushState) { history.pushState(null, '', '#' + id); }
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            this.activeId = id; this.highlight();
            a.focus({ preventScroll: true });
          }
        }, { passive: false });
      });

      heads.forEach(h => {
        h.classList.add('scroll-mt-28');
        const link = document.createElement('a');
        link.href = '#' + h.id;
        link.className = 'no-underline align-middle ml-2 opacity-0 group-hover:opacity-100 transition';
        link.setAttribute('aria-label', 'Anchor link to ' + h.textContent);
        link.innerHTML = '¶';
        const wrap = document.createElement('span');
        wrap.className = 'group';
        wrap.append(...h.childNodes);
        h.textContent = '';
        h.appendChild(wrap);
        h.appendChild(link);
      });

      const io = new IntersectionObserver((entries) => {
        entries.forEach(e => {
          if (e.isIntersecting) {
            this.activeId = e.target.id;
            this.highlight();
          }
        });
      }, { rootMargin: '0px 0px -70% 0px', threshold: [0, 1] });

      heads.forEach(h => io.observe(h));
      this.highlight();
    },
    highlight() {
      const all = document.querySelectorAll('[data-target]');
      all.forEach(a => {
        const on = (a.getAttribute('data-target') === this.activeId);
        a.classList.toggle('text-emerald-700', on);
        a.classList.toggle('bg-emerald-50', on);
        a.setAttribute('aria-current', on ? 'true' : 'false');
      });
    }
  }
}
</script>

<script>
  // Copy with "Copied!"
  document.addEventListener('DOMContentLoaded', () => {
    const showCopied = (btn) => {
      const def = btn.querySelector('[data-copy-default]');
      const done = btn.querySelector('[data-copy-done]');
      if (!def || !done) return;
      def.classList.add('hidden');
      done.classList.remove('hidden');
      setTimeout(() => {
        done.classList.add('hidden');
        def.classList.remove('hidden');
      }, 1600);
    };

    const fallbackCopy = (text, btn) => {
      const t = document.createElement('textarea');
      t.value = text;
      t.setAttribute('readonly', '');
      t.style.position = 'absolute';
      t.style.left = '-9999px';
      document.body.appendChild(t);
      t.select();
      try { document.execCommand('copy'); } catch(e) {}
      document.body.removeChild(t);
      showCopied(btn);
    };

    document.querySelectorAll('[data-copy]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const url = window.location.href;
        if (navigator.clipboard?.writeText) {
          navigator.clipboard.writeText(url).then(() => showCopied(btn)).catch(() => fallbackCopy(url, btn));
        } else {
          fallbackCopy(url, btn);
        }
      });
    });
  });
</script>

@endsection
