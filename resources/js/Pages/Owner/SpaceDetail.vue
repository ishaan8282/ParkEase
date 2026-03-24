<template>
    <OwnerLayout :page-title="space.name" :show-page-title="false">
        <template #topbar-actions>
            <Link :href="route('owner.spaces.index')" class="pe-back-link">← Back to My Spaces</Link>
            <Link :href="route('owner.spaces.edit', space.id)" class="pe-btn pe-btn--neon" style="margin-left: 12px;">
                Edit Space
            </Link>
        </template>

        <!-- Stats Cards -->
        <div class="pe-stats-grid">
            <div class="pe-stat-card">
                <div class="pe-stat-card__icon">🅿️</div>
                <div class="pe-stat-card__content">
                    <span class="pe-stat-card__value">{{ space.total_slots }}</span>
                    <span class="pe-stat-card__label">Total Slots</span>
                </div>
            </div>
            <div class="pe-stat-card">
                <div class="pe-stat-card__icon">✅</div>
                <div class="pe-stat-card__content">
                    <span class="pe-stat-card__value">{{ availableSlots }}</span>
                    <span class="pe-stat-card__label">Available</span>
                </div>
            </div>
            <div class="pe-stat-card">
                <div class="pe-stat-card__icon">📅</div>
                <div class="pe-stat-card__content">
                    <span class="pe-stat-card__value">{{ space.bookings?.length || 0 }}</span>
                    <span class="pe-stat-card__label">Total Bookings</span>
                </div>
            </div>
            <div class="pe-stat-card">
                <div class="pe-stat-card__icon">💰</div>
                <div class="pe-stat-card__content">
                    <span class="pe-stat-card__value">₹{{ space.price_per_hour }}</span>
                    <span class="pe-stat-card__label">Per Hour</span>
                </div>
            </div>
        </div>

        <div class="pe-detail-grid">
            <!-- Slots Section -->
            <section class="pe-detail-section">
                <h2>Parking Slots</h2>
                <div class="pe-slots-grid">
                    <div v-for="slot in space.slots" :key="slot.id" class="pe-slot-card" :class="slot.status">
                        <span class="pe-slot-number">{{ slot.slot_number }}</span>
                        <span class="pe-slot-type">{{ slot.type }}</span>
                        <span class="pe-slot-status">{{ slot.status }}</span>
                    </div>
                </div>
            </section>

            <!-- Info Section -->
            <section class="pe-detail-section">
                <h2>Space Details</h2>
                <div class="pe-info-list">
                    <div class="pe-info-item">
                        <span class="pe-info-label">Status</span>
                        <span class="pe-info-value" :class="space.is_verified ? 'verified' : 'pending'">
                            {{ space.is_verified ? '✓ Verified' : '⏳ Pending Verification' }}
                        </span>
                    </div>
                    <div class="pe-info-item">
                        <span class="pe-info-label">Active Status</span>
                        <span class="pe-info-value">{{ space.status }}</span>
                    </div>
                    <div class="pe-info-item">
                        <span class="pe-info-label">Price/Day</span>
                        <span class="pe-info-value">₹{{ space.price_per_day || 'N/A' }}</span>
                    </div>
                    <div class="pe-info-item">
                        <span class="pe-info-label">Description</span>
                        <span class="pe-info-value">{{ space.description || 'No description' }}</span>
                    </div>
                    <div class="pe-info-item">
                        <span class="pe-info-label">Amenities</span>
                        <div class="pe-amenities">
                            <span v-if="space.amenities && space.amenities.length" v-for="amenity in space.amenities" :key="amenity" class="pe-amenity-tag">
                                {{ amenity }}
                            </span>
                            <span v-else class="pe-info-value">No amenities listed</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Recent Bookings -->
        <section class="pe-detail-section full-width">
            <h2>Recent Bookings</h2>
            <div v-if="space.bookings && space.bookings.length" class="pe-bookings-table">
                <table>
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Driver</th>
                            <th>Slot</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Status</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="booking in space.bookings.slice(0, 10)" :key="booking.id">
                            <td>#{{ booking.id }}</td>
                            <td>{{ booking.user?.name || 'N/A' }}</td>
                            <td>{{ booking.slot?.slot_number || 'N/A' }}</td>
                            <td>{{ formatDate(booking.check_in) }}</td>
                            <td>{{ formatDate(booking.check_out) }}</td>
                            <td>
                                <span class="pe-status-badge" :class="booking.status">
                                    {{ booking.status }}
                                </span>
                            </td>
                            <td>₹{{ booking.total_amount }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="pe-empty-bookings">
                <p>No bookings yet</p>
            </div>
        </section>
    </OwnerLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import OwnerLayout from '@/Components/Owner/OwnerLayout.vue'

const props = defineProps({
    space: Object,
})

const availableSlots = computed(() => {
    if (!props.space.slots) return 0
    return props.space.slots.filter(s => s.status === 'available').length
})

function formatDate(date) {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-IN', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>

<style scoped>
/* Page-specific styles - most styles come from global owner-styles.css */
</style>
