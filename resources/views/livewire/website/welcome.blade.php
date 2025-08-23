<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ setting('websiteName', env('APP_NAME')) }}</title>
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Lato:wght@400;700&family=Open+Sans:wght@400;700&family=Montserrat:wght@400;700&family=Poppins:wght@400;700&display=swap"
        rel="stylesheet">
    <!-- lineawesome icons -->
    <link rel="stylesheet"
        href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    {{-- alpinejs --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @php
        $savedColor = setting('websiteColor', '#21a179');
        $appColor = new \OzdemirBurak\Iris\Color\Hex($savedColor);
        $appColorHsla = new \OzdemirBurak\Iris\Color\Hsla('' . $appColor->toHsla()->hue() . ',40%, 75%, 0.45');
        $colorShades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900];
        $colorShadePercentages = [95, 90, 75, 50, 25, 0, 5, 15, 25, 35];
        $appColorShades = [];
        foreach ($colorShades as $key => $colorShade) {
            if ($key < 5) {
                $appColorShade = $appColor->brighten($colorShadePercentages[$key]);
            } else {
                $appColorShade = $appColor->darken($colorShadePercentages[$key]);
            }
            $appColorShades[] = $appColorShade;
        }

        //
        function getContrastColor($hexColor, $reversed = false)
        {
            // Remove the '#' if it's there
    $hexColor = ltrim($hexColor, '#');

    // Convert 3-digit hex to 6-digit hex
    if (strlen($hexColor) == 3) {
        $hexColor = str_repeat($hexColor[0], 2) . str_repeat($hexColor[1], 2) . str_repeat($hexColor[2], 2);
    }

    // Get RGB values
    $r = hexdec(substr($hexColor, 0, 2));
    $g = hexdec(substr($hexColor, 2, 2));
    $b = hexdec(substr($hexColor, 4, 2));

    // Calculate relative luminance using the formula for luminance perception
    $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

    // Return white for dark colors, black for light colors
    // return $luminance > 0.5 ? 'black' : 'white';
    return $luminance > 0.5 && !$reversed ? 'black' : 'white';
        }

        //
        $faqColor = $appColorShades[5];

    @endphp



    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fcfcfc;
        }

        .bg-primary-500 {
            background-color: {{ $appColorShades[5] }} !important;
        }

        .text-primary-500 {
            color: {{ $appColorShades[5] }} !important;
        }

        .plain-btn {
            background-color: transparent;
            border: 0.6px solid white;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            transition: background-color 0.3s;
        }

        .accent-btn {
            background-color: {{ $appColorShades[7] }} !important;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            transition: background-color 0.3s;
            /* shadow */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .nav-item {
            padding: 0.2rem 1rem;
            border-radius: 0.75rem;
            transition: background-color 0.3s;
            color: white;
            font-size: medium;
            text-decoration-line: none !important;
        }

        /* Hover Effect */
        .nav-item:hover {
            background-color: #f3f3f3;
            color: {{ $appColorShades[5] }};
        }

        /* active class */
        .nav-item.active {
            background-color: {{ $appColorShades[5] }};
        }

        .top-section {
            margin: 10px;
            padding: 10px;
            border-radius: 1em;
            background-color: {{ $appColorShades[5] }};
            color: white;
        }

        .intro-title {
            /* use clamps */
            font-size: clamp(1.5rem, 5vw, 3rem);
            align-items: center;
            text-align: center;
        }

        .intro-subtitle {
            /* use clamps */
            font-size: clamp(0.2rem, 4vw, 1.1rem);
            align-items: center;
            text-align: center;
        }

        /* features */
        .feature-section {
            margin: 10px;
            padding: 10px;
            border-radius: 1em;
            background-color: {{ $appColorShades[3] }} !important;
            color: {{ getContrastColor($appColorShades[3]) }} !important;
        }

        .other-section {
            margin: 10px;
            padding: 10px;
            border-radius: 1em;
            background-color: {{ $appColorShades[2] }};
            color: white;
        }

        .faq-section {
            margin: 10px;
            padding: 10px;
            border-radius: 1em;
            background-color: {{ $faqColor }} !important;
            color: {{ getContrastColor($faqColor) }} !important;
        }

        .card {
            margin: 10px;
            padding: 5px;
            border-radius: 1em;
            background-color: white;
            color: black;
        }

        .card:hover {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            background-color: {{ $appColorShades[7] }};
            color: white;
        }

        .card-title {
            /* use clamps */
            font-size: clamp(0.7rem, 5vw, 1.2rem);
            /* font weight 400 */
            font-weight: 600;
            align-items: center;
            text-align: center;

            margin: 10px 12px 0px 12px;
        }

        .card-subtitle {
            /* use clamps */
            font-size: clamp(0.2rem, 5vw, 0.85rem);
            align-items: center;
            text-align: center;
            margin: 5px 12px 10px 12px;
        }

        .faq-item {
            /* flex */
            display: flex;
            justify-content: space-between;
            background-color: white;
            border: 1px solid #e2dff2;
            padding: 10px 20px 10px 20px;
            border-radius: 20em;
        }


        /* footer */
        .footer-section {
            margin: 10px;
            padding: 10px 6% 10px 6%;
            border-radius: 1em;
            background-color: {{ $appColorShades[7] }};
            color: white;
        }


        .social-icon {
            padding: 4px 7px 4px 7px;
            background-color: {{ $appColorShades[5] }};
            color: white;
            /* center */
            display: flex;
            /* full rounded */
            border-radius: 100%;
            border: 1px solid {{ $appColorShades[4] }};
            /* width: 40px;
            height: 40px; */
            justify-content: center;
        }

        .bg-primary-600 {
            background-color: {{ $appColorShades[6] }};
        }

        /* add icon to li */
        li {
            position: relative;
            padding-left: 25px;
            /* Adjust according to icon size */
            margin-bottom: 10px;
        }

        ul li:before {
            content: "\f111";
            /* Font Awesome icon code for 'chevron-right' */
            font-family: "Font Awesome 6 Free";
            /* Ensure it matches the correct version */
            font-weight: 500;

            /* Necessary for Font Awesome 6 */
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            /* Center the icon */
            margin: auto;
        }

        .no-li-icon li:before {
            content: none;
            position: block;
            font-weight: 0;
            padding: 0px;
        }

        .no-li-icon li {
            padding: 0px;
        }


        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-900">
    <!-- top section -->
    <div class="top-section">
        <header class="">
            <div class="container mx-auto flex justify-between items-center py-6 px-4">
                <!-- Logo -->
                <div class="text-2xl font-bold">
                    <a href="#" class="flex items-center space-x-3">
                        <img src="{{ appLogo() }}" alt="Glover Logo" class="h-10" />
                        <span>{{ setting('websiteName', env('APP_NAME')) }}</span>
                    </a>
                </div>
                <!-- Navigation Menus -->
                <nav class="hidden md:flex space-x-8 text-lg">
                    <a href="#home" class="nav-item">{{ __('Home') }}</a>
                    <a href="#features" class="nav-item">{{ __('Features') }}</a>
                    <a href="#about-us" class="nav-item">{{ __('About Us') }}</a>
                    <a href="#contact-us" class="nav-item">{{ __('Contact Us') }}</a>
                    <a href="#faqs" class="nav-item">{{ __('FAQs') }}</a>
                </nav>
                <!-- Download App Button -->
                <div class="hidden md:block">
                    <a href="{{ route('login') }}" class="plain-btn !no-underline">
                        {{ __('Admin/Store Login') }}
                    </a>

                    {{-- <a href="#" class="plain-btn">
                        Download App
                    </a> --}}
                </div>

                {{-- small device menu --}}
                <button class="md:hidden px-2 rounded border hover:shadow" id="smallMenuOpenBtn">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </header>

        {{-- small device menu --}}
        <div class="fixed top-0 bottom-0 left-0 right-0 bg-white  hidden" id="sm-menu">
            <div class="flex justify-between items-center p-6 text-primary-500">
                <p class="text-xl">{{ __('Menu') }}</p>
                <button class="md:hidden px-2 rounded border hover:shadow text-black" id="smallMenuCloseBtn">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <hr class="my-0" />
            <div class="space-y-4 px-2 py-2 text-lg text-black">
                <!-- Navigation Menus -->
                <p>
                    <a href="#home" class="px-2 hover:underline hover:text-primary-500">
                        <i class="h-4 w-4 fa-solid fa-arrow-right"></i>
                        {{ __('Home') }}
                    </a>
                </p>

                <p>
                    <a href="#features" class="px-2  hover:underline hover:text-primary-500">
                        <i class="h-4 w-4 fa-solid fa-arrow-right"></i>
                        {{ __('Features') }}
                    </a>
                </p>

                <p>
                    <a href="#about-us" class="px-2  hover:underline hover:text-primary-500">
                        <i class="h-4 w-4 fa-solid fa-arrow-right"></i>
                        {{ __('About Us') }}
                    </a>
                </p>

                <p>
                    <a href="#contact-us" class="px-2  hover:underline hover:text-primary-500">
                        <i class="h-4 w-4 fa-solid fa-arrow-right"></i>
                        {{ __('Contact Us') }}
                    </a>
                </p>

                <p>
                    <a href="#faqs" class="px-2 hover:underline hover:text-primary-500">
                        <i class="h-4 w-4 fa-solid fa-arrow-right"></i>
                        {{ __('FAQs') }}
                    </a>
                </p>
                <p class="flex items-center justify-center">
                    <a href="{{ route('login') }}"
                        class="plain-btn !no-underline border border-gray-300 mx-auto px-4 py-1">
                        {{ __('Admin/Store Login') }}
                    </a>
                </p>

            </div>
        </div>

        <!-- info -->
        <div class="text-center m-5 lg:m-20">
            <div class="mx-auto w-10/12 md:w-8/12 lg:w-6/12">
                <p class="intro-title font-bold">
                    {!! setting('websiteHeaderTitle', '') !!}

                </p>

                <p class="lg:max-w-84 intro-subtitle">
                    {!! nl2br(setting('websiteHeaderSubtitle', '')) !!}
                </p>
                <!-- buttons -->
                <div class="flex items-center mx-auto justify-center my-8">
                    <div class="block md:flex items-center md:space-x-3 space-y-2 md:space-y-0">
                        <a class="accent-btn text-sm text-medium space-x-2 flex items-center justify-center"
                            href="https://play.google.com/store/apps/details?id={{ env('dynamic_link.android') }}">
                            <i class="fa-brands fa-android"></i>
                            <p>{{ __('Android Download') }}</p>
                        </a>
                        <a class="accent-btn text-sm text-medium space-x-2 flex items-center justify-center"
                            href="https://apps.apple.com/app/{{ env('dynamic_link.ios_id') }}">
                            <i class="fa-brands fa-app-store-ios"></i>
                            <p>{{ __('iOS Download') }}</p>
                        </a>
                    </div>
                </div>
            </div>
            <!-- intro image -->
            <img src="{{ getValidValue(setting('websiteHeaderImage'), asset('images/website/intro.png')) }}"
                alt="Home" class="center-white-shadow w-full md:w-8/12 lg:w-6/12 mx-auto my-8">
        </div>
    </div>

    <!-- features section -->
    <div class="feature-section justify-center py-20 text-black" id="features">
        <!-- text: Feature -->
        <div class="flex mb-2">
            <span class="mx-auto px-4 py-1 bg-red-100 text-red-500 uppercase font-semibold rounded-full text-sm">
                {{ __('Features') }}
            </span>
        </div>

        <div class="mx-auto w-10/12 md:w-8/12 lg:w-6/12">
            <p class="intro-title font-bold">
                {!! setting('websiteFeatureTitle', '') !!}
            </p>

            <p class="lg:max-w-84 intro-subtitle">
                {!! nl2br(setting('websiteFeatureSubtitle', '')) !!}
            </p>
            <!-- cards -->
            <div class="mx-auto my-8 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <!-- card 1 -->
                @php
                    $features = setting('websiteFeatures', '[]');
                    $features = json_decode($features);
                @endphp
                @foreach ($features ?? [] as $feature)
                    <div class="card">
                        <!-- img -->
                        <img src="{{ $feature->image_url ?? '' }}" class="rounded-xl max-h-[25vh] w-full object-cover">
                        <!-- title -->
                        <p class="card-title">
                            {{ $feature->title }}
                        </p>
                        <!-- subtitle -->
                        <p class="card-subtitle">
                            {{ $feature->description }}
                        </p>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

    <!-- register section -->
    <div class="other-section bg-gray-50  justify-center py-20 text-black" id="features">

        <!-- header -->
        <div class="flex justify-center items-center">
            <p class="text-center font-bold text-3xl mb-20 hover:animate-bounce cursor-pointer ">
                <span class="underline">{{ __('Join') }}</span> {{ __('Us Today') }}
            </p>
        </div>


        <!-- driver -->
        <div class="mx-auto w-10/12 md:w-8/12 lg:w-8/12">

            <div class="block md:hidden">
                <img src="{{ getValidValue(setting('websiteDriverJoinImage'), asset('images/website/driver.png')) }}"
                    class="mx-auto max-h-[30vh] mb-4" id="action-img">
            </div>

            <div class="flex mb-2">
                <span class=" px-4 py-1 bg-red-100 text-red-500 uppercase font-semibold rounded-full text-sm">
                    {{ __('Driver') }}
                </span>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                <div class="text-start">
                    <p class="text-start intro-title font-bold">
                        {{ setting('websiteDriverJoinTitle', __('Drive for us')) }}
                    </p>
                    <div>
                        {!! setting('websiteDriverJoinDescription', '') !!}
                    </div>
                </div>

                <div class="hidden md:block">
                    <img src="{{ getValidValue(setting('websiteDriverJoinImage'), asset('images/website/driver.png')) }}"
                        class="mx-auto max-h-[75vh]" id="action-img">
                </div>

            </div>

        </div>

        <div class="h-[10vh]"></div>
        <!-- vendor -->
        <div class="mx-auto w-10/12 md:w-8/12 lg:w-8/12">


            <div class="grid grid-cols-1 gap-8 md:grid-cols-2">

                <div class="">
                    <img src="{{ getValidValue(setting('websiteVendorJoinImage'), asset('images/website/store.png')) }}"
                        class="mx-auto max-h-[30vh] md:max-h-[75vh]" id="action-img">
                </div>

                <div class="text-start">
                    <div class="flex mb-2">
                        <span class=" px-4 py-1 bg-red-100 text-red-500 uppercase font-semibold rounded-full text-sm">
                            {{ __('Vendor') }}
                        </span>
                    </div>
                    <p class="text-start intro-title font-bold">
                        {{ setting('websiteVendorJoinTitle', __('Sell with us')) }}
                    </p>
                    <div>
                        {!! setting('websiteVendorJoinDescription', '') !!}
                    </div>
                </div>
            </div>



        </div>

    </div>
    </div>
    <!-- about section -->
    <div class="other-section bg-gray-50 text-black justify-center py-20 " id="about-us">

        <div class="mx-auto w-11/12 md:w-10/12 lg:w-8/12">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="mx-auto">
                    <img src="{{ getValidValue(setting('websiteAboutUsImage'), asset('images/website/aboutus.png')) }}"
                        class="rounded-xl max-h-[25vh] md:max-h-[75vh]" id="action-img">
                </div>
                <div class="text-start">
                    <p class="text-start intro-title font-bold">{{ __('About Us') }}</p>
                    <div>
                        {!! setting('websiteAboutUs', '') !!}
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- contact section -->
    <div class="other-section bg-gray-50 justify-center py-20 text-black" id="contact-us">

        <div class="mx-auto w-11/12 md:w-10/12 lg:w-8/12">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="mx-auto md:hidden">
                    <img src="{{ getValidValue(setting('websiteContactUsImage'), asset('images/website/contactus.png')) }}"
                        class="rounded-xl max-h-[25vh]">
                </div>
                <div class="text-start">
                    <p class="text-start intro-title font-bold">{{ __('Contact Us') }}</p>
                    <div>
                        {!! setting('websiteContactUs', '') !!}
                    </div>

                </div>
                <div class="mx-auto hidden md:block">
                    <img src="{{ setting('website.imgs.contactus', asset('images/website/contactus.png')) }}"
                        class="rounded-xl max-h-[25vh]">
                </div>
            </div>
        </div>
    </div>

    <!-- faqs -->
    <div class="faq-section justify-center py-20 text-black" id="faqs">
        <div class="mx-auto w-10/12 md:w-8/12 lg:w-6/12">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <img src="{{ setting('websiteFooterImage', asset('images/website/device-portrait.png')) }}"
                        class="rounded-xl max-h-[75vh]">
                </div>
                <div class="text-start">
                    <p class="text-start intro-title font-bold">FAQs</p>
                    <p>
                        Need help? Check out our FAQs to find answers to your questions.
                    </p>
                    <!-- list -->
                    <div class="my-4 space-y-1">
                        @php
                            $faqs = \App\Models\Faq::active()->limit(10)->get();
                        @endphp
                        @foreach ($faqs ?? [] as $faq)
                            <div class="cursor-pointer hover:shadow rounded overflow-hidden"
                                style="background: {{ getContrastColor($faqColor) }};color:{{ $faqColor }} !important;"
                                x-data="{ openBody: false }">
                                <div class="px-4 py-2 flex space-x-1" x-on:click="openBody = !openBody">
                                    <p class="w-full">
                                        {{ $faq->title }}
                                    </p>
                                    <!-- arrow icon -->
                                    <i x-show="!openBody" class="fa-solid fa-angle-down my-auto"></i>
                                    <i x-show="openBody" class="fa-solid fa-angle-up my-auto"></i>

                                </div>
                                <div style="background: {{ getContrastColor($faqColor) }};color:{{ $faqColor }} !important;"
                                    x-show="openBody" class="border-t px-4 py-2 text-sm">
                                    {!! $faq->body ?? '' !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

    </div>


    <!-- footer section -->
    <div class="footer-section mb-24">

        <div class="block lg:flex items-center justify-between">
            <div class="text-start w-full lg:max-w-4/12 my-16">
                <div class="flex space-x-2 items-center mb-4">
                    <img src="{{ appLogo() }}" alt="Glover Logo" class="h-8" />
                    <p>{{ setting('websiteName', env('APP_NAME')) }}</p>
                </div>

                <p class="font-thin text-xs w-full lg:w-8/12">
                    {!! nl2br(e(setting('websiteFooterBrief', ''))) !!}
                </p>

                <!-- social icons -->
                <div class="flex items-center space-x-3 font-light mt-5">
                    <a href="{{ setting('social.twLink', '') }}" class="social-icon">
                        <i class="lab la-twitter text-sm lg:text-xl"></i>
                    </a>
                    <a href="{{ setting('social.fbLink', '') }}" class="social-icon">
                        <i class="lab la-facebook text-sm lg:text-xl"></i>
                    </a>
                    <a href="{{ setting('social.igLink', '') }}" class="social-icon">
                        <i class="lab la-instagram text-sm lg:text-xl"></i>
                    </a>
                    <a href="{{ setting('social.yuLink', '') }}" class="social-icon">
                        <i class="lab la-youtube text-sm lg:text-xl"></i>
                    </a>
                </div>

            </div>

            <!-- links -->
            <div class="grid gap-4 grid-cols-1 lg:grid-cols-3 w-full lg:w-7/12  no-li-icon">
                <div>
                    <p class="text-lg font-bold">Quick Links</p>
                    <ul class="space-y-2">
                        <li>
                            <a href="#" class="text-sm">Home</a>
                        </li>
                        <li>
                            <a href="#features" class="text-sm">Features</a>
                        </li>

                        <li>
                            <a href="#about-us" class="text-sm">About Us</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <p class="text-lg font-bold">Support</p>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('privacy') }}" class="text-sm">Privacy Policy</a>
                        </li>
                        <li>
                            <a href="{{ route('terms') }}" class="text-sm">Terms and condition</a>
                        </li>
                        <li>
                            <a href="#contact-us" class="text-sm">Contact Us</a>
                        </li>
                        <li>
                            <a href="#faqs" class="text-sm">FAQ</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <p class="text-lg font-bold">Partner</p>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('register.driver') }}" class="text-sm">Driver Sign-Up</a>
                        </li>
                        <li>
                            <a href="{{ route('register.vendor') }}" class="text-sm">Store/Vendor Sign-Up</a>
                        </li>
                    </ul>
                </div>

            </div>
        </div>


        <hr class="border-0 h-[0.95px] bg-gray-700 my-4 rounded-full" />
        <div class="text-center my-4 mt-8">
            <div>
                <p>
                    &copy; {{ date('Y') }} {{ setting('websiteName', env('APP_NAME')) }}.
                    {{ __('All rights reserved.') }}
                </p>
            </div>

        </div>
    </div>



    <!-- scroll to top -->
    <button class="fixed bottom-6 right-6 rounded-full border bg-primary-600 px-4 py-3 text-white hidden"
        id="scroll-top">
        <i class="las la-arrow-up"></i>
    </button>



    <!-- scripts -->
    <script>
        // on ready
        document.addEventListener('DOMContentLoaded', function() {
            // scroll to top
            const scrollTop = document.getElementById('scroll-top');
            scrollTop.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            // show scroll top button when scrolled about 400px
            window.addEventListener('scroll', function() {
                if (window.scrollY > 400) {
                    scrollTop.classList.remove('hidden');
                    scrollTop.classList.add('animate-bounce');
                } else {
                    scrollTop.classList.remove('animate-bounce');
                    scrollTop.classList.add('hidden');
                }
            });



            //small device open menu handler
            document.getElementById("smallMenuOpenBtn").addEventListener("click", function() {
                const smallMenu = document.getElementById('sm-menu');
                smallMenu.classList.toggle('hidden');
                smallMenu.classList.toggle('block');
            });

            document.getElementById("smallMenuCloseBtn").addEventListener("click", function() {
                const smallMenu = document.getElementById('sm-menu');
                smallMenu.classList.toggle('hidden');
                smallMenu.classList.toggle('block');
            });

            // Add click event to all <a> elements within the menu
            const links = document.querySelectorAll('a');
            links.forEach(link => {
                link.addEventListener("click", function() {
                    const smallMenu = document.getElementById('sm-menu');
                    smallMenu.classList.add('hidden');
                    smallMenu.classList.remove('block');
                });
            });

        });
    </script>

</body>

</html>
