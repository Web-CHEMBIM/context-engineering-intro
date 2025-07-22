<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    
    <title>@yield('title', 'School Management System')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Feather Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons@4.29.0/feather.min.css">
    
    <!-- Cuba Admin Theme CSS -->
    <link rel="stylesheet" href="{{ asset('cuba-theme/css/cuba-theme.css') }}">
    
    <!-- Additional Styles -->
    @stack('styles')
    
    <style>
        /* Page specific overrides */
        .feather {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            stroke-width: 1.5;
        }
        
        /* Sidebar menu styles */
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin: 0;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--secondary-600);
            text-decoration: none;
            transition: var(--transition-colors);
            border-radius: 0;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: var(--primary-50);
            color: var(--primary-600);
        }
        
        .sidebar-menu .feather {
            margin-right: 12px;
            flex-shrink: 0;
        }
        
        .sidebar.collapsed .sidebar-menu a span {
            display: none;
        }
        
        .sidebar.collapsed .sidebar-menu .feather {
            margin-right: 0;
        }
        
        /* User profile dropdown */
        .user-profile {
            position: relative;
        }
        
        .user-profile .dropdown-menu {
            right: 0;
            left: auto;
            min-width: 200px;
        }
        
        /* Page header styles */
        .page-header {
            background-color: var(--white);
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--secondary-200);
        }
        
        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--secondary-800);
            margin: 0;
        }
        
        .breadcrumb {
            background: none;
            padding: 0;
            margin: 0;
        }
        
        .breadcrumb-item a {
            color: var(--secondary-500);
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: var(--secondary-700);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="p-20">
            <!-- Logo -->
            <div class="text-center mb-4">
                <h4 class="f-w-700 font-primary">School MS</h4>
                <span class="f-12 font-secondary">Management System</span>
            </div>
            
            <!-- Navigation Menu -->
            <ul class="sidebar-menu">
                @hasrole('SuperAdmin|Admin')
                <li>
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i data-feather="home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                @endhasrole
                
                @hasrole('SuperAdmin')
                <li>
                    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i data-feather="users"></i>
                        <span>User Management</span>
                    </a>
                </li>
                @endhasrole
                
                @hasrole('SuperAdmin|Admin')
                <li>
                    <a href="{{ route('academic-years.index') }}" class="{{ request()->routeIs('academic-years.*') ? 'active' : '' }}">
                        <i data-feather="calendar"></i>
                        <span>Academic Years</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('classes.index') }}" class="{{ request()->routeIs('classes.*') ? 'active' : '' }}">
                        <i data-feather="grid-3x3"></i>
                        <span>Classes</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('subjects.index') }}" class="{{ request()->routeIs('subjects.*') ? 'active' : '' }}">
                        <i data-feather="book-open"></i>
                        <span>Subjects</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('students.index') }}" class="{{ request()->routeIs('students.*') ? 'active' : '' }}">
                        <i data-feather="user-check"></i>
                        <span>Students</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('teachers.index') }}" class="{{ request()->routeIs('teachers.*') ? 'active' : '' }}">
                        <i data-feather="user-plus"></i>
                        <span>Teachers</span>
                    </a>
                </li>
                @endhasrole
                
                @hasrole('Teacher')
                <li>
                    <a href="{{ route('teacher.dashboard') }}" class="{{ request()->routeIs('teacher.*') ? 'active' : '' }}">
                        <i data-feather="home"></i>
                        <span>My Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('teacher.classes') }}" class="{{ request()->routeIs('teacher.classes') ? 'active' : '' }}">
                        <i data-feather="grid-3x3"></i>
                        <span>My Classes</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('teacher.students') }}" class="{{ request()->routeIs('teacher.students') ? 'active' : '' }}">
                        <i data-feather="users"></i>
                        <span>My Students</span>
                    </a>
                </li>
                @endhasrole
                
                @hasrole('Student')
                <li>
                    <a href="{{ route('student.dashboard') }}" class="{{ request()->routeIs('student.*') ? 'active' : '' }}">
                        <i data-feather="home"></i>
                        <span>My Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('student.grades') }}" class="{{ request()->routeIs('student.grades') ? 'active' : '' }}">
                        <i data-feather="award"></i>
                        <span>My Grades</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('student.subjects') }}" class="{{ request()->routeIs('student.subjects') ? 'active' : '' }}">
                        <i data-feather="book"></i>
                        <span>My Subjects</span>
                    </a>
                </li>
                @endhasrole
                
                <!-- Settings & Logout -->
                @hasrole('SuperAdmin|Admin')
                <li class="mt-4">
                    <a href="{{ route('settings') }}" class="{{ request()->routeIs('settings') ? 'active' : '' }}">
                        <i data-feather="settings"></i>
                        <span>Settings</span>
                    </a>
                </li>
                @endhasrole
            </ul>
        </div>
    </nav>
    
    <!-- Top Navigation Bar -->
    <header class="navbar" id="navbar">
        <div class="d-flex align-items-center">
            <!-- Sidebar Toggle Button -->
            <button type="button" class="btn btn-link p-0 me-3" id="sidebarToggle">
                <i data-feather="menu" class="feather"></i>
            </button>
            
            <!-- Page Title -->
            <h5 class="mb-0 font-primary">@yield('page-title', 'Dashboard')</h5>
        </div>
        
        <div class="d-flex align-items-center">
            <!-- Notifications -->
            <div class="dropdown me-3">
                <button class="btn btn-link p-0 position-relative" type="button" data-bs-toggle="dropdown">
                    <i data-feather="bell" class="feather"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
                        3
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Notifications</h6></li>
                    <li><a class="dropdown-item" href="#">New student enrolled</a></li>
                    <li><a class="dropdown-item" href="#">Assignment submitted</a></li>
                    <li><a class="dropdown-item" href="#">Grade updated</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center" href="#">View all</a></li>
                </ul>
            </div>
            
            <!-- User Profile Dropdown -->
            <div class="dropdown user-profile">
                <button class="btn btn-link p-0 d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                    <img src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=0ea5e9&color=ffffff' }}" 
                         alt="Profile" class="avatar avatar-sm me-2">
                    <div class="text-start">
                        <div class="f-14 f-w-500">{{ Auth::user()->name }}</div>
                        <div class="f-12 font-secondary">
                            @if(Auth::user()->hasRole('SuperAdmin'))
                                Super Administrator
                            @elseif(Auth::user()->hasRole('Admin'))
                                Administrator
                            @elseif(Auth::user()->hasRole('Teacher'))
                                Teacher
                            @elseif(Auth::user()->hasRole('Student'))
                                Student
                            @endif
                        </div>
                    </div>
                    <i data-feather="chevron-down" class="feather ms-2"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">{{ Auth::user()->name }}</h6></li>
                    <li><a class="dropdown-item" href="{{ route('profile.show') }}">
                        <i data-feather="user" class="feather me-2"></i>Profile
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i data-feather="edit" class="feather me-2"></i>Edit Profile  
                    </a></li>
                    @hasrole('SuperAdmin|Admin')
                    <li><a class="dropdown-item" href="{{ route('settings') }}">
                        <i data-feather="settings" class="feather me-2"></i>Settings
                    </a></li>
                    @endhasrole
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i data-feather="log-out" class="feather me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>
    
    <!-- Main Content Area -->
    <main class="main-content" id="mainContent">
        <!-- Page Header -->
        @if(isset($pageHeader) && $pageHeader)
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                    @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb f-14">
                            @foreach($breadcrumbs as $breadcrumb)
                                @if($loop->last)
                                    <li class="breadcrumb-item active">{{ $breadcrumb['title'] }}</li>
                                @else
                                    <li class="breadcrumb-item">
                                        <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a>
                                    </li>
                                @endif
                            @endforeach
                        </ol>
                    </nav>
                    @endif
                </div>
                @yield('page-actions')
            </div>
        </div>
        @endif
        
        <!-- Flash Messages -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i data-feather="check-circle" class="feather me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i data-feather="alert-circle" class="feather me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        
        @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i data-feather="alert-triangle" class="feather me-2"></i>
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        
        @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i data-feather="info" class="feather me-2"></i>
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        
        <!-- Validation Errors -->
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i data-feather="alert-circle" class="feather me-2"></i>
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        
        <!-- Main Content -->
        @yield('content')
    </main>
    
    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <!-- Feather Icons -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons@4.29.0/feather.min.js"></script>
    
    <!-- Cuba Theme JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Feather Icons
            feather.replace();
            
            // Sidebar Toggle Functionality
            const sidebar = document.getElementById('sidebar');
            const navbar = document.getElementById('navbar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                
                // Update localStorage for persistence
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
            
            // Restore sidebar state from localStorage
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
            }
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
            
            // Handle responsive sidebar for mobile
            function handleResize() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.add('collapsed');
                } else if (localStorage.getItem('sidebarCollapsed') !== 'true') {
                    sidebar.classList.remove('collapsed');
                }
            }
            
            window.addEventListener('resize', handleResize);
            handleResize(); // Initial check
        });
    </script>
    
    <!-- Additional Scripts -->
    @stack('scripts')
</body>
</html>