<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Asset Management Inventory</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Add Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @vite('resources/css/app.css')   
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
        <div class="sidebar-header">
            <h1>Asset Management Inventory</h1>
            <p>Comprehensive tracking of organizational assets</p>
        </div>

        <nav class="sidebar-nav" x-data="{ open: null }">
            <hr class="sidebar-separator" />

            <button @click="open === 1 ? open = null : open = 1" class="nav-link-button" type="button">
                <i class="fas fa-user-cog"></i> Account Management
                <i class="fas" :class="open === 1 ? 'fa-chevron-up' : 'fa-chevron-down'" style="margin-left:auto;"></i>
            </button>
            <div x-show="open === 1" x-collapse class="nav-submenu">
                <a href="#"><i class="fas fa-user-shield"></i> Admins</a>
                <a href="#"><i class="fas fa-users"></i> Users</a>
            </div>

            <a href="#"><i class="fas fa-boxes"></i> Asset Management</a>
            <a href="#"><i class="fas fa-laptop-code"></i> Software Management</a>

            <button @click="open === 2 ? open = null : open = 2" class="nav-link-button" type="button">
                <i class="fas fa-tasks"></i> Assignment
                <i class="fas" :class="open === 2 ? 'fa-chevron-up' : 'fa-chevron-down'" style="margin-left:auto;"></i>
            </button>
            <div x-show="open === 2" x-collapse class="nav-submenu">
                <a href="#"><i class="fas fa-box"></i> Asset Assignment</a>
                <a href="#"><i class="fas fa-desktop"></i> Software</a>
            </div>

            <a href="#"><i class="fas fa-hand-holding"></i> Borrow Asset</a>
            <a href="#"><i class="fas fa-undo-alt"></i> Return Asset</a>
            <a href="#"><i class="fas fa-trash"></i> Dispose Asset</a>

            <button @click="open === 3 ? open = null : open = 3" class="nav-link-button" type="button">
                <i class="fas fa-print"></i> Print Reports
                <i class="fas" :class="open === 3 ? 'fa-chevron-up' : 'fa-chevron-down'" style="margin-left:auto;"></i>
            </button>
            <div x-show="open === 3" x-collapse class="nav-submenu">
                <a href="#">Asset Master List</a>
                <a href="#">Asset Request Form for Borrowing</a>
                <a href="#">Asset Assignment Report</a>
                <a href="#">Return Asset Report</a>
                <a href="#">History Return Asset</a>
                <a href="#">Disposed Asset Report</a>
                <a href="#">Software Inventory Report</a>
                <a href="#">Software Assignment Report</a>
                <a href="#">QRCode Sticker</a>
            </div>
        </nav>
    </aside>

    <main class="main-content-area">
        <header class="header">
            <h1>@yield('title', 'Dashboard')</h1>
            <div class="user-info-group">
                <div class="user-profile">
                    <i class="fas fa-user-circle"></i>
                    <div class="user-details">
                        <span class="user-name">John Doe</span>
                        <span class="user-role">Administrator</span>
                    </div>
                </div>
                <a href="#" class="btn btn-edit-profile"><i class="fas fa-user-edit"></i> Edit Profile</a>
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf
                    <button 
                        type="submit" 
                        class="btn btn-logout"                        
                    >
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>


                <!-- Theme Toggle Button -->
                <div>
                    <button @click="darkMode = !darkMode"
                            class="btn btn-logout"
                            title="Toggle Mode"
                            style="background-color: transparent; color: inherit; border: none; cursor: pointer;font-size: 1.5rem; border: 1px solid #b3b3b3;">
                        <i :class="darkMode ? 'fas fa-sun' : 'fas fa-moon'"></i>
                    </button>
                </div>
                
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

    @vite(['resources/js/app.js'])
    @livewireScripts
</body>
</html>
