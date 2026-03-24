<template>
    <OwnerLayout page-title="Dashboard" :pending-bookings="stats?.active_bookings || 0">
        <template #topbar-actions>
            <Link :href="route('owner.spaces.create')" class="pe-btn pe-btn--neon">
                + Add Space
            </Link>
        </template>

        <!-- Stats Grid -->
        <div class="pe-stats-grid">
            <div class="pe-stat-card">
                <div class="pe-stat-card__icon">🅿️</div>
                <div class="pe-stat-card__content">
                    <span class="pe-stat-card__value">{{ stats.total_spaces }}</span>
                    <span class="pe-stat-card__label">Total Spaces</span>
                </div>
            </div>
            <div class="pe-stat-card">
                <div class="pe-stat-card__icon">📍</div>
                <div class="pe-stat-card__content">
                    <span class="pe-stat-card__value">{{ stats.total_slots }}</span>
                    <span class="pe-stat-card__label">Total Slots</span>
                </div>
            </div>
            <div class="pe-stat-card">
                <div class="pe-stat-card__icon neon-green">✓</div>
                <div class="pe-stat-card__content">
                    <span class="pe-stat-card__value">{{ stats.active_bookings }}</span>
                    <span class="pe-stat-card__label">Active Bookings</span>
                </div>
            </div>
            <div class="pe-stat-card">
                <div class="pe-stat-card__icon neon-cyan">💰</div>
                <div class="pe-stat-card__content">
                    <span class="pe-stat-card__value">₹{{ formatNumber(stats.monthly_arnings) }}</span>
                    <span class="pe-stat-card__label">This Month</span>
                </div>
            </div>
        </div>

        <!-- Today's Bookings -->
        <section class="pe-section">
            <div class="pe-section__header">
                <h2>Today's Bookings</h2>
                <span class="pe-badge">{{ todayBookings.length }} bookings</span>
            </div>
            <div v-if="todayBookings.length > 0" class="pe-bookings-table">
                <table>
                    <thead>
                        <tr>
                            <th>Ref</th>
                            <th>Customer</th>
                            <th>Space</th>
                            <th>Slot</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="booking in todayBookings" :key="booking.id">
                            <td class="pe-booking-ref">{{ booking.booking_ref }}</td>
                            <td>
                                <div class="pe-customer">
                                    <span class="pe-customer__name">{{ booking.user?.name }}</span>
                                    <span class="pe-customer__phone">{{ booking.user?.phone }}</span>
                                </div>
                            </td>
                            <td>{{ booking.parking_space?.name }}</td>
                            <td>Slot {{ booking.parking_slot?.slot_number }}</td>
                            <td>
                                {{ formatTime(booking.check_in) }} - {{ formatTime(booking.check_out) }}
                            </td>
                            <td>
                                <span class="pe-status" :class="booking.status">{{ booking.status }}</span>
                            </td>
                            <td>
                                <button
                                    v-if="booking.status === 'pending'"
                                    @click="confirmBooking(booking.id)"
                                    class="pe-btn pe-btn--sm pe-btn--neon"
                                >
                                    Confirm
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="pe-empty">
                <span>📅</span>
                <p>No bookings for today</p>
            </div>
        </section>

        <!-- Recent Bookings -->
        <section class="pe-section">
            <div class="pe-section__header">
                <h2>Recent Bookings</h2>
                <Link :href="route('owner.bookings.index')" class="pe-link">View All →</Link>
            </div>
            <div v-if="recentBookings.length > 0" class="pe-bookings-list">
                <div v-for="booking in recentBookings" :key="booking.id" class="pe-booking-item">
                    <div class="pe-booking-item__left">
                        <div class="pe-booking-item__ref">{{ booking.booking_ref }}</div>
                        <div class="pe-booking-item__customer">{{ booking.user?.name }}</div>
                        <div class="pe-booking-item__space">{{ booking.parking_space?.name }}</div>
                    </div>
                    <div class="pe-booking-item__right">
                        <div class="pe-booking-item__amount">₹{{ booking.total_amount }}</div>
                        <span class="pe-status" :class="booking.status">{{ booking.status }}</span>
                    </div>
                </div>
            </div>
            <div v-else class="pe-empty">
                <span>📋</span>
                <p>No bookings yet</p>
            </div>
        </section>

        <!-- Low Availability Spaces -->
        <section v-if="lowAvailabilitySpaces.length > 0" class="pe-section">
            <div class="pe-section__header">
                <h2>⚠️ Low Availability</h2>
            </div>
            <div class="pe-alert-grid">
                <div v-for="space in lowAvailabilitySpaces" :key="space.id" class="pe-alert-card">
                    <div class="pe-alert-card__header">
                        <span class="pe-alert-card__name">{{ space.name }}</span>
                        <span class="pe-alert-card__slots">{{ space.available_slots }} slots left</span>
                    </div>
                    <div class="pe-alert-card__progress">
                        <div class="pe-progress-bar">
                            <div class="pe-progress-fill" :style="{ width: space.occupancy_rate + '%' }"></div>
                        </div>
                        <span class="pe-alert-card__rate">{{ space.occupancy_rate }}% occupied</span>
                    </div>
                </div>
            </div>
        </section>
    </OwnerLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import OwnerLayout from '@/Components/Owner/OwnerLayout.vue'

const props = defineProps({
    stats: Object,
    todayBookings: Array,
    recentBookings: Array,
    lowAvailabilitySpaces: Array,
    spaces: Array,
})

function formatNumber(num) {
    if (!num) return '0'
    return new Intl.NumberFormat('en-IN').format(num)
}

function formatTime(dateStr) {
    if (!dateStr) return '-'
    return new Date(dateStr).toLocaleTimeString('en-IN', {
        hour: '2-digit',
        minute: '2-digit'
    })
}

function confirmBooking(bookingId) {
    console.log('Confirm booking:', bookingId)
}
</script>

<style scoped>
/* Page-specific styles only - most styles come from global owner-styles.css */
.pe-booking-ref { font-family: monospace; color: #00D4FF; font-weight: 600; }
</style>
