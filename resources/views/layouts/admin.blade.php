<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - RANTS Admin</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo_rants.png') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        
        /* Fixed Sidebar */
        .sidebar-fixed {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 16rem;
            overflow-y: auto;
            z-index: 30;
            transition: transform 0.3s ease, width 0.3s ease;
        }
        
        /* Sidebar collapsed state */
        .sidebar-fixed.collapsed {
            width: 5rem;
        }
        
        /* Hide text when collapsed */
        .sidebar-fixed.collapsed .menu-text {
            display: none;
        }
        
        .sidebar-fixed.collapsed .logo-text {
            display: none;
        }
        
        /* Resize logo when collapsed */
        .sidebar-fixed.collapsed img {
            width: 2.5rem;
            height: auto;
        }
        
        /* Center icons when collapsed */
        .sidebar-fixed.collapsed nav a {
            justify-content: center;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }
        
        /* Hide scrollbar but keep functionality */
        .sidebar-fixed::-webkit-scrollbar {
            width: 0px;
            background: transparent;
        }
        
        .sidebar-fixed {
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
        }
        
        /* Fixed Header */
        .header-fixed {
            position: fixed;
            top: 0;
            left: 16rem;
            right: 0;
            z-index: 20;
            transition: left 0.3s ease;
        }
        
        .sidebar-fixed.collapsed ~ .main-content .header-fixed {
            left: 5rem;
        }
        
        /* Main Content with proper spacing */
        .main-content {
            margin-left: 16rem;
            padding-top: 5rem;
            transition: margin-left 0.3s ease;
        }
        
        .sidebar-fixed.collapsed ~ .main-content {
            margin-left: 5rem;
        }
        
        /* Toggle button */
        .sidebar-toggle {
            position: fixed;
            top: 1.5rem;
            left: 15rem;
            z-index: 40;
            background: #7f1d1d;
            color: white;
            border: none;
            border-radius: 0.5rem;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: left 0.3s ease, transform 0.2s ease;
        }
        
        .sidebar-toggle.collapsed {
            left: 4rem;
        }
        
        .sidebar-toggle:hover {
            background: #991b1b;
            transform: scale(1.1);
        }
        
        .sidebar-toggle svg {
            transition: transform 0.3s ease;
        }
        
        .sidebar-toggle.rotate-180 svg {
            transform: rotate(180deg);
        }
        
        .sidebar-toggle.collapsed {
            left: 4rem;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Toggle Button -->
    <button onclick="toggleSidebar()" class="sidebar-toggle" id="sidebarToggle">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <div class="min-h-screen">
        <!-- Sidebar -->
        <aside class="sidebar-fixed bg-gradient-to-b from-red-900 to-red-800 text-white" id="sidebar">
            <div class="p-6 flex flex-col items-center">
                <img src="{{ asset('images/logo_rants.png') }}" alt="RANTS Logo" class="w-24 h-auto mb-2">
                <p class="text-white text-sm font-semibold logo-text">RANTS Dashboard</p>
            </div>
            
            <nav class="mt-0 pb-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-6 py-3 hover:bg-red-700 transition {{ request()->routeIs('admin.dashboard') ? 'bg-red-700 border-l-4 border-white' : '' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="menu-text">Dashboard</span>
                </a>
                
                <a href="{{ route('admin.costumes.index') }}" class="flex items-center px-6 py-3 hover:bg-red-700 transition {{ request()->routeIs('admin.costumes.*') ? 'bg-red-700 border-l-4 border-white' : '' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    <span class="menu-text">Kostum</span>
                </a>
                
                <a href="{{ route('admin.dance-services.index') }}" class="flex items-center px-6 py-3 hover:bg-red-700 transition {{ request()->routeIs('admin.dance-services.*') ? 'bg-red-700 border-l-4 border-white' : '' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="menu-text">Jasa Tari</span>
                </a>
                
                <a href="{{ route('admin.makeup-services.index') }}" class="flex items-center px-6 py-3 hover:bg-red-700 transition {{ request()->routeIs('admin.makeup-services.*') ? 'bg-red-700 border-l-4 border-white' : '' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="menu-text">Jasa Rias</span>
                </a>
                
                <a href="{{ route('admin.orders.index') }}" class="flex items-center px-6 py-3 hover:bg-red-700 transition {{ request()->routeIs('admin.orders.*') ? 'bg-red-700 border-l-4 border-white' : '' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span class="menu-text">Pemesanan</span>
                </a>
                
                <a href="{{ route('admin.transactions.index') }}" class="flex items-center px-6 py-3 hover:bg-red-700 transition {{ request()->routeIs('admin.transactions.*') ? 'bg-red-700 border-l-4 border-white' : '' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="menu-text">Transaksi</span>
                </a>
                
                <a href="{{ route('admin.gallery.index') }}" class="flex items-center px-6 py-3 hover:bg-red-700 transition {{ request()->routeIs('admin.gallery.*') ? 'bg-red-700 border-l-4 border-white' : '' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="menu-text">Galeri</span>
                </a>
                
                <a href="{{ route('admin.users.index') }}" class="flex items-center px-6 py-3 hover:bg-red-700 transition {{ request()->routeIs('admin.users.*') ? 'bg-red-700 border-l-4 border-white' : '' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="menu-text">Pengguna</span>
                </a>

                <a href="{{ route('admin.schedule.index') }}" class="flex items-center px-6 py-3 hover:bg-red-700 transition {{ request()->routeIs('admin.schedule.*') ? 'bg-red-700 border-l-4 border-white' : '' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="menu-text">Jadwal</span>
                </a>

                <a href="{{ route('admin.chat-support.index') }}" class="flex items-center px-6 py-3 hover:bg-red-700 transition {{ request()->routeIs('admin.chat-support.*') ? 'bg-red-700 border-l-4 border-white' : '' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <span class="menu-text">Chat Support</span>
                    @php
                        $pendingChats = \App\Models\ChatSession::where('status', 'human_requested')->count();
                    @endphp
                    @if($pendingChats > 0)
                        <span class="ml-auto bg-yellow-500 text-white text-xs px-2 py-1 rounded-full font-medium menu-text">{{ $pendingChats }}</span>
                    @endif
                </a>

                <div class="mx-6 my-4 border-t border-red-700 logo-text"></div>

                <a href="{{ route('admin.profile.index') }}" class="flex items-center px-6 py-3 hover:bg-red-700 transition {{ request()->routeIs('admin.profile.*') ? 'bg-red-700 border-l-4 border-white' : '' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="menu-text">Profil Saya</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="main-content" id="mainContent">
            <!-- Header -->
            <header class="header-fixed bg-white shadow-sm" id="header">
                <div class="flex items-center justify-between px-8 py-4">
                    <h2 class="text-2xl font-semibold text-gray-800">@yield('page-title')</h2>
                    
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">{{ auth()->user()->name }}</span>
                        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="button" onclick="confirmLogout()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="p-8">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- SweetAlert Success/Error Messages -->
    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '{{ session('error') }}',
            confirmButtonColor: '#dc2626'
        });
    </script>
    @endif

    <script>
        // Logout Confirmation
        function confirmLogout() {
            Swal.fire({
                title: 'Logout?',
                text: "Apakah Anda yakin ingin keluar?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Logout!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        }

        // Delete Confirmation
        function confirmDelete(formId, itemName = 'data ini') {
            event.preventDefault();
            Swal.fire({
                title: 'Hapus Data?',
                text: `Apakah Anda yakin ingin menghapus ${itemName}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }

        // Sidebar Toggle Function
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const toggleButton = document.getElementById('sidebarToggle');
            
            // Toggle collapsed class on sidebar and button
            sidebar.classList.toggle('collapsed');
            toggleButton.classList.toggle('collapsed');
            
            // Toggle button rotation
            toggleButton.classList.toggle('rotate-180');
            
            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        }

        // Restore sidebar state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                const sidebar = document.getElementById('sidebar');
                const toggleButton = document.getElementById('sidebarToggle');
                sidebar.classList.add('collapsed');
                toggleButton.classList.add('collapsed');
                toggleButton.classList.add('rotate-180');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
