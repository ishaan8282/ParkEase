<template>
    <div class="pe-root">
        <!-- Navbar -->
        <nav class="pe-nav">
            <div class="pe-nav__inner">
                <Link href="/" class="pe-nav__logo">
                    <svg class="pe-logo-svg" viewBox="0 0 200 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="iG" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#00D4FF"/>
                                <stop offset="100%" style="stop-color:#7B4FFF"/>
                            </linearGradient>
                        </defs>
                        <rect x="4" y="4" width="42" height="42" rx="12" fill="url(#iG)"/>
                        <text x="14" y="35" font-family="Arial Black" font-weight="900" font-size="26" fill="#080B14">P</text>
                    </svg>
                </Link>
                <div class="pe-nav__actions">
                    <Link :href="route('search')" class="pe-btn pe-btn--ghost">Find Parking</Link>
                    <div class="pe-user-menu">
                        <span class="pe-user-avatar">{{ userInitials }}</span>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Success Message -->
        <div v-if="showSuccess" class="pe-success-banner">
            <span>🎉</span>
            <p>Booking confirmed! Your slot has been reserved.</p>
            <button @click="showSuccess = false">✕</button>
        </div>

        <!-- Main Content -->
        <main class="pe-my-bookings">
            <div class="pe-container">
                <div class="pe-header">
                    <h1>My Bookings</h1>
                    <Link :href="route('search')" class="pe-btn pe-btn--neon">
                        + Book New Parking
                    </Link>
                </div>

                <!-- Tabs -->
                <div class="pe-tabs">
                    <button
                        v-for="tab in tabs"
                        :key="tab.value"
                        @click="activeTab = tab.value"
                        class="pe-tab"
                        :class="{ active: activeTab === tab.value }"
                    >
                        {{ tab.label }}
                        <span class="pe-tab-count">{{ getTabCount(tab.value) }}</span>
                    </button>
                </div>

                <!-- Bookings List -->
                <div v-if="filteredBookings.length > 0" class="pe-bookings-list">
                    <div v-for="booking in filteredBookings" :key="booking.id" class="pe-booking-card">
                        <div class="pe-booking-main">
                            <div class="pe-booking-icon">🅿️</div>
                            <div class="pe-booking-info">
                                <h3>{{ booking.space?.name || 'Parking Space' }}</h3>
                                <p>{{ booking.space?.address }}, {{ booking.space?.city }}</p>
                                <div class="pe-booking-meta">
                                    <span>📅 {{ formatDate(booking.check_in) }}</span>
                                    <span>⏱️ {{ getDuration(booking.check_in, booking.check_out) }}</span>
                                    <span>🚗 {{ booking.vehicle_type }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="pe-booking-right">
                            <div class="pe-booking-price">
                                <span class="pe-amount">₹{{ booking.total_amount }}</span>
                                <span class="pe-status-badge" :class="booking.status">
                                    {{ booking.status }}
                                </span>
                            </div>

                            <div class="pe-booking-actions">
                                <button v-if="booking.status === 'confirmed'" @click="viewQRCode(booking)" class="pe-btn pe-btn--ghost pe-btn--sm">
                                    View QR
                                </button>
                                <button v-if="canCancel(booking)" @click="cancelBooking(booking)" class="pe-btn pe-btn--ghost pe-btn--sm cancel">
                                    Cancel
                                </button>
                                <Link v-if="booking.status === 'completed'" :href="route('spaces.show', booking.space_id)" class="pe-btn pe-btn--ghost pe-btn--sm">
                                    Book Again
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="pe-empty-state">
                    <div class="pe-empty-icon">📅</div>
                    <h2>No {{ activeTab }} bookings</h2>
                    <p v-if="activeTab === 'all'">You haven't made any parking bookings yet.</p>
                    <p v-else>No {{ activeTab }} bookings to show.</p>
                    <Link :href="route('search')" class="pe-btn pe-btn--neon">
                        Find Parking
                    </Link>
                </div>
            </div>
        </main>

        <!-- QR Code Modal -->
        <div v-if="showQR" class="pe-modal" @click.self="showQR = false">
            <div class="pe-modal-content">
                <h3>Your Booking QR Code</h3>
                <div class="pe-qr-code">
                    <div class="pe-qr-placeholder">
                        <span>📱</span>
                        <p>QR Code</p>
                        <small>#{{ selectedBooking?.id }}</small>
                    </div>
                </div>
                <p class="pe-qr-instructions">Show this QR code at the parking entrance</p>
                <button @click="showQR = false" class="pe-btn pe-btn--ghost">Close</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    bookings: Array,
    user: Object,
})

