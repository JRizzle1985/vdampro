<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light"><meta name="theme-color" content="#173bd4">
    <meta name="description" content="Verified product information for {{ $productName }}.">
    <title>{{ $productName }} — Verified product record</title>
    <link rel="stylesheet" href="/css/public-product.css?v=20260821-3">
</head>
<body>
<!--
THESIS: The product record is a living public dossier, not a generic catalogue card.
OWN-WORLD: Electric cobalt, carbon ink, signal lime, oversized grotesk type, and disciplined ledger rules.
STORY: Recognise the product, verify its source and scan activity, then read every approved disclosure by subject.
FIRST VIEWPORT: A cobalt identity field carries the name at monumental scale, with packaging beside a live verification ledger.
FORM: Controlled product dossier, grounded direction five, seed 2ecf32e3.
FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, and DESIGN.md
-->
<header class="masthead">
    <a class="brand-lockup" href="#product-record" aria-label="Veridot verified product record">@if($brandLogoPath)<img src="{{ $brandLogoPath }}" alt="Veridot" width="195" height="48">@else<span class="brand-wordmark">VERIDOT</span>@endif<span>Public Record</span></a>
    <div class="masthead-actions"><span class="live-source"><i aria-hidden="true"></i> Verified source</span><a class="vdot-login" href="{{ $vdotUrl }}">View in VDOT <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7 4h9v9M16 4 6 14M4 7v9h9"/></svg></a></div>
</header>
<main id="product-record">
    <section class="hero" aria-labelledby="product-title">
        <div class="hero-copy">
            @if($brandName)<p class="brand-name">{{ $brandName }}</p>@endif
            <h1 id="product-title">{{ $productName }}</h1>
            <p class="hero-summary">A verified public product record supplied from the controlled VDOT source.</p>
            <dl class="hero-meta">
                @if($categoryName)<div><dt>Type</dt><dd>{{ $categoryName }}</dd></div>@endif
                @if($companyName)<div><dt>Responsible company</dt><dd>{{ $companyName }}</dd></div>@endif
            </dl>
        </div>
        <div class="product-stage{{ $imagePath ? '' : ' product-stage--empty' }}">
            <span class="stage-orbit" aria-hidden="true"></span>
            @if($imagePath)
                <img src="{{ $imagePath }}" alt="{{ $productName }} product packaging" width="720" height="720" decoding="async" fetchpriority="high">
            @else
                <span class="product-monogram" aria-hidden="true">{{ mb_strtoupper(mb_substr($productName, 0, 1)) }}</span><p>Packaging image pending</p>
            @endif
        </div>
    </section>
    <section class="record-ledger" aria-label="Record verification">
        <div class="ledger-lead"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m7.5 12 3 3 6-7"/><circle cx="12" cy="12" r="9"/></svg><div><strong>Source verified</strong><span>Published from a controlled VDOT record</span></div></div>
        <dl>
            <div><dt>Public scans</dt><dd>{{ number_format($scanCount) }}</dd></div>
            <div><dt>Last updated</dt><dd>{{ $updatedAt?->format('d M Y') ?: 'Recently' }}</dd></div>
            <div><dt>Unit reference</dt><dd>{{ $asset->asset_tag }}</dd></div>
        </dl>
    </section>
    @if(isset($sections['allergens']))
        <section class="safety-alert" aria-labelledby="safety-title"><span class="safety-icon" aria-hidden="true">!</span><div><h2 id="safety-title">Read before use</h2>@foreach($sections['allergens']['fields'] as $field)<p><strong>{{ $field['name'] }}</strong> {{ $field['value'] }}</p>@endforeach</div></section>
    @endif
    @if(count($sections) > 0)
        <nav class="section-rail" aria-label="Product information sections"><span>In this record</span><div>@foreach($sections as $sectionKey => $section)<a href="#section-{{ $sectionKey }}">{{ $section['label'] }}</a>@endforeach</div></nav>
        <div class="disclosure-sections">
            @foreach($sections as $sectionKey => $section)
                <section id="section-{{ $sectionKey }}" class="disclosure-section" aria-labelledby="heading-{{ $sectionKey }}">
                    <header><h2 id="heading-{{ $sectionKey }}">{{ $section['label'] }}</h2></header>
                    <dl>@foreach($section['fields'] as $field)<div><dt>{{ $field['name'] }}</dt><dd>@if($field['url'])<a href="{{ $field['url'] }}" rel="nofollow noopener noreferrer">{{ $field['value'] }}</a>@else{!! nl2br(e($field['value'])) !!}@endif</dd></div>@endforeach</dl>
                </section>
            @endforeach
        </div>
    @else
        <section class="empty-disclosure" aria-labelledby="empty-title"><h2 id="empty-title">Product details are being prepared</h2><p>The product identity is verified, but no additional information has been approved for public display yet.</p></section>
    @endif
</main>
<footer><div class="footer-brand">@if($brandLogoPath)<img src="{{ $brandLogoPath }}" alt="" width="195" height="48">@endif<strong>Verified by VDOT</strong></div><p>Information is supplied by the responsible company from its controlled product record. Always follow the physical package and professional medical advice where applicable.</p><a href="#product-record">Back to top</a></footer>
</body>
</html>
