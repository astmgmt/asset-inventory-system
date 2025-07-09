<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Asset Management') }}</title>

    <!-- FAVICON -->
    <link rel="icon" href="{{ asset('images/icon.ico') }}" type="image/x-icon">


    <!-- GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    
    <!-- FONT-AWESOME ICONS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
   
    @vite(['resources/css/app.css', 'resources/js/app.js'])  
    @livewireStyles
    
</head>
<body x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }"
      x-init="
          localStorage.getItem('theme') === 'dark' ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
          window.dispatchEvent(new CustomEvent('theme-changed', { detail: darkMode }));
          $watch('darkMode', value => {
              localStorage.setItem('theme', value ? 'dark' : 'light');
              document.documentElement.classList.toggle('dark', value);
              window.dispatchEvent(new CustomEvent('theme-changed', { detail: value }));
          });
      "
      :class="{ 'dark': darkMode }">

    <aside class="sidebar">
        <div class="sidebar-header text-center">
            <img src="{{ asset('images/logo.png') }}" alt="Asset Logo" class="logo mb-3">
            <h1 class="header-title">Asset Inventory System (AIS)</h1>
            <p class="header-subtitle">Comprehensive tracking of organizational assets</p>
        </div>

        <nav class="sidebar-nav" x-data="{ open: null }">
            <hr class="sidebar-separator" />

            @auth
                @php
                    $user = Auth::user();
                    $user->load('role');
                @endphp

                <!-- SUPER ADMIN MENUS -->
                @if($user->isSuperAdmin())

                    <!-- Home -->
                    <a href="{{ route('dashboard.superadmin') }}"><i class="fas fa-home"></i> Home</a>

                    <!-- Account Management -->
                    <button @click="open === 1 ? open = null : open = 1" class="nav-link-button" type="button">
                        <i class="fas fa-user-cog"></i> Account Management
                        <i class="fas" :class="open === 1 ? 'fa-chevron-up' : 'fa-chevron-down'" style="margin-left:auto;"></i>
                    </button>
                    <div x-show="open === 1" x-collapse class="nav-submenu">
                        <a href="{{route('superadmin.register')}}"><i class="fas fa-user-plus"></i> Create</a>
                        <a href="{{route('superadmin.manage')}}"><i class="fas fa-users-cog"></i> Manage</a>                        
                    </div>

                    <!-- Asset Management -->
                    <button @click="open === 2 ? open = null : open = 2" class="nav-link-button" type="button">
                        <i class="fas fa-boxes"></i> Asset Management
                        <i class="fas" :class="open === 2 ? 'fa-chevron-up' : 'fa-chevron-down'" style="margin-left:auto;"></i>
                    </button>
                    <div x-show="open === 2" x-collapse class="nav-submenu">
                        <a href="{{ route('manage.assets') }}"><i class="fas fa-cogs"></i> Manage Asset</a>
                        <a href="{{ route('asset.assignment') }}"><i class="fas fa-random"></i> Assign Asset</a>
                        <a href="{{ route('approve.requests') }}"><i class="fas fa-hand-paper"></i> Borrow Request(s)</a>
                        <a href="{{ route('approve.return') }}"><i class="fas fa-undo-alt"></i> Return Request(s)</a>
                        <a href="{{ route('track.assets') }}"><i class="fas fa-chart-line"></i> Track Assets</a>
                        <a href="{{ route('asset.disposal') }}"><i class="fas fa-trash-alt"></i> Dispose Asset</a>                                               
                    </div>

                    <!-- Software Management -->
                    <button @click="open === 3 ? open = null : open = 3" class="nav-link-button" type="button">
                        <i class="fas fa-laptop-code"></i> Software Management
                        <i class="fas" :class="open === 3 ? 'fa-chevron-up' : 'fa-chevron-down'" style="margin-left:auto;"></i>
                    </button>
                    <div x-show="open === 3" x-collapse class="nav-submenu">
                        <a href="{{route('manage.softwares')}}"><i class="fas fa-cogs"></i> Manage Software</a>
                        <a href="{{route('software.assignment')}}"><i class="fas fa-share-square"></i> Assign Software</a>                         
                        <a href="{{ route('track.software') }}"><i class="fas fa-chart-line"></i> Track Software</a>
                    </div>                   

                    <!-- Print Reports -->
                    <button @click="open === 4 ? open = null : open = 4" class="nav-link-button" type="button">
                        <i class="fas fa-print"></i> Print Reports
                        <i class="fas" :class="open === 4 ? 'fa-chevron-up' : 'fa-chevron-down'" style="margin-left:auto;"></i>
                    </button>
                    <div x-show="open === 4" x-collapse class="nav-submenu">
                        <a href="{{ route('print.assets') }}"><i class="fas fa-file-alt"></i> Asset Master List</a>
                        <a href="{{ route('print.softwares') }}"><i class="fas fa-file-code"></i> Software Master List</a>
                        <a href="{{ route('print.qrcodes') }}"><i class="fas fa-qrcode"></i> QRCodes</a>
                    </div>

                    <!-- My Transactions -->
                    <button @click="open === 5 ? open = null : open = 5" class="nav-link-button" type="button">
                        <i class="fas fa-exchange-alt"></i> Transactions
                        <i class="fas" :class="open === 5 ? 'fa-chevron-up' : 'fa-chevron-down'" style="margin-left:auto;"></i>
                    </button>
                    <div x-show="open === 5" x-collapse class="nav-submenu">
                        <a href="{{ route('admin.return.assets') }}"><i class="fas fa-boxes"></i> My Assets</a>
                        <a href="{{ route('admin.history.assets') }}"><i class="fas fa-history"></i> History</a>
                    </div>                   

                    <!-- Contact User -->
                    <a href="{{ route('contact.user') }}"><i class="fas fa-envelope"></i> Send Email</a>

                <!-- ADMIN MENUS -->
                @elseif($user->isAdmin())
                    <!-- Home -->
                    <a href="{{ route('dashboard.admin') }}"><i class="fas fa-home"></i> Home</a>

                    <!-- Asset Management -->
                    <button @click="open === 2 ? open = null : open = 2" class="nav-link-button" type="button">
                        <i class="fas fa-boxes"></i> Asset Management
                        <i class="fas" :class="open === 2 ? 'fa-chevron-up' : 'fa-chevron-down'" style="margin-left:auto;"></i>
                    </button>
                    <div x-show="open === 2" x-collapse class="nav-submenu">
                        <a href="{{ route('manage.assets') }}"><i class="fas fa-cogs"></i> Manage Asset</a>
                        <a href="{{ route('asset.assignment') }}"><i class="fas fa-random"></i> Assign Asset</a>
                        <a href="{{ route('approve.requests') }}"><i class="fas fa-hand-paper"></i> Borrow Request(s)</a>
                        <a href="{{ route('approve.return') }}"><i class="fas fa-undo-alt"></i> Return Request(s)</a>
                        <a href="{{ route('track.assets') }}"><i class="fas fa-chart-line"></i> Track Assets</a>
                        <a href="{{ route('asset.disposal') }}"><i class="fas fa-trash-alt"></i> Dispose Asset</a>                                               
                    </div>

                    <!-- Software Management -->
                    <button @click="open === 3 ? open = null : open = 3" class="nav-link-button" type="button">
                        <i class="fas fa-laptop-code"></i> Software Management
                        <i class="fas" :class="open === 3 ? 'fa-chevron-up' : 'fa-chevron-down'" style="margin-left:auto;"></i>
                    </button>
                    <div x-show="open === 3" x-collapse class="nav-submenu">
                        <a href="{{route('manage.softwares')}}"><i class="fas fa-cogs"></i> Manage Software</a>
                        <a href="{{route('software.assignment')}}"><i class="fas fa-share-square"></i> Assign Software</a>
                        <a href="{{ route('track.software') }}"><i class="fas fa-chart-line"></i> Track Software</a>
                    </div>                   

                    <!-- Print Reports -->
                    <button @click="open === 4 ? open = null : open = 4" class="nav-link-button" type="button">
                        <i class="fas fa-print"></i> Print Reports
                        <i class="fas" :class="open === 4 ? 'fa-chevron-up' : 'fa-chevron-down'" style="margin-left:auto;"></i>
                    </button>
                    <div x-show="open === 4" x-collapse class="nav-submenu">
                        <a href="{{ route('print.assets') }}"><i class="fas fa-file-alt"></i> Asset Master List</a>
                        <a href="{{ route('print.softwares') }}"><i class="fas fa-file-code"></i> Software Master List</a>
                        <a href="{{ route('print.qrcodes') }}"><i class="fas fa-qrcode"></i> QRCodes</a>
                    </div>

                    <!-- My Transactions -->
                    <button @click="open === 5 ? open = null : open = 5" class="nav-link-button" type="button">
                        <i class="fas fa-exchange-alt"></i> Transactions
                        <i class="fas" :class="open === 5 ? 'fa-chevron-up' : 'fa-chevron-down'" style="margin-left:auto;"></i>
                    </button>
                    <div x-show="open === 5" x-collapse class="nav-submenu">
                        <a href="{{ route('admin.return.assets') }}"><i class="fas fa-boxes"></i> My Assets</a>
                        <a href="{{ route('admin.history.assets') }}"><i class="fas fa-history"></i> History</a>
                    </div>                

                    <!-- Contact User -->
                    <a href="{{ route('contact.user') }}"><i class="fas fa-envelope"></i> Send Email</a>
                
                <!-- USER MENUS -->
                @else
                    <!-- Home -->
                    <a href="{{ route('dashboard.user') }}"><i class="fas fa-home"></i> Home</a>

                    <!-- Borrow Assets -->
                    <a href="{{ route('user.borrow.assets') }}"><i class="fas fa-hand-holding"></i> Borrow Assets</a>                   

                    <button @click="open === 5 ? open = null : open = 5" class="nav-link-button" type="button">
                        <i class="fas fa-receipt"></i> Transactions
                        <i class="fas" :class="open === 5 ? 'fa-chevron-up' : 'fa-chevron-down'" style="margin-left:auto;"></i>
                    </button>

                    <div x-show="open === 5" x-collapse class="nav-submenu">
                        <a href="{{ route('user.borrow.transactions') }}">
                            <i class="fas fa-hand-holding"></i> Borrow
                        </a>
                        <a href="{{ route('user.return.transactions') }}">
                            <i class="fas fa-undo-alt"></i> Return
                        </a>
                        <a href="{{ route('user.history') }}">
                            <i class="fas fa-history"></i> History
                        </a>
                    </div>
                    
                    <a href="{{ route('contact.admin') }}"><i class="fas fa-headset"></i> Contact Support</a>

                @endif
            @endauth
        </nav>
    </aside>

    <main class="main-content-area">
        <header class="app-header">
            <!-- Row 1: Dashboard -->
            <div class="header-row top-row">
                <h1 class="dashboard-title">@yield('title', 'Dashboard')</h1>
            </div>

            <!-- Row 2: User Info + Actions -->
            <div class="header-row bottom-row">
                @auth
                    @php
                        $user = Auth::user();
                        $user->load('role');
                    @endphp

                    <div class="user-info">
                        @if($user->profile_photo_path)
                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="{{ $user->name }}" class="user-avatar">
                        @else
                            <i class="fas fa-user-circle user-avatar"></i>
                        @endif

                        <div class="user-details">
                            <span class="user-name">{{ $user->name }}</span>
                            <span class="user-role">{{ $user->role->name ?? 'User' }}</span>
                        </div>
                    </div>

                    <a href="{{ route('account.edit.profile') }}" class="btn edit-profile-btn">
                        <i class="fas fa-user-edit"></i> Edit Profile
                    </a>

                    <form method="POST" action="{{ route('logout') }}" x-data>
                        @csrf
                        <button type="submit" class="btn logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>

                    <button 
                        @click="darkMode = !darkMode"
                        class="btn toggle-theme-button"
                        title="Toggle Theme"
                    >
                        <i :class="darkMode ? 'fas fa-sun' : 'fas fa-moon'"></i>
                    </button>

                    {{-- NOTIFICATION BELLS --}}
                    @if(auth()->check())
                        @if(auth()->user()->isUser())
                            <livewire:user.user-notification-bell />
                        @endif
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                            <livewire:super-admin.admin-notification-bell />
                        @endif
                    @endif                   

                @endauth
            </div>
        </header>

        <div class="content">
            <div class="content-card">
                {{ $slot }}
            </div>
        </div>

        <footer class="footer">
            <p>&copy; {{ date('Y') }} Asset Management Inventory System. All rights reserved.</p>
        </footer>
    </main>    
   
    @livewireScripts
</body>
</html>