// Check for success param in URL
const urlParams = new URLSearchParams(window.location.search)
const showSuccess = ref(urlParams.get('success') === 'true')

const activeTab = ref('all')
const showQR = ref(false)
const selectedBooking = ref(null)

const tabs = [
    { label: 'All', value: 'all' },
    { label: 'Upcoming', value: 'upcoming' },
    { label: 'Completed', value: 'completed' },
    { label: 'Cancelled', value: 'cancelled' },
]

// Sample bookings for demo (would come from backend)
const allBookings = computed(() => props.bookings || [
    {
        id: 1001,
        space: { name: 'Mall Road Parking', address: 'Mall Road', city: 'Shimla' },
        check_in: '2025-02-28 10:00:00',
        check_out: '2025-02-28 14:00:00',
        total_amount: 200,
        vehicle_type: 'car',
        status: 'confirmed',
        slot: { slot_number: 'SLOT-001' }
    },
    {
        id: 1002,
        space: { name: 'Manali Main Parking', address: 'Mall Road', city: 'Manali' },
        check_in: '2025-02-20 09:00:00',
        check_out: '2025-02-20 18:00:00',
        total_amount: 450,
        vehicle_type: 'suv',
        status: 'completed',
        slot: { slot_number: 'SLOT-015' }
    },
    {
        id: 1003,
        space: { name: 'Mussoorie Parking', address: 'Library Road', city: 'Mussoorie' },
        check_in: '2025-02-15 11:00:00',
        check_out: '2025-02-15 15:00:00',
        total_amount: 160,
        vehicle_type: 'car',
        status: 'cancelled',
        slot: { slot_number: 'SLOT-003' }
    },
])

const filteredBookings = computed(() => {
    if (activeTab.value === 'all') return allBookings.value
    if (activeTab.value === 'upcoming') {
        return allBookings.value.filter(b => ['confirmed', 'pending'].includes(b.status))
    }
    return allBookings.value.filter(b => b.status === activeTab.value)
})

function getTabCount(tab) {
    if (tab === 'all') return allBookings.value.length
    if (tab === 'upcoming') return allBookings.value.filter(b => ['confirmed', 'pending'].includes(b.status)).length
    return allBookings.value.filter(b => b.status === tab).length
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-IN', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    })
}

function getDuration(checkIn, checkOut) {
    const inDate = new Date(checkIn)
    const outDate = new Date(checkOut)
    const hours = (outDate - inDate) / (1000 * 60 * 60)
    return `${hours} hours`
}

function canCancel(booking) {
    return ['confirmed', 'pending'].includes(booking.status)
}

function viewQRCode(booking) {
    selectedBooking.value = booking
    showQR.value = true
}

function cancelBooking(booking) {
    if (confirm('Are you sure you want to cancel this booking?')) {
        // Call the backend to cancel the booking
        fetch(window.route('driver.bookings.cancel', { booking: booking.id }), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify({ reason: 'User requested cancellation' })
        })
        .then(response => {
            if (response.ok) {
                // Reload the page to get updated bookings
                window.location.reload()
            } else {
                alert('Failed to cancel booking. Please try again.')
            }
        })
        .catch(error => {
            console.error('Cancel error:', error)
            alert('Failed to cancel booking. Please try again.')
        })
    }
}

const userInitials = computed(() => {
    if (!props.user) return 'U'
    const names = props.user.name?.split(' ') || ['U']
    return names.map(n => n[0]).join('').toUpperCase().slice(0, 2)
})
</script>

<style scoped>
.pe-root {
    font-family: 'Outfit', sans-serif;
    background: #080B14;
    color: #E8EDF5;
    min-height: 100vh;
}

.pe-nav {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 100;
    background: rgba(8, 11, 20, 0.95);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(0, 212, 255, 0.1);
    padding: 16px 0;
}

.pe-nav__inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.pe-logo-svg { height: 36px; }
.pe-nav__actions { display: flex; align-items: center; gap: 16px; }

.pe-user-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #00D4FF, #7B4FFF);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
    color: #080B14;
}

.pe-success-banner {
    position: fixed;
    top: 80px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 229, 160, 0.15);
    border: 1px solid rgba(0, 229, 160, 0.3);
    padding: 16px 24px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    z-index: 200;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from { opacity: 0; transform: translate(-50%, -20px); }
    to { opacity: 1; transform: translate(-50%, 0); }
}

.pe-success-banner p { color: #00E5A0; font-weight: 500; }
.pe-success-banner button {
    background: none;
    border: none;
    color: rgba(232, 237, 245, 0.5);
    cursor: pointer;
    font-size: 1.1rem;
}

.pe-my-bookings { padding: 100px 24px 60px; }
.pe-container { max-width: 900px; margin: 0 auto; }

.pe-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
}

