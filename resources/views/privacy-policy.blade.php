{{-- resources/views/privacy-policy.blade.php --}}
@extends('layouts.app')

@section('title', 'Privacy Policy | Astoria Bohol')

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

{{-- HERO / HEADER — Legal panel with zoomable seal --}}
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
          Privacy Policy
        </h1>

        {!! $updatedDisplay
          ? '<p class="hero-updated mt-3 text-sm md:text-[0.95rem] text-white/90">Last updated: '
            . e($updatedDisplay) . ' (' . e($tz) . ')</p>'
          : ''
        !!}

        <p class="mt-4 max-w-xl text-sm md:text-base text-emerald-50/95 leading-relaxed">
          Learn how Astoria Bohol and Astoria Hotels and Resorts handle personal data collected
          through our resorts, membership touchpoints, and online services.
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

        {{-- Quick facts row --}}
        <dl class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4 text-[0.78rem] text-emerald-50/95">
          <div class="border border-white/40 rounded-xl px-3 py-3 bg-white/10">
            <dt class="font-semibold text-white">Jurisdiction</dt>
            <dd class="mt-1 leading-relaxed">Philippines, Data Privacy Act of 2012</dd>
          </div>
          <div class="border border-white/40 rounded-xl px-3 py-3 bg-white/10">
            <dt class="font-semibold text-white">Applies to</dt>
            <dd class="mt-1 leading-relaxed">Guests, members, website &amp; app users</dd>
          </div>
          <div class="border border-white/40 rounded-xl px-3 py-3 bg-white/10">
            <dt class="font-semibold text-white">Scope</dt>
            <dd class="mt-1 leading-relaxed">Online services &amp; on-property interactions</dd>
          </div>
        </dl>
      </div>

      {{-- RIGHT: bigger zoomable seal card --}}
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
The Millennium Properties and Brokerage Incorporated (MPBI) doing the names and styles of Astoria Bohol values your interest as our guest and recognizes that privacy is important to you. We want you to be familiar with how we collect, use and disclose data.
<br><br>
This Privacy Statement describes the privacy practices of AHR for data that we collect:
<br><br>
• Through our company websites, software, mobile applications, social media, call center hotlines, and emails made available to use through computers and mobile devices (collectively, Online Services).
<br>
• When you visit or interact with us at our hotels and resorts, events, exhibits, sales venues, dining and banquet functions, and fitness and recreation facilities as a guest (collectively, Offline Services).
<br>
• Via personal data from other sources, such as public databases, joint marketing partners, and other third parties.

<h2 id="collection">Collection of Personal Data</h2>
<!-- Paste content here -->
The personal data we collect is information that identifies you as the Data Subject, and throughout your guest journey, we collect Personal Data in accordance with the law, such as:
<br><br>
Personal Information – refers to any information or set of information in any form that would directly identify you. These may include your name, contact number, address, and email address, among others.
<br><br>
Sensitive Personal Information – refers to personal information which may include your nationality, marital status, age, gender, education, passport or other government identification, license number, credit card and debit card number, among others.
<br><br>
Privileged Information – refers to data which is considered as privileged communication under the law. This may include any information between you and your doctor, your lawyer, or your spouse.
<br><br>
In more limited circumstances, we may also collect:
<br><br>
• Members preferences, enquiries, comments and any other personalized data such as interests, activities, hobbies, food and beverage choices, services and amenities of which you advise us or which we learn about during your visit
<br>
• Data about family members and companions, such as names and ages of children
<br>
• Electronic data such as photos or digital images, biometrics, IP address and Device ID, Geolocation information, Internet click activities in our website
<br>
• Photo, video, and audio data via:<br>
(a) security cameras located in public areas, such as hallways and lobbies of our properties and<br>
(b) your mobile phone or camera submitted by you on our social media pages and email<br>

