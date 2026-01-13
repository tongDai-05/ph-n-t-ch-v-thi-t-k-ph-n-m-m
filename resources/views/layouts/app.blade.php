<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Website Bán Sách Tự Động') }}</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ route('books.index') }}">
                    Website Bán Sách
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    {{-- Left Side Of Navbar --}}
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('books.index') }}">Sách</a>
                        </li>
                    
                        {{-- PHẦN DÀNH CHO ADMIN --}}
                        @auth
                            @if (Auth::user()->role === 'admin')
                                @php
                                    // Đếm số đơn hàng khách yêu cầu hủy nhưng trạng thái chưa phải là 'cancelled'
                                    $pendingCancels = \App\Models\Order::where('cancellation_requested', true)
                                                                      ->where('status', '!=', 'cancelled')
                                                                      ->count();
                                @endphp
                                <li class="nav-item">
                                    <a class="nav-link text-danger fw-bold position-relative" href="{{ route('admin.orders.index') }}">
                                        🛠️ Quản lý Đơn hàng
                                        @if($pendingCancels > 0)
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" title="Có {{ $pendingCancels }} yêu cầu hủy mới">
                                                {{ $pendingCancels }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-primary fw-bold" href="{{ route('admin.dashboard') }}">
                                        📊 Thống kê doanh thu
                                    </a>
                                </li>
                            @endif
                        @endauth
                    </ul>

                    {{-- Right Side Of Navbar --}}
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('cart.index') }}">
                                🛒 Giỏ hàng
                            </a>
                        </li>
                        
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('orders.history') }}">
                                    Lịch sử Đơn hàng
                                </a>
                            </li>

                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>