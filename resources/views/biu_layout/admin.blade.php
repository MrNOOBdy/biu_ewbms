@extends('biu_layout.app')

@section('title', 'BI-U: eWBS')

@section('content')
<div class="container admin-page">
    <div id="sidebar" class="sidebar-expanded">
        <div class="sidebar-header">
            <div class="logo-container">
                <img src="{{ asset('images/logo/logo2.png') }}" alt="Secondary Logo" class="logo2">
                <img src="{{ asset('images/logo/bi-u_logo.png') }}" alt="BI-U Logo" class="logo_nav">
            </div>
            <div class="sidebar-toggle">
                <button id="sidebar-toggle-btn" class="sidebar-toggle-btn" aria-label="Toggle Sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
        
        <div class="sidebar-content">
            <nav class="sidebar-nav">
                <ul class="tab-list">
                    @if($userRole->hasPermission('access-dashboard'))
                    <a href="{{ route('dashboard') }}" class="tab-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <div class="tab-icon"><i class="fa-solid fa-home"></i></div>
                        <span class="tab-label">Dashboard</span>
                    </a>
                    @endif
                    
                    @if($userRole->hasPermission('access-consumers'))
                    <a href="{{ route('consumers.index') }}" class="tab-item {{ request()->routeIs('consumers.*') ? 'active' : '' }}">
                        <div class="tab-icon"><i class="fa-solid fa-users"></i></div>
                        <span class="tab-label">Consumers</span>
                    </a>
                    @endif

                    @if($userRole->hasPermission('access-connection-payment'))
                    <li data-title="Connection Payment" class="tab-item dropdown {{ request()->routeIs(['service.*', 'application.*']) ? 'open' : '' }}">
                        <div class="tab-header">
                            <div class="tab-icon"><i class="fa-solid fa-plug-circle-bolt"></i></div>
                            <span class="tab-label">Connection Payment</span>
                        </div>
                        <ul class="dropdown-list">
                            @if($userRole->hasPermission('service-fee-access'))
                            <a href="{{ route('service.index') }}" class="dropdown-item {{ request()->routeIs('service.*') ? 'active' : '' }}">
                                <span class="item-dot"></span>
                                Service Fee
                            </a>
                            @endif
                            @if($userRole->hasPermission('access-application-fee'))
                            <a href="{{ route('application.fee') }}" class="dropdown-item {{ request()->routeIs('application.*') ? 'active' : '' }}">
                                <span class="item-dot"></span>
                                Application Fee
                            </a>
                            @endif
                        </ul>
                    </li>
                    @endif

                    @if($userRole->hasPermission('billing-payment'))
                    <a href="{{ route('billing.payments') }}" class="tab-item {{ request()->routeIs('billing.*') ? 'active' : '' }}">
                        <div class="tab-icon"><i class="fa-solid fa-money-check"></i></div>
                        <span class="tab-label">Billing Payment</span>
                    </a>
                    @endif

                    @if($userRole->hasPermission('access-billing'))
                    <li data-title="Billing" class="tab-item dropdown {{ request()->is('latest-bills', 'bill_notice') ? 'open' : '' }}">
                        <div class="tab-header">
                            <div class="tab-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                            <span class="tab-label">Billing</span>
                        </div>
                        <ul class="dropdown-list">
                            <a href="{{ route('latest-bills') }}" class="dropdown-item {{ request()->is('latest-bills') ? 'active' : '' }}">
                                <span class="item-dot"></span>
                                Latest Bills
                            </a>
                            <a href="{{ route('notice-bill') }}" class="dropdown-item {{ request()->is('notice-bill') ? 'active' : '' }}">
                                <span class="item-dot"></span>
                                Bill Notice
                            </a>
                        </ul>
                    </li>
                    @endif

                    @if($userRole->hasPermission('access-meter-reading'))
                    <a href="{{ route('meter-readings') }}" class="tab-item {{ request()->routeIs('meter-readings') ? 'active' : '' }}">
                        <div class="tab-icon"><i class="fa-solid fa-tachometer-alt"></i></div>
                        <span class="tab-label">Meter Reading</span>
                    </a>
                    @endif

                    @if($userRole->hasPermission('access-reports'))
                    <li data-title="Report" class="tab-item dropdown {{ request()->is(['income_rep', 'balance_rep', 'appli_income']) ? 'open' : '' }}">
                        <div class="tab-header">
                            <div class="tab-icon"><i class="fa-solid fa-chart-column"></i></div>
                            <span class="tab-label">Report</span>
                        </div>
                        <ul class="dropdown-list">
                            <li data-tab="income_rep" class="dropdown-item {{ request()->is('income_rep') ? 'active' : '' }}">
                                <span class="item-dot"></span>
                                Income Report
                            </li>
                            <li data-tab="balance_rep" class="dropdown-item {{ request()->is('balance_rep') ? 'active' : '' }}">
                                <span class="item-dot"></span>
                                Balance Report
                            </li>
                            <a href="{{ route('appli_income') }}" class="dropdown-item {{ request()->routeIs('appli_income') ? 'active' : '' }}">
                                <span class="item-dot"></span>
                                Application Income
                            </a>
                        </ul>
                    </li>
                    @endif

                    <div class="sidebar-divider">System</div>

                    @if($userRole->hasPermission('access-settings'))
                    <li data-title="General Settings" class="tab-item dropdown {{ request()->routeIs(['coverage-dates.*', 'blocks.*', 'billRates.*', 'notifications.*', 'local-settings.*']) ? 'open' : '' }}">
                        <div class="tab-header">
                            <div class="tab-icon"><i class="fa-solid fa-gear"></i></div>
                            <span class="tab-label">General Settings</span>
                        </div>
                        <ul class="dropdown-list">
                            @if($userRole->hasPermission('access-coverage-date'))
                            <a href="{{ route('coverage-dates.index') }}" class="dropdown-item {{ request()->routeIs('coverage-dates.*') ? 'active' : '' }}">
                                <span class="item-dot"></span>
                                Coverage Date
                            </a>
                            @endif
                            @if($userRole->hasPermission('access-block-management'))
                            <a href="{{ route('blocks.index') }}" class="dropdown-item {{ request()->routeIs('blocks.*') ? 'active' : '' }}">
                                <span class="item-dot"></span>
                                Block Management
                            </a>
                            @endif
                            @if($userRole->hasPermission('access-bill-rate'))
                            <a href="{{ route('billRates.index') }}" class="dropdown-item {{ request()->routeIs('billRates.*') ? 'active' : '' }}">
                                <span class="item-dot"></span>
                                Bill Rate
                            </a>
                            @endif
                            @if($userRole->hasPermission('view-notification-management'))
                            <a href="{{ route('notifications.index') }}" class="dropdown-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                                <span class="item-dot"></span>
                                Notification Management
                            </a>
                            @endif
                            <a href="{{ route('local-settings.index') }}" class="dropdown-item {{ request()->routeIs('local-settings.*') ? 'active' : '' }}">
                                <span class="item-dot"></span>
                                Local Settings
                            </a>
                        </ul>
                    </li>
                    @endif

                    @if($userRole->hasPermission('access-utilities'))
                    <li data-title="Utilities" class="tab-item dropdown {{ request()->routeIs(['roles.*', 'users.*']) ? 'open' : '' }}">
                        <div class="tab-header">
                            <div class="tab-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                            <span class="tab-label">Utilities</span>
                        </div>
                        <ul class="dropdown-list">
                            @if($userRole->hasPermission('view-role-management'))
                            <a href="{{ route('roles.index') }}" class="dropdown-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                <span class="item-dot"></span>
                                Roles and Permissions
                            </a>
                            @endif
                            @if($userRole->hasPermission('view-user-management'))
                            <a href="{{ route('users.index') }}" class="dropdown-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <span class="item-dot"></span>
                                User Management
                            </a>
                            @endif
                        </ul>
                    </li>
                    @endif
                </ul>
            </nav>
        </div>
    </div>

    <div class="block-contents">
        <div class="navigation-bar">
            <div class="page-title">
                <h1>BI-U: Electronic Water Billing System</h1>
            </div>

            <div class="top-bar-right">
                <div class="user-dropdown">
                    <button class="dropdown-toggle" onclick="toggleUserMenu(event)">
                        <div class="user-avatar">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div class="user-info">
                            <span class="username">{{ Auth::user()->username }}</span>
                            <span class="user-role">{{ Auth::user()->role }}</span>
                        </div>
                    </button>

                    <div class="user-menu" id="userMenu">
                        <div class="user-menu-header">
                            <div class="user-fullname">
                                {{ Auth::user()->firstname }} {{ Auth::user()->lastname }}
                            </div>
                            <div class="user-email">
                                {{ Auth::user()->email }}
                            </div>
                        </div>
                        <div class="user-menu-items">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="menu-item">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-content">
            @yield('tab-content')
        </div>
    </div>
</div>
@endsection

@stack('scripts')
@yield('scripts')