<h2 id="other-data">How We Collect Other Data</h2>
• BROWSING – We collect general information about you or your activities through which you cannot be identified. When you use the Internet, your web browser or client software may transmit certain kinds of information to the servers that host the websites you visit. The information may include the IP address or unique numbers assigned to your server or Internet connection, the capabilities and features of your computer, your geographic location, and your movement within and interaction with the website, including what pages you look at and what information you download. When you visit AHR’s websites, its servers collect such general information. AHR uses this general information to generate aggregate statistics about its website’s visitors. In situations where it is possible to do so, general information may be linked to personal information.
<br><br>
• COOKIES – AHR’s website uses cookie technology. Cookies identify pieces of information transferred to your computer hard drive by our website. Cookies help identify you when you access its website. Cookies do not allow AHR to access any personal information about you, but they do allow it to understand your use of our websites better so that it may help AHR improve its website services. AHR’s website uses cookies to provide a unique identifier to your computer so that it can generate statistics about the usage of its websites, such as the percentage of repeat visitors and other general statistics. The unique identifiers are not matched with any personal information. Cookies do not store any personal information about you. You can configure your browser to allow you to browse the Internet and AHR’s website without cookies or to notify you when each cookie is offered so that you can accept or reject each cookie. Please be informed that you may not be able to use some services on our website if you prevent your browser from accepting cookies.
<br><br>
• PHISHING AND MALWARE –  AHR’s website uses cookie technology. Cookies identify pieces of information transferred to your computer hard drive by our website. Cookies help identify you when you access its website. Cookies do not allow AHR to access any personal information about you, but they do allow it to understand your use of our websites better so that it may help AHR improve its website services. AHR’s website uses cookies to provide a unique identifier to your computer so that it can generate statistics about the usage of its websites, such as the percentage of repeat visitors and other general statistics. The unique identifiers are not matched with any personal information. Cookies do not store any personal information about you. You can configure your browser to allow you to browse the Internet and AHR’s website without cookies or to notify you when each cookie is offered so that you can accept or reject each cookie. Please be informed that you may not be able to use some services on our website if you prevent your browser from accepting cookies.
<br><br>
• GOOGLE ANALYTICS – We use Google Analytics to help us get a better understanding of how visitors use our website and to facilitate interest-based advertising associated with your Google Account and other devices you use. The information generated by the Google Analytics cookie about your use of our website is transmitted to and stored by Google.
<br><br>
• MOBILE DEVICES – When you use or access our Services from a mobile device, we may collect information such as your unique device ID and your location. If you download and use an app, we and our service providers may track and collect app usage data, such as the date and time the App on your device accesses our servers and what information and files have been downloaded to the App based on your device number, as well as any other personal information specified by the app in its terms or notices.

<h2 id="third-parties">Third-party Links / Sites</h2>
We are not responsible for the privacy policies and procedures of third-party sites that may link to our websites, or we may link to as part of past or present business relationships or initiatives. Please review the privacy policies of any linked sites you visit before using or providing information to any of those sites.

<h2 id="use-of-info">How We Use Your Information</h2>
We may use the collected information when we enter into a contract with the member for, but not limited to:
<br><br>
• Marketing purposes to notify you of special promotions
<br>
• Facilitating social sharing functionalities
<br>
• Conducting offers and events
<br>
• Enabling it to contact you for confirmation or customer service questions, or after you sign up for or participate in certain activities
<br>
• Responding to inquiries, complaints, and other communications
<br>
• Profiling demographics of the website’s visitors
<br><br>
We may also use this information to understand your needs and provide you with better services, particularly to improve our products and services, conduct market research, create surveys, and organize special promotions and events.
<br><br>
We may contact you by email, phone, or chat via social media, and respond to your inquiries or complaints. We may also use the information to customize our website according to your interests. We may also use it to comply with legal processes under the applicable laws, respond to requests from public and government authorities, and enforce our terms and conditions.

