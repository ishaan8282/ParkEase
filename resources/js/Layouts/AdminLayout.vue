<template>
    <div class="d-flex" style="min-height: 100vh;">

        <!-- Sidebar -->
        <nav class="d-flex flex-column flex-shrink-0 p-3 bg-dark text-white" style="width: 260px; min-height: 100vh;">
            <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <i class="bi bi-p-circle-fill fs-4 me-2 text-primary"></i>
                <span class="fs-5 fw-bold">ParkEase</span>
            </a>
            <small class="text-muted mb-3">Admin Panel</small>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <Link :href="route('admin.dashboard')"
                          class="nav-link text-white"
                          :class="{ active: isActive('admin.dashboard') }">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </Link>
                </li>
                <li class="nav-item">
                    <Link :href="route('admin.users.index')"
                          class="nav-link text-white"
                          :class="{ active: isActive('admin.users') }">
                        <i class="bi bi-people me-2"></i> Users
                    </Link>
                </li>
                <li class="nav-item">
                    <Link :href="route('admin.spaces.index')"
                          class="nav-link text-white"
                          :class="{ active: isActive('admin.spaces') }">
                        <i class="bi bi-building me-2"></i> Parking Spaces
                        <span v-if="pendingSpacesCount > 0" class="badge bg-warning text-dark ms-1">
                            {{ pendingSpacesCount }}
                        </span>
                    </Link>
                </li>
                <li class="nav-item">
                    <Link :href="route('admin.bookings.index')"
                          class="nav-link text-white"
                          :class="{ active: isActive('admin.bookings') }">
                        <i class="bi bi-calendar-check me-2"></i> Bookings
                    </Link>
                </li>
            </ul>
            <hr>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                   data-bs-toggle="dropdown">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-2"
                         style="width:32px;height:32px;">
                        <span class="text-white fw-bold" style="font-size:13px;">
                            {{ auth.user.name.charAt(0).toUpperCase() }}
                        </span>
                    </div>
                    <strong class="text-truncate" style="max-width:140px;">{{ auth.user.name }}</strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                    <li><span class="dropdown-item-text text-muted small">{{ auth.user.email }}</span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <Link :href="route('logout')" method="post" as="button" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </Link>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="flex-grow-1 bg-light">
            <!-- Top Bar -->
            <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-muted fw-normal">
                    <slot name="header">Dashboard</slot>
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger-subtle text-danger">Admin</span>
                </div>
            </header>

            <!-- Flash Messages -->
            <div class="px-4 pt-3">
                <div v-if="flash.success" class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ flash.success }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <div v-if="flash.error" class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>{{ flash.error }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>

            <!-- Page Content -->
            <main class="p-4">
                <slot />
            </main>
        </div>

    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const auth = computed(() => page.props.auth)
const flash = computed(() => page.props.flash)

// You can pass this from DashboardController shared data later
const pendingSpacesCount = computed(() => page.props.pendingSpacesCount ?? 0)

function isActive(routeName) {
    return route().current(routeName + '*')
}
</script>
