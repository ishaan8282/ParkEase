<template>
    <div class="pe-root">
        <!-- Sidebar -->
        <OwnerSidebar
            :is-open="sidebarOpen"
            :pending-bookings="pendingBookings"
            @close="sidebarOpen = false"
        >
            <template #nav></template>
        </OwnerSidebar>

        <!-- Overlay for mobile -->
        <div v-if="sidebarOpen" class="pe-overlay" @click="sidebarOpen = false"></div>

        <!-- Main -->
        <main class="pe-main">
            <!-- Top bar -->
            <OwnerTopbar
                :title="pageTitle"
                :show-live-indicator="showLiveIndicator"
                @toggle-sidebar="sidebarOpen = !sidebarOpen"
            >
                <template #actions>
                    <slot name="topbar-actions"></slot>
                </template>
            </OwnerTopbar>

            <!-- Flash Messages -->
            <FlashMessages
                :success="flash?.success"
                :error="flash?.error"
            />

            <!-- Page Title -->
            <div v-if="showPageTitle" class="pe-page-title">
                <h1>
                    <span v-if="pageTitleHighlight" class="neon-cyan">{{ pageTitleHighlight }}</span>
                    <span v-else>{{ pageTitle }}</span>
                </h1>
                <p>{{ pageSubtitle }}</p>
            </div>

            <!-- Content -->
            <slot></slot>
        </main>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import OwnerSidebar from './OwnerSidebar.vue'
import OwnerTopbar from './OwnerTopbar.vue'
import FlashMessages from './FlashMessages.vue'

// Import global CSS for Owner pages
import '@/../css/owner-styles.css'

const props = defineProps({
    pageTitle: {
        type: String,
        required: true
    },
    pageTitleHighlight: {
        type: String,
        default: null
    },
    pageSubtitle: {
        type: String,
        default: ''
    },
    showPageTitle: {
        type: Boolean,
        default: true
    },
    showLiveIndicator: {
        type: Boolean,
        default: false
    },
    pendingBookings: {
        type: Number,
        default: 0
    }
})

const page = usePage()
const flash = computed(() => page.props.flash)

const sidebarOpen = ref(false)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Outfit:wght@300;400;500;600&display=swap');

.pe-root {
    display: flex;
    min-height: 100vh;
    background: #060910;
    font-family: 'Outfit', sans-serif;
    color: #E8EDF5;
}

.neon-cyan {
    color: #00D4FF;
    text-shadow: 0 0 10px rgba(0,212,255,.9), 0 0 30px rgba(0,212,255,.4);
}

.pe-overlay {
    position: fixed; inset: 0; z-index: 190;
    background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);
}

.pe-main {
    flex: 1;
    margin-left: 260px;
    padding: 0 32px 40px;
    min-height: 100vh;
    position: relative;
}

/* subtle background grid */
.pe-main::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image:
        linear-gradient(rgba(0,212,255,.018) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,212,255,.018) 1px, transparent 1px);
    background-size: 50px 50px;
    pointer-events: none;
    z-index: 0;
}

.pe-page-title {
    margin-bottom: 28px;
    position: relative;
    z-index: 1;
}

.pe-page-title h1 {
    font-family: 'Syne', sans-serif;
    font-size: 2rem; font-weight: 800;
    letter-spacing: -1px; margin-bottom: 4px;
}

.pe-page-title p {
    color: rgba(232,237,245,.45);
    font-size: .9rem;
}

@media (max-width: 1024px) {
    .pe-main {
        margin-left: 0;
        padding: 0 16px 40px;
    }
}
</style>
