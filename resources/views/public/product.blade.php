<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#12352f">
    <meta name="description" content="Verified product information for {{ $productName }}.">
    <title>{{ $productName }} — Product information</title>
    <link rel="stylesheet" href="/css/public-product.css">
</head>
<body>
<!--
THESIS: A product passport that puts identity and safety before promotion; it refuses the generic card-grid catalogue.
OWN-WORLD: Deep evergreen ink, mineral paper, citrus safety signals, ruled disclosure rows, and one continuous reading rail.
STORY: Identify the product, see urgent disclosures, navigate the controlled facts, and confirm provenance.
FIRST VIEWPORT: Product image left or above, identity at full scale, verified-source seal and safety summary visible before the fold.
FORM: Mobile product passport, grounded structure five, seed 434914f1.
FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, and DESIGN.md
-->
<header class="site-header">
    <div class="wordmark" aria-label="VDOT Product Information">
        <span class="wordmark-mark" aria-hidden="true">V</span>
        <span>Product information</span>
    </div>
    <span class="source-status">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m7.5 12 3 3 6-7"/><circle cx="12" cy="12" r="9"/></svg>
        VDOT source
    </span>
</header>

<main>
    <section class="product-identity" aria-labelledby="product-title">
        <div class="product-visual{{ $imagePath ? '' : ' product-visual--empty' }}">
            @if($imagePath)
                <img src="{{ $imagePath }}" alt="{{ $productName }} product packaging" width="720" height="720">
            @else
                <span aria-hidden="true">{{ mb_strtoupper(mb_substr($productName, 0, 1)) }}</span>
                <p>Product image not supplied</p>
            @endif
        </div>

        <div class="product-heading">
            @if($brandName)
                <p class="brand-name">{{ $brandName }}</p>
            @endif
            <h1 id="product-title">{{ $productName }}</h1>
            <dl class="identity-facts">
                @if($categoryName)
                    <div><dt>Category</dt><dd>{{ $categoryName }}</dd></div>
                @endif
                @if($companyName)
                    <div><dt>Responsible company</dt><dd>{{ $companyName }}</dd></div>
                @endif
                <div><dt>Unit reference</dt><dd>{{ $asset->asset_tag }}</dd></div>
            </dl>
            <div class="verification-note">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m7.5 12 3 3 6-7"/><circle cx="12" cy="12" r="9"/></svg>
                <div>
                    <strong>Published from the controlled VDOT record</strong>
                    <span>Information updated {{ $updatedAt?->format('d M Y') ?: 'recently' }}</span>
                </div>
            </div>
        </div>
    </section>

    @if(isset($sections['allergens']))
        <section class="safety-summary" aria-labelledby="safety-title">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 2.8 20h18.4L12 3Z"/><path d="M12 9v5m0 3h.01"/></svg>
            <div>
                <h2 id="safety-title">Allergens and warnings</h2>
                @foreach($sections['allergens']['fields'] as $field)
                    <p><strong>{{ $field['name'] }}:</strong> {{ $field['value'] }}</p>
                @endforeach
            </div>
        </section>
    @endif

    @if(count($sections) > 0)
        <nav class="section-rail" aria-label="Product information sections">
            @foreach($sections as $sectionKey => $section)
                <a href="#section-{{ $sectionKey }}">{{ $section['label'] }}</a>
            @endforeach
        </nav>

        <div class="disclosure-sections">
            @foreach($sections as $sectionKey => $section)
                <section id="section-{{ $sectionKey }}" class="disclosure-section" aria-labelledby="heading-{{ $sectionKey }}">
                    <h2 id="heading-{{ $sectionKey }}">{{ $section['label'] }}</h2>
                    <dl>
                        @foreach($section['fields'] as $field)
                            <div>
                                <dt>{{ $field['name'] }}</dt>
                                <dd>
                                    @if($field['url'])
                                        <a href="{{ $field['url'] }}" rel="nofollow noopener noreferrer">{{ $field['value'] }}</a>
                                    @else
                                        {!! nl2br(e($field['value'])) !!}
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endforeach
        </div>
    @else
        <section class="empty-disclosure" aria-labelledby="empty-title">
            <h2 id="empty-title">Product details are being prepared</h2>
            <p>The product identity is verified, but no additional information has been approved for public display yet.</p>
        </section>
    @endif
</main>

<footer>
    <p>Information is supplied by the responsible company from its VDOT record. Always follow the physical package and professional medical advice where applicable.</p>
    <p class="footer-reference">Reference {{ $asset->asset_tag }}</p>
</footer>
</body>
</html>
