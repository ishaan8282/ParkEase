<template>
    <OwnerLayout page-title="Add New Parking Space" :show-page-title="false">
        <template #topbar-actions>
            <Link :href="route('owner.spaces.index')" class="pe-back-link">← Back to My Spaces</Link>
        </template>

        <form @submit.prevent="submitForm" class="pe-form">
            <!-- Basic Info -->
            <section class="pe-form-section">
                <h2>Basic Information</h2>
                <div class="pe-form-grid">
                    <div class="pe-form-group">
                        <label>Space Name *</label>
                        <input v-model="form.name" type="text" placeholder="e.g., Mall Road Parking" required />
                    </div>
                    <div class="pe-form-group">
                        <label>City *</label>
                        <select v-model="form.city" required>
                            <option value="">Select city</option>
                            <option value="Shimla">Shimla</option>
                            <option value="Manali">Manali</option>
                            <option value="Mussoorie">Mussoorie</option>
                            <option value="Nainital">Nainital</option>
                            <option value="Dharamshala">Dharamshala</option>
                            <option value="Dalhousie">Dalhousie</option>
                        </select>
                    </div>
                </div>
                <div class="pe-form-group">
                    <label>Address *</label>
                    <input v-model="form.address" type="text" placeholder="Full address" required />
                </div>
                <div class="pe-form-group">
                    <label>Description</label>
                    <textarea v-model="form.description" rows="3" placeholder="Describe your parking space..."></textarea>
                </div>
            </section>

            <!-- Location -->
            <section class="pe-form-section">
                <h2>Location Coordinates</h2>
                <div class="pe-form-grid">
                    <div class="pe-form-group">
                        <label>Latitude</label>
                        <input v-model="form.latitude" type="number" step="0.0000001" placeholder="e.g., 31.1048" />
                    </div>
                    <div class="pe-form-group">
                        <label>Longitude</label>
                        <input v-model="form.longitude" type="number" step="0.0000001" placeholder="e.g., 77.1734" />
                    </div>
                </div>
            </section>

            <!-- Slots & Pricing -->
            <section class="pe-form-section">
                <h2>Slots Configuration</h2>
                <p class="pe-form-description">Specify the number of slots for each vehicle type. This helps drivers find appropriate parking.</p>

                <div class="pe-slot-config-grid">
                    <div class="pe-slot-type-card">
                        <div class="pe-slot-type-icon">🚗</div>
                        <div class="pe-slot-type-info">
                            <label>Car Slots</label>
                            <input v-model.number="form.slots.car" type="number" min="0" placeholder="0" />
                        </div>
                    </div>
                    <div class="pe-slot-type-card">
                        <div class="pe-slot-type-icon">🏍️</div>
                        <div class="pe-slot-type-info">
                            <label>Bike Slots</label>
                            <input v-model.number="form.slots.bike" type="number" min="0" placeholder="0" />
                        </div>
                    </div>
                    <div class="pe-slot-type-card">
                        <div class="pe-slot-type-icon">🚙</div>
                        <div class="pe-slot-type-info">
                            <label>SUV Slots</label>
                            <input v-model.number="form.slots.suv" type="number" min="0" placeholder="0" />
                        </div>
                    </div>
                    <div class="pe-slot-type-card">
                        <div class="pe-slot-type-icon">🚌</div>
                        <div class="pe-slot-type-info">
                            <label>Bus Slots</label>
                            <input v-model.number="form.slots.bus" type="number" min="0" placeholder="0" />
                        </div>
                    </div>
                </div>

                <div class="pe-total-slots-display">
                    <span class="pe-total-label">Total Slots:</span>
                    <span class="pe-total-value">{{ totalSlots }}</span>
                </div>
            </section>

            <!-- Pricing -->
            <section class="pe-form-section">
                <h2>Pricing</h2>
                <div class="pe-form-grid">
                    <div class="pe-form-group">
                        <label>Price per Hour (₹) *</label>
                        <input v-model="form.price_per_hour" type="number" min="0" placeholder="e.g., 50" required />
                    </div>
                    <div class="pe-form-group">
                        <label>Price per Day (₹)</label>
                        <input v-model="form.price_per_day" type="number" min="0" placeholder="e.g., 300" />
                    </div>
                </div>
            </section>

            <!-- Amenities -->
            <section class="pe-form-section">
                <h2>Amenities</h2>
                <div class="pe-amenities-grid">
                    <label v-for="amenity in amenityOptions" :key="amenity" class="pe-amenity-checkbox">
                        <input type="checkbox" :value="amenity" v-model="form.amenities" />
                        <span class="pe-amenity-label">{{ amenity }}</span>
                    </label>
                </div>
            </section>

            <!-- Operating Hours -->
            <section class="pe-form-section">
                <h2>Operating Hours</h2>
                <div class="pe-hours-grid">
                    <div v-for="day in days" :key="day" class="pe-hours-row">
                        <span class="pe-hours-day">{{ day }}</span>
                        <div class="pe-hours-inputs">
                            <input v-model="form.operating_hours[day].open" type="time" />
                            <span>to</span>
                            <input v-model="form.operating_hours[day].close" type="time" />
                        </div>
                        <label class="pe-checkbox">
                            <input type="checkbox" v-model="form.operating_hours[day].closed" />
                            Closed
                        </label>
                    </div>
                </div>
            </section>

            <!-- Submit -->
            <div class="pe-form-actions">
                <Link :href="route('owner.spaces.index')" class="pe-btn pe-btn--ghost">Cancel</Link>
                <button type="submit" class="pe-btn pe-btn--neon" :disabled="form.processing">
                    {{ form.processing ? 'Creating...' : 'Create Space' }}
                </button>
            </div>
        </form>
    </OwnerLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import OwnerLayout from '@/Components/Owner/OwnerLayout.vue'

