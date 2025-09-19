<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ session('theme') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'finlia.sk') }}</title>

    <meta name="robots" content="noindex, nofollow">

    <!-- favicon icon -->
    <link rel="shortcut icon" href="{{asset('images/icon.svg')}}">
    <link rel="apple-touch-icon" href="{{asset('images/icon.svg')}}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{asset('images/icon.svg')}}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{asset('images/icon.svg')}}">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body>
<div class="image-container" style="position: absolute; height: 500px; width: 100vw; top: 0; z-index: -1;">
    <img src="{{ asset('images/custom/shape-waves.png') }}" alt="background image" class="image-with-mask"
         style="width: 100%; height: 100%; object-fit: cover; pointer-events: none;">
</div>
<div id="preloader" class="position-fixed w-100 h-100" style="z-index: 50000; display: none">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="spinner-border">
            <span class="visually-hidden">{{ __('Loading') }}...</span>
        </div>
    </div>
</div>
<div id="app">
    <div class="row g-0">
        <div class="offcanvas offcanvas-fixed offcanvas-end {{ (session('sidebar') == 'expanded') ? 'show' : '' }}" data-bs-backdrop="false" data-bs-scroll="true"  tabindex="-1" id="offcanvasNavbarMenu" aria-labelledby="offcanvasNavbarMenuLabel">
            <div class="offcanvas-header flex-column p-0">
                <div class="d-flex align-items-center justify-content-start gap-2 p-3 w-100">
                    @guest
                        <div style="height: 39px"></div>
                    @else
                    <div>
                        <a class="nav-link shadow-none fw-bold p-0" data-bs-toggle="collapse" href="#offsetMenuAccountCollapse" role="button" aria-expanded="false" aria-controls="offsetMenuAccountCollapse">
                            <div class="d-flex flex-column">
                                <span class="text-truncate" style="max-width: 236px">{{ optional(Auth::user()->activeBroker)->fullname ?? Auth::user()->fullname }}</span>
                                <span class="text-secondary text-truncate" style="max-width: 236px;font-size: 10px; letter-spacing: 1px;">
                                    @if(optional(Auth::user()->activeBroker))
                                        {{ optional(Auth::user()->activeBroker)->broker_type ?? '' }}
                                        @if(optional(Auth::user()->activeBroker)->career_id)<span class="fw-light">({{ optional(Auth::user()->activeBroker)->career_id ?? '' }}) | </span>@endif
                                    @endif
                                    <span class="fw-light">{{ Auth::user()->email }}</span>
                                </span>
                            </div>
                        </a>
                    </div>
                    @endguest
                    <button type="button" class="btn-close py-2 ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                @guest
                @else
                @if(Auth::user()->hasVerifiedEmail() && ($authBrokers = Auth::user()->brokers()->activeCareer()->withCompleteName()->get())->isNotEmpty())
                    <div class="collapse w-100 p-3 pt-0" id="offsetMenuAccountCollapse">
                        <ul class="navbar-nav justify-content-end flex-grow-1">
                            @foreach($authBrokers as $authBroker)
                                <li class="nav-item">
                                    <button class="nav-link text-start {{ $authBroker->id == optional(Auth::user()->activeBroker)->id ? 'active' : '' }}" onclick="setActiveBroker('{{ $authBroker->id }}')" style="max-width: 380px">
                                        <div class="d-flex flex-column">
                                            <span>{{ $authBroker->fullname }}</span>
                                            <span class="fw-bold text-secondary" style="font-size: 10px; letter-spacing: 1px;">{{ $authBroker->broker_type ?? '' }} <span class="fw-light">({{ $authBroker->career_id ?? '' }})</span></span>
                                        </div>

                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @endguest
                <hr class="my-0 w-100"/>
            </div>
            <div class="offcanvas-body p-0">
                @guest
                @else
                    <ul class="navbar-nav justify-content-end flex-grow-1 p-3">
                        <li class="nav-item">
                            <a class="nav-link {{ routeCompare('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-collection fw-bold me-2"></i>
                                {{ __('Dashboard') }}
                            </a>
                        </li>
                        @can('client.client.read')
                            <li class="nav-item">
                                <a class="nav-link {{ routeCompare('client.client') ? 'active' : '' }}" href="{{ route('client.client.index') }}">
                                    <i class="bi bi-people fw-bold me-2"></i>
                                    {{ __('Clients') }}
                                </a>
                            </li>
                        @endcan
                        @can('contract.contract.read')
                            <li class="nav-item">
                              <a class="nav-link {{ routeCompare('contract.contract') ? 'active' : '' }}" href="{{ route('contract.contract.index') }}">
                                    <i class="bi bi-journals fw-bold me-2"></i>
                                    {{ __('Contracts') }}
                                </a>
                            </li>
                        @endcan
                        @can('intervention.intervention.read')
                            <li class="nav-item">
                                <a class="nav-link {{ routeCompare('intervention.intervention') ? 'active' : '' }}" href="{{ route('intervention.intervention.index') }}">
                                    <i class="bi bi-exclamation-circle fw-bold me-2"></i>
                                    {{ __('Interventions') }}
                                    @php
                                        $interventionCount = \App\Plugins\Intervention\app\Models\Intervention::userInterventions()->where('status', 'pending')->count();
                                    @endphp
                                    @if($interventionCount > 0)
                                        <span class="badge bg-danger ms-2" style="font-size: 8px; position: relative; top: -2px; padding: 3px 6px;">
                                            {{ $interventionCount }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endcan
                        @can('commission.broker_commission.read')
                            <li class="nav-item">
                                <a class="nav-link {{ routeCompare('commission.broker-commission') ? 'active' : '' }}" href="{{ route('commission.broker-commission.index') }}">
                                    <i class="bi bi-cash-stack fw-bold me-2"></i>
                                    {{ __('Commissions') }}
                                </a>
                            </li>
                        @endcan
                        @can('commission.storno_commission.read')
                            <li class="nav-item">
                                <a class="nav-link {{ routeCompare('commission.storno-commission') ? 'active' : '' }}" href="{{ route('commission.storno-commission.index') }}">
                                    <i class="bi bi-coin fw-bold me-2"></i>
                                    {{ __('Stornos') }}
                                </a>
                            </li>
                        @endcan
                        @can('closure.closure.read')
                            <li class="nav-item">
                                <a class="nav-link {{ routeCompare('closure.closure') ? 'active' : '' }}" href="{{ route('closure.closure.index') }}">
                                    <i class="bi bi-file-earmark-lock2 fw-bold me-2"></i>
                                    {{ __('Closures') }}
                                </a>
                            </li>
                        @endcan
                        @canany(['user.division.read', 'user.broker.read', 'complaint.complaint.update', 'intervention.intervention.create', 'commission.sfa_commission.read', 'closure.closure.create', 'elearning.course.create', 'drive.file.read'])
                        <h5 class="text-secondary text-uppercase pt-3" style="font-size: 10px;letter-spacing: 1px">{{ __('Administration') }}</h5>
                        @endcanany
                        @can('user.division.read')
                            @if(count($broker_divisions = \App\Plugins\User\app\Models\Division::brokerDivision(null, true)->pluck('id')) == 1)
                                <li class="nav-item">
                                    <a class="nav-link {{ routeCompare('user.division') ? 'active' : '' }}" href="{{ route('user.division.show', $broker_divisions->first()) }}">
                                        <i class="bi bi-diagram-2 fw-bold me-2"></i>
                                        {{ __('Division') }}
                                    </a>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a class="nav-link {{ routeCompare('user.division') ? 'active' : '' }}" href="{{ route('user.division.index') }}">
                                        <i class="bi bi-diagram-2 fw-bold me-2"></i>
                                        {{ __('Divisions') }}
                                    </a>
                                </li>
                            @endif
                        @endcan
                        @can('user.broker.read')
                            <li class="nav-item">
                                <a class="nav-link {{ routeCompare('user.broker') ? 'active' : '' }}" href="{{ route('user.broker.index') }}">
                                    <i class="bi bi-person-vcard fw-bold me-2"></i>
                                    {{ __('Brokers') }}
                                </a>
                            </li>
                        @endcan
                        @can('complaint.complaint.update')
                            <li class="nav-item">
                                <a class="nav-link {{ routeCompare('complaint.admin-complaint') ? 'active' : '' }}" href="{{ route('complaint.admin-complaint.index') }}">
                                    <i class="bi bi-question-circle fw-bold me-2"></i>
                                    {{ __('Complaints') }}
                                    @php
                                        $complaintCount = \App\Plugins\Complaint\app\Models\Complaint::brokerComplaints(null, true)->where('status', 'pending')->count();
                                    @endphp
                                    @if($complaintCount > 0)
                                        <span class="badge bg-danger ms-2" style="font-size: 8px; position: relative; top: -2px; padding: 3px 6px;">
                                            {{ $complaintCount }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endcan
                        @can('intervention.intervention.create')
                            <li class="nav-item">
                                <a class="nav-link {{ routeCompare('intervention.admin-intervention') ? 'active' : '' }}" href="{{ route('intervention.admin-intervention.index') }}">
                                    <i class="bi bi-exclamation-circle fw-bold me-2"></i>
                                    {{ __('Interventions') }}
                                </a>
                            </li>
                        @endcan
                        @can('commission.sfa_commission.read')
                            <li class="nav-item">
                                <a class="nav-link {{ routeCompare('commission.sfa-commission') ? 'active' : '' }}" href="{{ route('commission.sfa-commission.index') }}">
                                    <i class="bi bi-cash-coin fw-bold me-2"></i>
                                    {{ __('SfaCommissions') }}
                                </a>
                            </li>
                        @endcan
                        @can('closure.closure.create')
                            <li class="nav-item">
                                <a class="nav-link {{ routeCompare('closure.admin-closure') ? 'active' : '' }}" href="{{ route('closure.admin-closure.index') }}">
                                    <i class="bi bi-file-earmark-lock2 fw-bold me-2"></i>
                                    {{ __('Closures') }}
                                </a>
                            </li>
                        @endcan
                        <h5 class="text-secondary text-uppercase pt-3" style="font-size: 10px;letter-spacing: 1px">{{ __('Applications') }}</h5>
                        @can('elearning.course.study')
                            <li class="nav-item">
                                <a class="nav-link {{ routeCompare('elearning.study') ? 'active' : '' }}" href="{{ route('elearning.study.index') }}">
                                    <i class="bi bi-mortarboard me-2"></i>
                                    {{ __('Education') }}
                                </a>
                            </li>
                        @endcan
                        @can('reminder.notification_template.read')
                            <li class="nav-item">
                                <a class="nav-link {{ routeCompare('reminder.notification-template') ? 'active' : '' }}" href="{{ /*TODO route('reminder.notification-template.index')*/'' }}">
                                    <i class="bi bi-alarm me-2"></i>
                                    {{ __('Reminders') }}
                                </a>
                            </li>
                        @endcan
                        @can('drive.file.read')
                            <li class="nav-item">
                                <a class="nav-link {{ routeCompare('drive.file') ? 'active' : '' }}" href="{{ route('drive.file.index') }}">
                                    <i class="bi bi-folder fw-bold me-2"></i>
                                    {{ __('Drive') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                @endguest
            </div>
            <div class="offcanvas-footer">
                <hr class="my-0"/>
                @guest
                    @if (Route::has('login'))
                    <ul class="navbar-nav justify-content-end flex-grow-1 p-3">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">
                                    <i class="bi bi-box-arrow-in-right fw-bold me-2"></i>
                                    {{ __('Login') }}
                                </a>
                            </li>
                        </ul>
                    @endif
                @else
                    <div class="collapse" id="offsetMenuSettingsCollapse">
                        <ul class="navbar-nav justify-content-end flex-grow-1 p-3 pb-0">
                            <li class="nav-item">
                                <a class="nav-link {{ routeCompare('profile') ? 'active' : '' }}" href="{{ route('profile.show', [auth()->id()]) }}">
                                    {{ __('Profile') }}
                                </a>
                            </li>
                            @can('product.product.read')
                                <li class="nav-item">
                                    <a class="nav-link {{ routeCompare('product.product') ? 'active' : '' }}" href="{{ route('product.product.index') }}">
                                        {{--                                        <i class="bi bi-boxes fw-bold me-2"></i>--}}
                                        {{ __('Products') }}
                                    </a>
                                </li>
                            @endcan
                            @can('closure.partner_import.read')
                                <li class="nav-item">
                                    <a class="nav-link {{ routeCompare('closure.partner-import.index') ? 'active' : '' }}" href="{{ route('closure.partner-import.index') }}">
                                        {{ __('PartnerImports') }}
                                    </a>
                                </li>
                            @endcan
                            @can('elearning.course.create')
                                <li class="nav-item">
                                    <a class="nav-link {{ routeCompare('elearning.course') ? 'active' : '' }}" href="{{ route('elearning.course.index') }}">
                                        {{--                                        <i class="bi bi-easel me-2"></i>--}}
                                        {{ __('Courses') }}
                                    </a>
                                </li>
                            @endcan
                            @can('system.user.read')
                                <li class="nav-item">
                                    <a class="nav-link {{ routeCompare('system.user.index') ? 'active' : '' }}" href="{{ route('system.user.index') }}">
                                        {{ __('Users') }}
                                    </a>
                                </li>
                            @endcan
                            @can('contact_form.idea.read')
                                <li class="nav-item">
                                    <a class="nav-link {{ routeCompare('contact-form.idea.index') ? 'active' : '' }}" href="{{ route('contact-form.idea.index') }}">
                                        {{ __('Ideas') }}
                                    </a>
                                </li>
                            @endcan
                            @can('system.system_setting.read')
                                <li class="nav-item">
                                    <a class="nav-link {{ routeCompare('system.system-setting.index') ? 'active' : '' }}" href="{{ route('system.system-setting.index') }}">
                                        {{ __('System settings') }}
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                    <ul class="navbar-nav justify-content-end flex-grow-1 p-3" style="margin-bottom: -5px/* aby sedela vyska voci footeru*/">
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="collapse" href="#offsetMenuSettingsCollapse" role="button" aria-expanded="false" aria-controls="offsetMenuSettingsCollapse">
                                <i class="bi bi-gear fw-bold me-2"></i>
                                {{ __('Settings') }}
                            </a>
                        </li>
                    </ul>
                    <ul class="navbar-nav justify-content-end flex-grow-1 p-3">
                        <a class="nav-link" href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                                                    document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right fw-bold me-2"></i>
                            {{ __('Logout') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </ul>
                @endguest
            </div>
        </div>
        <div class="col d-flex flex-column position-relative" style="min-height: 100vh">
            @if(!App::isProduction())
            <div class="position-absolute" style="left: 10px; z-index: 1060;">
                <button onclick="databaseReset()" type="button" class="btn btn-danger position-fixed rounded-pill d-flex justify-content-center align-items-center shadow" style="width: 48px;height: 48px; bottom: 50px;">
                    <i class="bi bi-trash3-fill fs-5"></i>
                </button>
            </div>
            <script>
                function databaseReset() {
                    confirm(null, (result) => {
                        if (result.isConfirmed) {
                            ajax({
                                url: '{{ route('api.admin.database.reset') }}',
                            }, (response) => {
                                success(response.message, null, null, 2000, () => location.reload())
                            })
                        }
                    }, 'Obnoviť databázu?', null, '{{ __('Delete') }}', null, null, {
                        customClass: {
                            confirmButton: 'btn btn-danger mx-1',
                            cancelButton: 'btn btn-outline-secondary mx-1'
                        }
                    });
                }
            </script>
            @endif
            <nav id="appNavigation" class="navbar sticky-top-custom px-lg-3 blurred-background" style="min-height: 99.40625px; !important;">
                <div class="container-lg justify-content-center column-gap-2 flex-nowrap">
                    <a class="navbar-brand justify-content-center" href="{{ route('home') }}">
                        <img src="{{ session()->get('theme') == 'dark' ? asset('images/logo.svg') : asset('images/logo_dark.svg') }}" alt="{{ config('app.name') }} Logo" style="width: 100px; object-fit: contain;" class="d-inline-block align-text-top">
                        <img id="navbar-brand-on-scroll" src="{{ asset('images/logo_dark.svg') }}" alt="{{ config('app.name') }} Logo" style="width: 100px; object-fit: contain;" class="d-inline-block align-text-top">
                    </a>
                    <ul class="navbar-nav d-none d-lg-flex flex-grow-1">
                        @can('client.client.read')
                            <li class="nav-item">
                                <a href="{{ route('client.client.index') }}" class="nav-link {{ routeCompare('client.client') ? 'active' : '' }}">{{ __('Clients') }}</a>
                            </li>
                        @endcan
                        @can('contract.contract.read')
                            <li class="nav-item">
                                <a href="{{ route('contract.contract.index') }}" class="nav-link {{ routeCompare('contract.contract') ? 'active' : '' }}">{{ __('Contracts') }}</a>
                            </li>
                        @endcan
                        @can('intervention.intervention.read')
                            <li class="nav-item">
                                <a href="{{ route('intervention.intervention.index') }}" class="nav-link {{ routeCompare('intervention.intervention') ? 'active' : '' }}">
                                    {{ __('Interventions') }}
                                    @php
                                        $interventionCount = \App\Plugins\Intervention\app\Models\Intervention::userInterventions()->where('status', 'pending')->count();
                                    @endphp
                                    @if($interventionCount > 0)
                                        <span class="badge bg-danger" style="font-size: 8px; position: relative; left: -6px; top: -8px; padding: 3px 6px;">
                                            {{ $interventionCount }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endcan
                        @can('commission.broker_commission.read')
                            <li class="nav-item">
                                <a href="{{ route('commission.broker-commission.index') }}" class="nav-link {{ routeCompare('commission.broker-commission') ? 'active' : '' }}">
                                    {{ __('Commissions') }}
                                </a>
                            </li>
                        @endcan
                        @can('commission.storno_commission.read')
                            <li class="nav-item">
                                <a href="{{ route('commission.storno-commission.index') }}" class="nav-link {{ routeCompare('commission.storno-commission') ? 'active' : '' }}">
                                    {{ __('Stornos') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                    <div class="ms-auto d-flex align-items-center gap-2" style="min-width: 0;">
                        @guest
                            @if (Route::has('login'))
                                <a class="navbar-custom-btn btn btn-primary" href="{{ route('login') }}">{{ __('Login') }}</a>
                            @endif
                        @else
{{--                            <div class="dropdown">--}}
                                <a href="{{ route('profile.show', [auth()->id()]) }}" class="navbar-custom-btn btn btn-primary d-flex align-items-center flex-shrink-1 overflow-hidden" {{--data-bs-toggle="dropdown" aria-expanded="false"--}}>
                                    <i class="bi bi-person-circle position-relative d-none d-sm-block">
                                        @if(Auth::user()->profile_photo_path)
                                            <x-profile-photo class="me-2 position-absolute start-50 top-50 translate-middle" :user="auth()->user()/*Auth::user()->profile_photo_path ? Auth::user() : (Auth::user()->activeBroker->name ?? Auth::user())*/" alt="{{ __('forms.profile_photo_path') }}" width="29.5" height="29.5" style="margin-top: -1px;margin-left: -0px"></x-profile-photo>
                                        @endif
                                    </i>
                                    <span class="text-truncate">
                                        {{ optional(Auth::user()->activeBroker)->firstname ?? Auth::user()->firstname }}
                                    </span>
                                </a>
{{--                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">--}}
{{--                                    <li><h6 class="dropdown-header">{{ __('Settings') }}</h6></li>--}}
{{--                                    <li><a class="dropdown-item" href="{{ route('profile.show', [auth()->id()]) }}">{{ __('Profile') }}</a></li>--}}
{{--                                    <li><hr class="dropdown-divider"></li>--}}
{{--                                    <li>--}}
{{--                                        <form action="{{ route('logout') }}" method="POST">--}}
{{--                                            @csrf--}}
{{--                                            <button type="submit" class="dropdown-item">--}}
{{--                                                <span>{{__('Logout')}}</span>--}}
{{--                                            </button>--}}
{{--                                        </form>--}}
{{--                                    </li>--}}
{{--                                </ul>--}}
{{--                            </div>--}}
                        @endguest
                        <button class="navbar-custom-btn btn btn-primary @guest d-none @endguest" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbarMenu" aria-controls="offcanvasNavbarMenu" aria-label="Toggle navigation">
                            <i class="bi bi-list"></i>
                        </button>
                    </div>
                </div>
            </nav>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col col-lg-11">
                        <div class="d-flex flex-wrap justify-content-between">
                            @yield('breadcrumb')
                        </div>
                    </div>
                </div>
            </div>
            <main class="mt-0">
                @yield('content')
            </main>
            <footer class="mt-auto">
                <div class="container py-5">
                    <div class="row justify-content-center">
                        <div class="col col-xxl-11">
                            <div class="row gy-5 mb-3 align-items-end">
                                <div class="col-12 col-lg-auto justify-content-center justify-content-lg-start text-center">
                                    <img class="img-fluid w-auto" style="height: 21px"
                                         src="{{ asset('images/logo_light.svg') }}"
                                         alt="{{ config('app.name') }}"
                                    />
                                </div>
                                <div class="col">
                                    <ul class="nav flex-column flex-md-row flex-wrap justify-content-center justify-content-lg-end text-center gap-3" style="">
                                        <li><a href="#" onclick="openContactForm(); return false;" class="nav-link p-0">Kontakt</a></li>
                                        <li><a href="{{ asset('documents/finlia_cookies_sk.pdf') }}" class="nav-link p-0">Cookies</a></li>
                                        <li><a href="{{ asset('documents/finlia_data_protection_sk.pdf') }}" class="nav-link p-0">Ochrana osobných údajov</a></li>
                                        <li><a href="{{ asset('documents/finlia_terms_of_use_sk.pdf') }}" class="nav-link p-0">Podmienky používania</a></li>
                                        <li><a href="#" class="nav-link p-0 disabled">© {{ date('Y') }} {{ config('app.name', 'finlia.sk') }}</a></li>
                                        <li>
                                            <div class="form-check form-switch form-switch-color-mode mx-auto p-0" style="width: max-content">
                                                <label class="form-check-label" for="lightSwitch"></label>
                                                <input
                                                    class="form-check-input mx-auto" type="checkbox" id="lightSwitch" {{ session('theme') == 'dark' ? '' :  'checked' }}
                                                onclick="setTheme(this)"
                                                />
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <div id="contactFormContainer" style="display: none">
        <form>
            {!! renderInputCollection(app(\App\Http\Controllers\Api\HomeApiController::class)->createFormFields('contact_form', 'idea', true, [])->getOriginalContent(), 'm-1') !!}
        </form>
    </div>
    @stack('scripts-modals')
</div>
</body>
@include('layouts.layout-scripts')
<script>
    $(window).on("scroll", function () {
        const el = $('#appNavigation');
        if ($(this).scrollTop() > 50) {
            el.addClass("active");
        } else {
            el.removeClass("active");
        }
    });
</script>
<script>
    function openContactForm() {
        const formContainer = '#contactFormContainer';
        Swal.fire({
            title: '{{ __('Contact us') }}',
            html: '',
            showCancelButton: true,
            confirmButtonText: '{{ __('Submit') }}',
            cancelButtonText: '{{ __('Cancel') }}',
            showCloseButton: true,
            didOpen: (data) => {
                $('form', formContainer).appendTo('#swal2-html-container');
                {!! JsValidator::formRequest(App\Plugins\ContactForm\app\Http\Requests\IdeaStoreRequest::class, '.swal2-container form')->view('layouts.jsvalidation') !!};
                $('[name]:not([type="hidden"])', data).first().focus();
            },
            preConfirm: () => {return $('.swal2-container form').valid()},
        }).then((result) => {
            if (result.isConfirmed) {
                ajax({
                    url: '{{ route('api.contact-form.idea.store') }}',
                    data: getFormData($('.swal2-container')),
                });
            }
            $('form', '#swal2-html-container').appendTo(formContainer);
        });
    }
</script>
<script>
    @guest
    @else
    function setActiveBroker(broker_id) {
        ajax({
            method: "PUT",
            url: '{{ route('api.user.broker.active', [':broker']) }}'.replace(':broker', broker_id),
        }, (result) => {
            if(result.success) {
                location.reload();
            }
        });
    }

    @if(Auth::user()->hasVerifiedEmail() && ((!session()->get('active_broker_id') || session()->has('logged_active_broker_id')) && $authBrokers->isNotEmpty()))
    (() => {
        let html = `<form>
            <x-forms.select :label="__('Account')" name="broker_id" class="m-1">
                @php
                    $_logged_active_broker_id = session()->pull('logged_active_broker_id');
                @endphp
                @foreach($authBrokers as $authBroker)
                    <option value="{{ $authBroker->id }}" {{ ($_logged_active_broker_id && $_logged_active_broker_id == $authBroker->id) ? 'selected' : '' }}>{{ $authBroker->complete_name }}</option>
                @endforeach
            </x-forms.select>
        </form>`;
        Swal.fire({
            title: '{{ __('Choose account') }}',
            html: html,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: (data) => {
                $('[name]:not([type="hidden"])', data).first().focus();
            }
        }).then(function (result) {
            if(result.isConfirmed) {
                let data = {};
                $.each($('[name]:not([name="proengsoft_jsvalidation"])', '.swal2-container'), function (key, value) {
                    data[$(value).attr('name')] = $(value).val();
                });

                setActiveBroker(data['broker_id'])
            }
        });
    })()
    @endif
    @endguest
</script>
@stack('scripts')
</html>
