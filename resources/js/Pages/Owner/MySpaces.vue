<template>
    <OwnerLayout page-title="My Spaces" page-subtitle="Manage your parking spaces">
        <template #topbar-actions>
            <Link :href="route('owner.spaces.create')" class="pe-btn pe-btn--neon">
                + Add New Space
            </Link>
        </template>

        <!-- Spaces Grid -->
        <div v-if="spaces.length > 0" class="pe-spaces-grid">
            <div v-for="space in spaces" :key="space.id" class="pe-space-card">
                <div class="pe-space-card__image">
                    <img v-if="space.images && space.images.length" :src="space.images[0]" :alt="space.name" />
                    <div v-else class="pe-space-card__placeholder">🅿️</div>
                    <div class="pe-space-card__status" :class="space.is_verified ? 'verified' : 'pending'">
                        {{ space.is_verified ? '✓ Verified' : '⏳ Pending' }}
                    </div>
                </div>
                <div class="pe-space-card__content">
                    <h3>{{ space.name }}</h3>
                    <p class="pe-space-card__address">
                        📍 {{ space.address }}, {{ space.city }}
                    </p>
                    <div class="pe-space-card__stats">
                        <div class="pe-space-stat">
                            <span class="pe-space-stat__value">{{ space.total_slots }}</span>
                            <span class="pe-space-stat__label">Slots</span>
                        </div>
                        <div class="pe-space-stat">
                            <span class="pe-space-stat__value">₹{{ space.price_per_hour }}</span>
                            <span class="pe-space-stat__label">/hour</span>
                        </div>
                        <div class="pe-space-stat">
                            <span class="pe-space-stat__value">{{ space.bookings_count || 0 }}</span>
                            <span class="pe-space-stat__label">Bookings</span>
                        </div>
                    </div>
                    <div class="pe-space-card__actions">
                        <Link :href="route('owner.spaces.show', space.id)" class="pe-btn pe-btn--ghost pe-btn--sm">
                            View
                        </Link>
                        <Link :href="route('owner.spaces.edit', space.id)" class="pe-btn pe-btn--ghost pe-btn--sm">
                            Edit
                        </Link>
                        <button @click="deleteSpace(space.id)" class="pe-btn pe-btn--ghost pe-btn--sm delete">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="pe-empty-state">
            <div class="pe-empty-icon">🅿️</div>
            <h2>No parking spaces yet</h2>
            <p>Start earning by listing your first parking space</p>
            <Link :href="route('owner.spaces.create')" class="pe-btn pe-btn--neon">
                + Add Your First Space
            </Link>
        </div>
    </OwnerLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import OwnerLayout from '@/Components/Owner/OwnerLayout.vue'

const props = defineProps({
    spaces: Array,
})

function deleteSpace(id) {
    if (confirm('Are you sure you want to delete this parking space?')) {
        console.log('Delete:', id)
    }
}
</script>

<style scoped>
/* Page-specific styles only - most styles come from global owner-styles.css */
.pe-btn.delete { color: #FF6B6B; }
.pe-btn.delete:hover { background: rgba(255, 107, 107, 0.15); border-color: #FF6B6B; }
</style>