.pe-header h1 {
    font-family: 'Syne', sans-serif;
    font-size: 1.8rem;
    font-weight: 800;
}

.pe-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 24px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    padding-bottom: 16px;
}

.pe-tab {
    background: none;
    border: none;
    color: rgba(232, 237, 245, 0.5);
    font-size: 0.95rem;
    padding: 10px 20px;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.pe-tab:hover { color: #E8EDF5; }
.pe-tab.active {
    background: rgba(0, 212, 255, 0.1);
    color: #00D4FF;
}

.pe-tab-count {
    background: rgba(255, 255, 255, 0.1);
    padding: 2px 8px;
    border-radius: 100px;
    font-size: 0.75rem;
}

.pe-tab.active .pe-tab-count {
    background: rgba(0, 212, 255, 0.2);
}

.pe-bookings-list { display: flex; flex-direction: column; gap: 16px; }

.pe-booking-card {
    background: rgba(10, 14, 24, 0.95);
    border: 1px solid rgba(0, 212, 255, 0.1);
    border-radius: 20px;
    padding: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: border-color 0.2s;
}

.pe-booking-card:hover { border-color: rgba(0, 212, 255, 0.25); }

.pe-booking-main { display: flex; gap: 16px; }

.pe-booking-icon {
    width: 56px;
    height: 56px;
    background: rgba(0, 212, 255, 0.1);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.pe-booking-info h3 {
    font-family: 'Syne', sans-serif;
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 4px;
}

.pe-booking-info p {
    font-size: 0.85rem;
    color: rgba(232, 237, 245, 0.5);
    margin-bottom: 10px;
}

.pe-booking-meta {
    display: flex;
    gap: 16px;
    font-size: 0.8rem;
    color: rgba(232, 237, 245, 0.6);
}

.pe-booking-right { text-align: right; }

.pe-booking-price {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    margin-bottom: 12px;
}

.pe-amount {
    font-family: 'Syne', sans-serif;
    font-size: 1.3rem;
    font-weight: 700;
}

.pe-status-badge {
    padding: 4px 12px;
    border-radius: 100px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: capitalize;
}
.pe-status-badge.confirmed { background: rgba(0, 229, 160, 0.15); color: #00E5A0; }
.pe-status-badge.completed { background: rgba(0, 212, 255, 0.15); color: #00D4FF; }
.pe-status-badge.cancelled { background: rgba(255, 107, 107, 0.15); color: #FF6B6B; }
.pe-status-badge.pending { background: rgba(255, 217, 61, 0.15); color: #FFD93D; }

.pe-booking-actions { display: flex; gap: 10px; justify-content: flex-end; }

.pe-btn--sm { padding: 8px 14px; font-size: 0.85rem; }
.pe-btn.cancel { color: #FF6B6B; }
.pe-btn.cancel:hover { background: rgba(255, 107, 107, 0.15); border-color: #FF6B6B; }

.pe-empty-state {
    text-align: center;
    padding: 80px 24px;
    background: rgba(10, 14, 24, 0.9);
    border: 1px solid rgba(0, 212, 255, 0.1);
    border-radius: 20px;
}

.pe-empty-icon { font-size: 4rem; margin-bottom: 20px; }
.pe-empty-state h2 { font-family: 'Syne', sans-serif; font-size: 1.5rem; margin-bottom: 8px; }
.pe-empty-state p { color: rgba(232, 237, 245, 0.5); margin-bottom: 24px; }

.pe-modal {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 300;
}

.pe-modal-content {
    background: rgba(10, 14, 24, 0.98);
    border: 1px solid rgba(0, 212, 255, 0.2);
    border-radius: 24px;
    padding: 32px;
    text-align: center;
    max-width: 360px;
}

.pe-modal-content h3 {
    font-family: 'Syne', sans-serif;
    font-size: 1.2rem;
    margin-bottom: 24px;
}

.pe-qr-code {
    background: white;
    padding: 20px;
    border-radius: 16px;
    margin-bottom: 16px;
}

.pe-qr-placeholder {
    height: 180px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #080B14;
}
.pe-qr-placeholder span { font-size: 3rem; margin-bottom: 8px; }
.pe-qr-placeholder small { color: #666; margin-top: 4px; }

.pe-qr-instructions {
    color: rgba(232, 237, 245, 0.6);
    font-size: 0.9rem;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .pe-booking-card { flex-direction: column; gap: 20px; }
    .pe-booking-right { text-align: left; width: 100%; }
    .pe-booking-actions { justify-content: flex-start; }
}
</style>