const form = useForm({
    name: '',
    description: '',
    address: '',
    city: '',
    latitude: '',
    longitude: '',
    slots: {
        car: 0,
        bike: 0,
        suv: 0,
        bus: 0,
    },
    price_per_hour: '',
    price_per_day: '',
    amenities: [],
    operating_hours: {
        Monday: { open: '06:00', close: '22:00', closed: false },
        Tuesday: { open: '06:00', close: '22:00', closed: false },
        Wednesday: { open: '06:00', close: '22:00', closed: false },
        Thursday: { open: '06:00', close: '22:00', closed: false },
        Friday: { open: '06:00', close: '22:00', closed: false },
        Saturday: { open: '06:00', close: '22:00', closed: false },
        Sunday: { open: '06:00', close: '22:00', closed: false },
    },
})

// Calculate total slots from all types
const totalSlots = computed(() => {
    return (form.slots.car || 0) +
           (form.slots.bike || 0) +
           (form.slots.suv || 0) +
           (form.slots.bus || 0)
})

const amenityOptions = [
    'CCTV', 'Security Guard', 'Covered Parking', 'Open Parking',
    'EV Charging', 'Car Wash', 'Valet Parking', 'Restaurant Nearby',
    'Restroom Nearby', 'Lift Access', 'Wheelchair Access', '24/7 Access'
]

const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']

function submitForm() {
    // Validate that at least one slot type has slots
    if (totalSlots.value < 1) {
        alert('Please specify at least one slot for any vehicle type')
        return
    }

    // Format operating_hours to string values before submitting
    const formData = { ...form }
    formData.operating_hours = {}
    Object.entries(form.operating_hours).forEach(([day, hours]) => {
        if (hours.closed) {
            formData.operating_hours[day] = 'Closed'
        } else {
            formData.operating_hours[day] = `${hours.open} - ${hours.close}`
        }
    })

    // Calculate total_slots from slots object
    formData.total_slots = totalSlots.value

    form.transform((data) => ({
        ...formData
    })).post(route('owner.spaces.store'), {
        onSuccess: () => {
            // Form submitted successfully
        },
        onError: (errors) => {
            console.error('Form errors:', errors)
        }
    })
}
</script>

<style scoped>
/* Page-specific styles - form styles are in global CSS */

.pe-form-description {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.pe-slot-config-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.pe-slot-type-card {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: border-color 0.2s;
}

.pe-slot-type-card:hover {
    border-color: #0d9488;
}

.pe-slot-type-icon {
    font-size: 1.5rem;
}

.pe-slot-type-info {
    flex: 1;
}

.pe-slot-type-info label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
}

.pe-slot-type-info input {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.pe-total-slots-display {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: #0d9488;
    color: white;
    border-radius: 6px;
    font-weight: 600;
}

.pe-total-value {
    font-size: 1.25rem;
}
</style>