<h2 id="disclosure">Disclosure of Collected Information</h2>
• Affiliates and Resorts Partners: We may share Personal Information with our affiliated resort owners and operators in order to provide you with services, facilitate a booking you requested, to respond to your inquiry for further information about accommodation, tours, transfers, etc.
<br>
• Service Providers and Vendors: We may disclose your personal information to service providers and vendors we retain in connection with our business such as: travel services companies, property owners’ associations, data analysis, payment processing, information technology and related infrastructure provision, customer service, email delivery, credit card processing, tax and financial advisers, legal advisers, accountants, auditing services or others.
<br>
• Sponsors, Business Partners and other Third Parties: We may disclose your Personal Information to Sponsors and co-sponsors of promotions, business partners and other third parties in order to provide you with services that may interest you. Furthermore, we may share your Personal Information with third party providers located on-site at hotels or resorts, such as spas, golf clubs, concierge services, and dining providers. If you provide additional information to any of these third parties, such information will be subject to such third parties’ privacy practices.
<br>
• Message Boards: We may make reviews, message boards, blogs, and other such user-generated content facilities available to users. Any information disclosed in these areas becomes public information and you should always be careful when deciding to disclose your Personal Information. We are not responsible for privacy practices of other users including website operators to whom you provide information.
<br>
• Disclosure Permitted by Law as follows:
<br>
• to comply with a court order, subpoena, search warrant or other legal process
<br>
• to comply with legal, regulatory or administrative requirements of any governmental authorities
<br>
• to protect and defend the company, its parent, subsidiaries and affiliates, and all their officers, directors, employees, attorneys, agents, contractors, and partners, in connection with any legal action, claim, or dispute
<br>
• to enforce the terms of our website
<br>
• to prevent imminent physical harm to businesses that we may use for purposes of performing its functions in connection with the sale, assignment, or transfer of any subsidiary or affiliate hotel and resorts or website.
<br>
• Except as outlined in this Privacy Policy, we will not sell or trade to third parties any personal information obtained through its website without your consent.

<h2 id="choice-provide">Choosing to Provide Information</h2>
Providing personal information is required through the AHR website to be able to provide you with our products and services we offer. You may opt not to provide any personal information. However, we cannot guarantee the limits of services offered through our website.

If you submit any personal information on behalf of another person, you represent that you have the authority to provide such information and to permit us to use said personal information in accordance with this Privacy Notice.
<br><br>
If you submit any personal information on behalf of another person, you represent that you have the authority to provide such information and to permit us to use said personal information in accordance with this Privacy Notice.

<h2 id="security">How We Store & Secure Information</h2>
Personal information collected by the website is stored on secure on-premises servers. The secure servers are protected by firewalls and other standard security procedures. The secure servers are not generally accessible by unauthorized third parties but could become accessible in the event of a security breach. Unfortunately, no security system is 100% secure, thus we cannot ensure the security of all information you provide to us via the Services.

<h2 id="rights">Your Rights and Preferences under the Data Privacy Act of 2012</h2>
You acknowledge that you have a full understanding of and completely agree to giving your consent to us and/or its operating and related companies to collect, store, access, share, process, and/or destroy copies of your personal data. You further agree and give consent to the sharing of all your personal information with third parties, when required by the law or public authority in connection with the abovementioned purposes; and Warrant that you are fully aware and completely understand your rights under the Data Privacy Act, including the right to request access to your personal, sensitive and/or privileged data, as well as to move for the correction of the same, if said data is already inaccurate and/or outdated.
<br><br>
We shall safeguard the confidentiality of all types of personal data it has collected, stored, shared or used and treat them with reasonable and appropriate degree of protection.
<br><br>
We are keen to protect your privacy rights.
<br><br>
• You have the right to be informed for which purposes your personal data is collected.
<br>
• You have the right to access your personal data with us and it is your right to rectify should you find that your personal data needs updating. We respect the exercise of said rights provided that the accompanying request is not vexatious and unreasonable.
<br>
• You have the right to object to the processing of your personal data or withhold consent previously given. Likewise, you have the right to erasure or the blocking of your personal data. Should you exercise these rights, we will be constrained to limit your access to our facilities and/or quality service.
<br>
• You have the right to data portability.
<br>
• You have the right to damages.

