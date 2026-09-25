<template>
    <aside class="pe-sidebar" :class="{ 'pe-sidebar--open': isOpen, 'collapsed': collapsed }">
        <div class="pe-sidebar__header">
            <Link href="/" class="pe-sidebar__logo">
                <svg class="pe-logo-svg" viewBox="0 0 200 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="iG" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#00D4FF"/>
                            <stop offset="100%" style="stop-color:#7B4FFF"/>
                        </linearGradient>
                        <filter id="ng"><feGaussianBlur stdDeviation="2" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
                    </defs>
                    <rect x="4" y="4" width="42" height="42" rx="12" fill="url(#iG)" filter="url(#ng)"/>
                    <rect x="4" y="4" width="42" height="42" rx="12" fill="none" stroke="rgba(0,212,255,0.4)" stroke-width="1"/>
                    <text x="14" y="35" font-family="Arial Black,sans-serif" font-weight="900" font-size="26" fill="#080B14">P</text>
                    <text x="56" y="30" font-family="Arial Black,sans-serif" font-weight="900" font-size="20" fill="white" letter-spacing="-0.5">Park</text>
                    <text x="103" y="30" font-family="Arial Black,sans-serif" font-weight="900" font-size="20" fill="url(#iG)" filter="url(#ng)" letter-spacing="-0.5">Ease</text>
                </svg>
            </Link>
            <button v-if="isOpen !== undefined" class="pe-sidebar__close" @click="$emit('close')">✕</button>
        </div>

        <div v-if="showUser" class="pe-sidebar__user">
            <div class="pe-sidebar__avatar">{{ auth?.user?.name?.charAt(0).toUpperCase() }}</div>
            <div>
                <div class="pe-sidebar__uname">{{ auth?.user?.name }}</div>
                <div class="pe-sidebar__urole">{{ userRole || 'Space Owner' }}</div>
            </div>
        </div>

        <nav class="pe-sidebar__nav">
            <div v-if="showSectionLabel" class="pe-sidebar__section-label">Menu</div>
            <slot name="nav"></slot>
            <Link :href="route('owner.dashboard')" class="pe-sidebar__link" :class="{ active: isActive('owner.dashboard') }">
                <span class="pe-sidebar__icon">📊</span>
                <span>Dashboard</span>
            </Link>
            <Link :href="route('owner.spaces.index')" class="pe-sidebar__link" :class="{ active: isActive('owner.spaces') }">
                <span class="pe-sidebar__icon">🅿️</span>
                <span>My Spaces</span>
            </Link>
            <Link :href="route('owner.bookings.index')" class="pe-sidebar__link" :class="{ active: isActive('owner.bookings') }">
                <span class="pe-sidebar__icon">📅</span>
                <span>Bookings</span>
                <span v-if="pendingBookings > 0" class="pe-sidebar__badge">{{ pendingBookings }}</span>
            </Link>
            <div class="pe-sidebar__divider"></div>
            <Link :href="route('home')" class="pe-sidebar__link">
                <span class="pe-sidebar__icon">🌐</span>
                <span>View Site</span>
            </Link>
        </nav>

        <div class="pe-sidebar__footer">
            <Link :href="route('logout')" method="post" as="button" class="pe-sidebar__link pe-sidebar__link--logout">
                <span class="pe-sidebar__icon">👋</span>
                <span>Logout</span>
            </Link>
        </div>
    </aside>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: undefined
    },
    collapsed: {
        type: Boolean,
        default: false
    },
    showUser: {
        type: Boolean,
        default: true
    },
    showSectionLabel: {
        type: Boolean,
        default: true
    },
    userRole: {
        type: String,
        default: null
    },
    pendingBookings: {
        type: Number,
        default: 0
    }
})

defineEmits(['close'])

const page = usePage()
const auth = computed(() => page.props.auth)

function isActive(name) {
    return route().current(name + '*')
}
</script>

<style scoped>
.pe-sidebar {
    width: 260px;
    background: rgba(8, 12, 22, 0.98);
    border-right: 1px solid rgba(0,212,255,0.08);
    display: flex;
    flex-direction: column;
    position: fixed;
    height: 100vh;
    z-index: 200;
    box-shadow: 4px 0 40px rgba(0,0,0,0.4);
    transition: transform 0.3s ease;
}

.pe-sidebar.collapsed {
    width: 80px;
}

.pe-sidebar--open {
    transform: translateX(0);
}

.pe-sidebar__header {
    padding: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.pe-logo-svg { height: 38px; width: auto; }
.pe-sidebar__close { display: none; background: none; border: none; color: rgba(232,237,245,.4); font-size: 1rem; cursor: pointer; }

.pe-sidebar__user {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.pe-sidebar__avatar {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, #00D4FF, #7B4FFF);
    display: flex; align-items: center; justify-content: center;
    font-family: 'Syne', sans-serif;
    font-weight: 800; font-size: 1rem; color: #060910;
    flex-shrink: 0;
    box-shadow: 0 0 16px rgba(0,212,255,0.3);
}

.pe-sidebar__uname { font-weight: 600; font-size: .9rem; }
.pe-sidebar__urole { font-size: .75rem; color: rgba(232,237,245,.4); margin-top: 2px; }

.pe-sidebar__nav {
    flex: 1;
    padding: 16px 12px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    overflow-y: auto;
}

.pe-sidebar__section-label {
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: rgba(232,237,245,.25);
    padding: 8px 16px 6px;
}

.pe-sidebar__link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 16px;
    border-radius: 12px;
    text-decoration: none;
    color: rgba(232,237,245,.55);
    font-size: .9rem;
    transition: all .2s;
    position: relative;
    border: none;
    background: none;
    width: 100%;
    cursor: pointer;
    font-family: 'Outfit', sans-serif;
}

.pe-sidebar__link:hover {
    background: rgba(0,212,255,0.06);
    color: #E8EDF5;
}

.pe-sidebar__link.active {
    background: rgba(0,212,255,0.1);
    color: #00D4FF;
    text-shadow: 0 0 8px rgba(0,212,255,0.5);
    border: 1px solid rgba(0,212,255,0.15);
    box-shadow: 0 0 20px rgba(0,212,255,0.06), inset 0 0 12px rgba(0,212,255,0.04);
}

.pe-sidebar__link.active .pe-sidebar__icon {
    filter: drop-shadow(0 0 4px rgba(0,212,255,0.6));
}

.pe-sidebar__icon { font-size: 1.1rem; flex-shrink: 0; }

.pe-sidebar__badge {
    margin-left: auto;
    background: rgba(255,217,61,0.15);
    color: #FFD93D;
    border: 1px solid rgba(255,217,61,0.3);
    font-size: .65rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 100px;
    box-shadow: 0 0 8px rgba(255,217,61,0.2);
}

.pe-sidebar__divider {
    height: 1px;
    background: rgba(255,255,255,0.05);
    margin: 10px 16px;
}

.pe-sidebar__footer { padding: 12px; border-top: 1px solid rgba(255,255,255,0.05); }

.pe-sidebar__link--logout:hover {
    background: rgba(255,107,107,0.08);
    color: #FF6B6B;
}

@media (max-width: 1024px) {
    .pe-sidebar { transform: translateX(-100%); }
    .pe-sidebar--open { transform: translateX(0); }
    .pe-sidebar__close { display: block; }
}
</style>
