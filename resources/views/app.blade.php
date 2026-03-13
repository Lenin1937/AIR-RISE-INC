<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Title is managed by Inertia <Head> components via @inertiaHead --}}

        {{-- ================================================================
             SERVER-SIDE SEO META TAGS
             These are rendered in the initial HTML (no JavaScript needed),
             making them visible to social media scrapers and search bots.
             The Vue <Head> component provides client-side updates for SPA nav.
        ================================================================ --}}
        @php
            $baseUrl        = config('app.url');
            $logoUrl        = $baseUrl . '/logo.png';
            $component      = $page['component'] ?? null;

            // OG image: blog pages use blog banner, individual posts use their own image (or blog banner fallback)
            if ($component === 'Marketing/Blog/Show') {
                $postImageUrl   = $page['props']['post']['image_url'] ?? null;
                $defaultOgImage = $postImageUrl ?: ($baseUrl . '/blog-post-previwe.jpg');
            } elseif ($component === 'Marketing/Blog/Index') {
                $defaultOgImage = $baseUrl . '/blog-post-previwe.jpg';
            } else {
                $defaultOgImage = $baseUrl . '/images/og-preview.jpg';  // main social media preview photo
            }

            // Private pages — block from crawlers
            $isPrivate = $component && (
                str_starts_with($component, 'Admin/')
                || in_array($component, [
                    'Auth/Login','Auth/Register','Auth/ForgotPassword',
                    'Auth/ResetPassword','Auth/VerifyEmail','Auth/ConfirmPassword',
                    'Dashboard','Orders/Index','Orders/Create','Orders/Payment',
                    'Documents/Index','Messages/Index','Notifications/Index',
                    'PaymentMethods/Index','Profile/Edit',
                ])
            );

            // Helpers
            $faq = fn(string $q, string $a) => ['@type' => 'Question', 'name' => $q,
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a]];
            $breadcrumb = fn(array $items) => [
                '@context' => 'https://schema.org',
                '@type'    => 'BreadcrumbList',
                'itemListElement' => array_map(fn($it, $pos) => [
                    '@type'    => 'ListItem',
                    'position' => $pos + 1,
                    'name'     => $it[0],
                    'item'     => $it[1],
                ], $items, array_keys($items)),
            ];

            $seoConfig = [
                'Marketing/Home' => [
                    'title'       => 'LLC & Business Formation Services | CORPIUS',
                    'description' => 'Form your LLC, C-Corp, S-Corp, or Nonprofit with CORPIUS. Expert business formation, tax filing, and Green Card lottery services. Get started today.',
                    'url'         => $baseUrl . '/',
                    'schema'      => ['@context' => 'https://schema.org', '@graph' => [
                        ['@type' => 'Organization', 'name' => 'CORPIUS', 'url' => $baseUrl,
                         'alternateName' => ['CORPIUS', 'Corp', 'US Corp', 'NY Corp', 'US LLC', 'US C Corp', 'NY Corporation', 'Corpus', 'Corpon', 'Corpiuz', 'Corpuz', 'uscorp', 'nycorp', 'usllc', 'usccorp', 'nycorporation'],
                         'logo'  => ['@type' => 'ImageObject', 'url' => $logoUrl],
                         'description' => 'Professional business formation services: LLC, C-Corp, S-Corp, Nonprofit, Tax Filing, Green Card Lottery.',
                         'areaServed' => 'US',
                         'sameAs' => ['https://x.com/corpius_ny', 'https://www.facebook.com/corpius.ny/', 'https://www.linkedin.com/company/corpius', 'https://www.trustpilot.com/review/corpius.net', 'https://www.quora.com/profile/James-Steward-124'],
                         'contactPoint' => ['@type' => 'ContactPoint', 'contactType' => 'customer support', 'url' => $baseUrl . '/contact'],
                         'aggregateRating' => ['@type' => 'AggregateRating', 'ratingValue' => '4.9', 'bestRating' => '5', 'ratingCount' => '10000', 'reviewCount' => '10000']],
                        ['@type' => 'WebSite', 'name' => 'CORPIUS', 'url' => $baseUrl,
                         'potentialAction' => ['@type' => 'SearchAction',
                             'target' => ['@type' => 'EntryPoint', 'urlTemplate' => $baseUrl . '/knowledge-base?search={search_term_string}'],
                             'query-input' => 'required name=search_term_string']],
                    ]],
                ],
                'Marketing/Pricing' => [
                    'title'       => 'Transparent Pricing | Business Formation Services | CORPIUS',
                    'description' => 'Affordable business formation packages from CORPIUS. Compare LLC, C-Corp, S-Corp, and Nonprofit plans. Simple pricing, no hidden fees.',
                    'url'         => $baseUrl . '/pricing',
                    'schema'      => ['@context' => 'https://schema.org', '@type' => 'FAQPage',
                        'mainEntity' => [
                            $faq('How much does LLC formation cost with CORPIUS?', 'CORPIUS offers LLC formation starting from $149 plus state fees, which vary by state. All plans include the required state filing and registered agent service.'),
                            $faq('Are there hidden fees in your packages?', 'No. CORPIUS pricing is fully transparent. The price shown includes all our service fees. State filing fees are listed separately as they vary by state.'),
                            $faq('What is the difference between Standard and Pro plans?', 'The Pro plan includes expedited filing (1–2 business days vs. 5–7), priority customer support, a full compliance calendar, and additional document preparation services.'),
                            $faq('Do you offer a money-back guarantee?', 'Yes. We offer a satisfaction guarantee on our formation services. Contact our support team within 30 days if you are not satisfied with your order.'),
                        ],
                    ],
                ],
                'Marketing/About' => [
                    'title'       => 'About CORPIUS | Business Formation Experts',
                    'description' => 'CORPIUS is a trusted business formation company helping entrepreneurs launch LLCs, corporations, and nonprofits with expert guidance and support.',
                    'url'         => $baseUrl . '/about',
                    'schema'      => ['@context' => 'https://schema.org', '@type' => 'AboutPage',
                        'name' => 'About CORPIUS', 'url' => $baseUrl . '/about',
                        'description' => 'CORPIUS is a trusted business formation company helping entrepreneurs launch LLCs, corporations, and nonprofits with expert guidance and support.',
                        'mainEntity' => ['@type' => 'Organization', 'name' => 'CORPIUS', 'url' => $baseUrl,
                            'logo' => ['@type' => 'ImageObject', 'url' => $logoUrl],
                            'description' => 'Professional business formation services: LLC, C-Corp, S-Corp, Nonprofit, Tax Filing, Green Card Lottery.',
                            'areaServed' => 'US',
                            'sameAs' => ['https://x.com/corpius_ny', 'https://www.facebook.com/corpius.ny/', 'https://www.linkedin.com/company/corpius', 'https://www.trustpilot.com/review/corpius.net', 'https://www.quora.com/profile/James-Steward-124']]],
                ],
                'Marketing/Contact' => [
                    'title'       => 'Contact CORPIUS | Business Formation Support',
                    'description' => 'Get in touch with CORPIUS for business formation questions. Expert advice on LLC, C-Corp, S-Corp, Nonprofit, income tax, and Green Card services.',
                    'url'         => $baseUrl . '/contact',
                    'schema'      => ['@context' => 'https://schema.org', '@type' => 'ContactPage',
                        'name' => 'Contact CORPIUS', 'url' => $baseUrl . '/contact',
                        'mainEntity' => ['@type' => 'Organization', 'name' => 'CORPIUS', 'url' => $baseUrl,
                            'contactPoint' => ['@type' => 'ContactPoint', 'contactType' => 'customer support', 'url' => $baseUrl . '/contact']]],
                ],
                'Marketing/KnowledgeBase' => [
                    'title'       => 'Knowledge Base | Business Formation Resources | CORPIUS',
                    'description' => 'Browse CORPIUS knowledge base for expert guides on LLC formation, corporate law, tax planning, and immigration. Free resources for entrepreneurs.',
                    'url'         => $baseUrl . '/knowledge-base',
                ],
                'Marketing/Services/LLC' => [
                    'title'       => 'LLC Formation Services | Start Your LLC | CORPIUS',
                    'description' => 'Form your LLC quickly with CORPIUS. Get limited liability protection, pass-through taxation, and flexible management. Free registered agent included.',
                    'url'         => $baseUrl . '/services/llc',
                    'schema'      => ['@context' => 'https://schema.org', '@graph' => [
                        $breadcrumb([['Home',$baseUrl.'/'],['Services',$baseUrl.'/services/llc'],['LLC Formation',$baseUrl.'/services/llc']]),
                        ['@type'=>'Service','name'=>'LLC Formation','serviceType'=>'LLC Formation','areaServed'=>'US',
                         'provider'=>['@type'=>'Organization','name'=>'CORPIUS','url'=>$baseUrl],'url'=>$baseUrl.'/services/llc'],
                        ['@type'=>'FAQPage','mainEntity'=>[
                            $faq('What is an LLC?','A Limited Liability Company (LLC) is a flexible business entity that combines the liability protection of a corporation with the tax benefits of a sole proprietorship or partnership.'),
                            $faq('How long does it take to form an LLC?','With CORPIUS, standard LLC formation takes 5–7 business days. The Pro plan includes expedited processing in 1–2 business days, depending on your state.'),
                            $faq('What are the benefits of forming an LLC?','Benefits include personal liability protection, pass-through taxation (avoiding double taxation), flexible management structure, and enhanced business credibility.'),
                            $faq('Can a non-US resident form an LLC?','Yes. Non-US residents and foreign nationals can form an LLC in the United States. CORPIUS handles all filings and registered agent requirements.'),
                            $faq('Do I need a registered agent for my LLC?','Yes. Every LLC must have a registered agent in its state of formation. CORPIUS provides registered agent service included with all LLC formation plans.'),
                        ]],
                    ]],
                ],
                'Marketing/Services/CCorporation' => [
                    'title'       => 'C-Corporation Formation | Incorporate Your C-Corp | CORPIUS',
                    'description' => 'Incorporate your C-Corporation with CORPIUS. Issue stock, raise venture capital, and scale your business with the most flexible corporate structure available.',
                    'url'         => $baseUrl . '/services/c-corporation',
                    'schema'      => ['@context' => 'https://schema.org', '@graph' => [
                        $breadcrumb([['Home',$baseUrl.'/'],['Services',$baseUrl.'/services/c-corporation'],['C-Corporation',$baseUrl.'/services/c-corporation']]),
                        ['@type'=>'Service','name'=>'C-Corporation Formation','serviceType'=>'C-Corporation Formation','areaServed'=>'US',
                         'provider'=>['@type'=>'Organization','name'=>'CORPIUS','url'=>$baseUrl],'url'=>$baseUrl.'/services/c-corporation'],
                        ['@type'=>'FAQPage','mainEntity'=>[
                            $faq('What is a C-Corporation?','A C-Corporation is a legal business structure taxed separately from its owners. It can have unlimited shareholders, issue multiple classes of stock, and is the preferred structure for venture-backed startups.'),
                            $faq('How does a C-Corp differ from an LLC?','Unlike an LLC, a C-Corp can issue stock to raise venture capital and offer employee stock options. However, C-Corps face double taxation — once at the corporate level and again when dividends are distributed.'),
                            $faq('Can foreign nationals own a C-Corporation?','Yes. Unlike S-Corporations, C-Corporations have no citizenship or residency requirements. Foreigners and foreign companies can own C-Corp stock freely.'),
                            $faq('What is the C-Corporation tax rate?','The federal corporate income tax rate for C-Corporations is a flat 21% following the Tax Cuts and Jobs Act.'),
                        ]],
                    ]],
                ],
                'Marketing/Services/SCorporation' => [
                    'title'       => 'S-Corporation Formation | Tax-Efficient Business Structure | CORPIUS',
                    'description' => 'Form your S-Corporation with CORPIUS. Reduce self-employment taxes with pass-through taxation while maintaining full limited liability protection.',
                    'url'         => $baseUrl . '/services/s-corporation',
                    'schema'      => ['@context' => 'https://schema.org', '@graph' => [
                        $breadcrumb([['Home',$baseUrl.'/'],['Services',$baseUrl.'/services/s-corporation'],['S-Corporation',$baseUrl.'/services/s-corporation']]),
                        ['@type'=>'Service','name'=>'S-Corporation Formation','serviceType'=>'S-Corporation Formation','areaServed'=>'US',
                         'provider'=>['@type'=>'Organization','name'=>'CORPIUS','url'=>$baseUrl],'url'=>$baseUrl.'/services/s-corporation'],
                        ['@type'=>'FAQPage','mainEntity'=>[
                            $faq('What is an S-Corporation?','An S-Corporation passes income, deductions, and credits through to shareholders for federal tax purposes, avoiding double taxation while providing limited liability protection.'),
                            $faq('What are the tax savings of an S-Corp?','S-Corp owners pay themselves a reasonable salary and take additional profits as distributions. Only the salary is subject to self-employment taxes, potentially saving thousands per year.'),
                            $faq('Who can own an S-Corporation?','S-Corps are restricted to US citizens or permanent residents, a maximum of 100 shareholders, and only one class of stock. Corporations and partnerships cannot be S-Corp shareholders.'),
                            $faq('Can an LLC elect S-Corp tax status?','Yes. An LLC can elect to be taxed as an S-Corporation by filing IRS Form 2553, retaining its flexible management structure while gaining S-Corp tax benefits.'),
                        ]],
                    ]],
                ],
                'Marketing/Services/Nonprofit' => [
                    'title'       => 'Nonprofit Formation | 501(c)(3) Tax-Exempt Organization | CORPIUS',
                    'description' => 'Start your 501(c)(3) nonprofit with CORPIUS. Expert IRS applications, state filings, and compliance support to launch your mission-driven organization.',
                    'url'         => $baseUrl . '/services/nonprofit',
                    'schema'      => ['@context' => 'https://schema.org', '@graph' => [
                        $breadcrumb([['Home',$baseUrl.'/'],['Services',$baseUrl.'/services/nonprofit'],['Nonprofit Formation',$baseUrl.'/services/nonprofit']]),
                        ['@type'=>'Service','name'=>'Nonprofit 501(c)(3) Formation','serviceType'=>'Nonprofit Formation','areaServed'=>'US',
                         'provider'=>['@type'=>'Organization','name'=>'CORPIUS','url'=>$baseUrl],'url'=>$baseUrl.'/services/nonprofit'],
                        ['@type'=>'FAQPage','mainEntity'=>[
                            $faq('What is a 501(c)(3) organization?','A 501(c)(3) is an IRS-recognized tax-exempt nonprofit. Donations to 501(c)(3) organizations are often tax-deductible for donors on their federal income tax returns.'),
                            $faq('How long does it take to get 501(c)(3) status?','The IRS typically takes 3–6 months to review Form 1023. The streamlined Form 1023-EZ for eligible organizations can be approved in as little as 2–4 weeks.'),
                            $faq('What are the advantages of forming a nonprofit?','Key advantages include federal and state tax exemption, eligibility for grants and tax-deductible donations, reduced postage rates, and enhanced credibility for your mission.'),
                            $faq('What types of nonprofits can CORPIUS help form?','CORPIUS assists with charitable organizations, religious organizations, educational institutions, scientific foundations, and other 501(c)(3) entities.'),
                        ]],
                    ]],
                ],
                'Marketing/Services/GreenCard' => [
                    'title'       => 'Green Card Lottery Application | DV Lottery Program | CORPIUS',
                    'description' => 'Apply for the Green Card Diversity Visa Lottery with CORPIUS. Expert guidance, accurate DV lottery entries, and real-time application status tracking.',
                    'url'         => $baseUrl . '/services/green-card-lottery',
                    'schema'      => ['@context' => 'https://schema.org', '@graph' => [
                        $breadcrumb([['Home',$baseUrl.'/'],['Services',$baseUrl.'/services/green-card-lottery'],['Green Card Lottery',$baseUrl.'/services/green-card-lottery']]),
                        ['@type'=>'Service','name'=>'Green Card DV Lottery Application','serviceType'=>'Immigration Assistance','areaServed'=>'US',
                         'provider'=>['@type'=>'Organization','name'=>'CORPIUS','url'=>$baseUrl],'url'=>$baseUrl.'/services/green-card-lottery'],
                        ['@type'=>'FAQPage','mainEntity'=>[
                            $faq('What is the Green Card Diversity Visa Lottery?','The DV Lottery is a US government program that makes 50,000 immigrant visas available annually to applicants from countries with historically low immigration rates to the United States.'),
                            $faq('When does the Green Card Lottery registration open?','The DV Lottery registration period typically opens in October and closes in early November each year. Winners are notified the following May.'),
                            $faq('What are the DV Lottery eligibility requirements?','Applicants must be from an eligible country, have a high school education or equivalent work experience, and must not have a disqualifying criminal record or immigration violation.'),
                            $faq('How does CORPIUS help with the DV Lottery application?','CORPIUS provides expert guidance through the complete application, ensures photos and information meet strict State Department specs, and provides real-time application status tracking.'),
                        ]],
                    ]],
                ],
                'Marketing/Services/IncomeTax' => [
                    'title'       => 'Income Tax Filing & Planning | Professional Tax Services | CORPIUS',
                    'description' => 'Professional income tax filing and planning with CORPIUS. Maximize deductions, minimize tax liability, and stay IRS-compliant with expert CPAs.',
                    'url'         => $baseUrl . '/services/income-tax-filing-planning',
                    'schema'      => ['@context' => 'https://schema.org', '@graph' => [
                        $breadcrumb([['Home',$baseUrl.'/'],['Services',$baseUrl.'/services/income-tax-filing-planning'],['Income Tax Filing',$baseUrl.'/services/income-tax-filing-planning']]),
                        ['@type'=>'Service','name'=>'Income Tax Filing & Planning','serviceType'=>'Tax Preparation','areaServed'=>'US',
                         'provider'=>['@type'=>'Organization','name'=>'CORPIUS','url'=>$baseUrl],'url'=>$baseUrl.'/services/income-tax-filing-planning'],
                        ['@type'=>'FAQPage','mainEntity'=>[
                            $faq('When is the federal income tax filing deadline?','The standard federal individual and business (calendar year) tax deadline is April 15. A 6-month extension to October 15 is available by filing Form 4868.'),
                            $faq('What business deductions can I claim?','Common deductions include home office expenses, vehicle mileage, professional services, business travel, marketing costs, employee salaries, and self-employed health insurance premiums.'),
                            $faq('What is the penalty for filing taxes late?','The IRS charges a Failure to File penalty of 5% of unpaid taxes per month, up to 25%. Interest on unpaid taxes accrues daily from the due date.'),
                            $faq('Does CORPIUS offer year-round tax planning?','Yes. CORPIUS provides year-round tax strategy to minimize tax liability, manage estimated payments, select the optimal business structure, and maintain IRS compliance.'),
                        ]],
                    ]],
                ],
            ];

            $seo = null;

            if ($component) {
                if (isset($seoConfig[$component])) {
                    $seo = $seoConfig[$component];
                } elseif ($component === 'Marketing/KnowledgeBaseShow' && !empty($page['props']['article'])) {
                    $art      = $page['props']['article'];
                    $slug     = $art['slug']   ?? '';
                    $artTitle = $art['title']  ?? 'Knowledge Base Article';
                    $excerpt  = $art['excerpt'] ?? '';
                    $desc     = $excerpt ? mb_substr($excerpt, 0, 155) : ($artTitle . ' — Expert guide from CORPIUS Knowledge Base.');
                    $artUrl   = $baseUrl . '/knowledge-base/' . $slug;
                    $seo = [
                        'title'       => $artTitle . ' | CORPIUS Knowledge Base',
                        'description' => $desc,
                        'url'         => $artUrl,
                        'type'        => 'article',
                        'schema'      => ['@context' => 'https://schema.org', '@graph' => [
                            $breadcrumb([['Home',$baseUrl.'/'],['Knowledge Base',$baseUrl.'/knowledge-base'],[$artTitle,$artUrl]]),
                            ['@type' => 'Article', 'headline' => $artTitle, 'description' => $desc, 'url' => $artUrl,
                             'publisher' => ['@type' => 'Organization', 'name' => 'CORPIUS', 'url' => $baseUrl,
                                            'logo' => ['@type' => 'ImageObject', 'url' => $logoUrl]]],
                        ]],
                    ];
                }
            }
        @endphp

        @if ($seo)
            {{-- Core SEO tags rendered server-side for crawlers and SEO tools --}}
            @if (isset($seo['title']))
                <title>{{ $seo['title'] }}</title>
                <meta property="og:title" content="{{ $seo['title'] }}">
                <meta name="twitter:title" content="{{ $seo['title'] }}">
            @endif
            @if (isset($seo['description']))
                <meta name="description" content="{{ $seo['description'] }}">
                <meta property="og:description" content="{{ $seo['description'] }}">
                <meta name="twitter:description" content="{{ $seo['description'] }}">
            @endif
            @if (isset($seo['url']))
                <link rel="canonical" href="{{ $seo['url'] }}">
                <meta property="og:url" content="{{ $seo['url'] }}">
            @endif
            <meta property="og:type" content="{{ $seo['type'] ?? 'website' }}">
            @if (isset($seo['schema']))
                <script type="application/ld+json">{!! json_encode($seo['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
            @endif
        @endif

        {{-- Robots: block private/auth pages --}}
        @if ($isPrivate)
            <meta name="robots" content="noindex, nofollow">
        @else
            <meta name="robots" content="index, follow">
        @endif

        <!-- Global defaults -->
        <meta name="author" content="CORPIUS">
        <meta name="theme-color" content="#facc15">
        @if (!$isPrivate)
        <meta name="keywords" content="corpius, corp, uscorp, nycorp, usllc, usccorp, nycorporation, inc, corpus, corpon, corpiuz, corpuz, LLC formation, business formation, incorporate, C-Corp, S-Corp, nonprofit formation, EIN, registered agent, business registration USA">
        @endif
        <meta property="og:site_name" content="CORPIUS">
        <meta property="og:locale"    content="en_US">
        <meta property="og:image"        content="{{ $defaultOgImage }}">
        <meta property="og:image:secure_url" content="{{ $defaultOgImage }}">
        <meta property="og:image:type"   content="image/jpeg">
        <meta property="og:image:alt"    content="CORPIUS — Business Formation Services">
        <meta property="og:image:width"  content="1280">
        <meta property="og:image:height" content="853">
        <meta name="twitter:card"  content="summary_large_image">
        <meta name="twitter:site"  content="@corpius">
        <meta name="twitter:image" content="{{ $defaultOgImage }}">
        <meta name="twitter:image:alt" content="CORPIUS — Business Formation Services">

        <!-- Favicons -->
        <link rel="icon"             type="image/png"    href="{{ asset('favicon.png') }}">
        <link rel="shortcut icon"    type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

        <!-- DNS prefetch + font preconnect -->
        <link rel="dns-prefetch" href="//fonts.bunny.net">
        <link rel="preconnect"   href="https://fonts.bunny.net" crossorigin>
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