<h2 id="choice-access">Choice, Access and Retention</h2>
You have choices when it comes to how we use your data, and we want to ensure you have the information to make the choices that are right for you.
<br><br>@endverbatim

If you no longer want to receive marketing-related emails, you may opt out by visiting our <a href="https://ahr-hdf.com/unsubscribe-form/" target="_blank">unsubscribe page</a>.
<br><br>@verbatim
We will try to comply with your request as soon as reasonably practicable. If you opt out of receiving marketing emails from us, we may still send you important administrative messages, from which you cannot opt out.

<h2 id="requests">How to Request Access / Changes / Deletion / Restriction</h2>
@endverbatim
If you would like to request to access, change, delete, restrict the use or object to the processing of your Personal Data that you have previously provided to us, or if you would like to receive an electronic copy of your Personal Data for purposes of transmitting it to another company (to the extent these rights are provided to you by law), please complete the <a href="https://ahr-hdf.com/dpo-data-access-request/?utm_source=astoria_current&utm_medium=form&utm_campaign=DPO&utm_id=DPO+Data+Access+Request&utm_content=web_form" target="_blank">Data Subject Access Subject Form</a>. If you have any questions about the form or our process, feel free to email us at dpo@astoria.com.ph
<br><br>@verbatim
For your protection, we may need to verify your identity before fulfilling your request. We will try to comply with your request as soon as reasonably practicable and consistent with applicable law.
<br><br>
Please note that we often need to retain certain data for recordkeeping purposes and/or to complete any transactions that you began prior to requesting a change or deletion (e.g., when you make a purchase or reservation, or enter a promotion, you may not be able to change or delete the Personal Data provided until after the completion of such purchase, reservation, or promotion). There may also be residual data that will remain within our databases and other records, which will not be removed. In addition, there may be certain data that we may not allow you to review for legal, security, or other reasons.

<h2 id="retention">Retention</h2>
We will retain your Personal Data for the period necessary to fulfill the purposes outlined in this Privacy Statement unless a longer retention period is required or permitted by law.
<br><br>
The criteria used to determine our retention periods include:
<br><br>
• The length of time we have an ongoing relationship with you and provide the Services to you (for example, for as long as you have an account with us or keep using our Services)
<br>
• Whether there is a legal obligation to which we are subject (for example, certain laws require us to keep records of your transactions for a certain period before we can delete them)
<br>
• Whether retention is advisable considering our legal position (such as, for statutes of limitations, litigation or regulatory investigations)

<h2 id="sensitive">Sensitive Data</h2>
Unless specifically requested, we ask that you not send us, and you not disclose, on or through the Services or otherwise to us, any Sensitive Personal Data e.g., social security number, taxpayer identification number, passport number, driver’s license number or other government-issued identification number; credit or debit card details or financial account number, with or without any code or password that would permit access to the account, credit history; or information on race, religion, ethnicity, sex life or practices or sexual orientation, medical or health information, genetic or biometric information, biometric templates, political or philosophical beliefs, political party membership, background check information, judicial data such as criminal records or information on other judicial or administrative proceedings.

<h2 id="use-of-services-by-minors">Use of Services by Minors</h2>
The Services are not directed to individuals under the age of sixteen (16), and we request that they not provide Personal Data through the Services.

<h2 id="updates-to-this-privacy-statement">Updates to this Privacy Statement</h2>
The Services are not directed to individuals under the age of sixteen (16), and we request that they not provide Personal Data through the Services.

@endverbatim
<h2 id="additional-privacy">Additional Privacy</h2>
<p>CCTV Privacy Policy <a href="{{ route('cctv-policy') }}">(CLICK HERE)</a></p>
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
  // Copy with "Copied!" (Print CTA removed per request)
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
