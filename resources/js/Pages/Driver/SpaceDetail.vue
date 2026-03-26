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
                    <Link :href="route('driver.bookings.index')" class="pe-btn pe-btn--ghost">My Bookings</Link>
                    <div class="pe-user-menu">
                        <span class="pe-user-avatar">{{ userInitials }}</span>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="pe-space-detail">
            <div class="pe-container">
                <!-- Back Link -->
                <Link :href="route('search')" class="pe-back-link">← Back to Search</Link>

                <div class="pe-space-grid">
                    <!-- Left Column - Details -->
                    <div class="pe-space-info">
                        <div class="pe-space-header">
                            <h1>{{ space.name }}</h1>
                            <div class="pe-space-verified" v-if="space.is_verified">
                                <span class="pe-verified-badge">✓ Verified</span>
                            </div>
                        </div>

                        <p class="pe-space-address">
                            📍 {{ space.address }}, {{ space.city }}
                        </p>

                        <!-- Stats -->
                        <div class="pe-space-stats">
                            <div class="pe-stat">
                                <span class="pe-stat__icon">🅿️</span>
                                <div>
                                    <span class="pe-stat__value">{{ availableSlots }}</span>
                                    <span class="pe-stat__label">Slots Available</span>
                                </div>
                            </div>
                            <div class="pe-stat">
                                <span class="pe-stat__icon">⭐</span>
                                <div>
                                    <span class="pe-stat__value">{{ space.rating || 'New' }}</span>
                                    <span class="pe-stat__label">Rating</span>
                                </div>
                            </div>
                            <div class="pe-stat">
                                <span class="pe-stat__icon">📅</span>
                                <div>
                                    <span class="pe-stat__value">{{ space.total_bookings || 0 }}</span>
                                    <span class="pe-stat__label">Bookings</span>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="pe-section">
                            <h2>About this Space</h2>
                            <p>{{ space.description || 'No description provided.' }}</p>
                        </div>

                        <!-- Amenities -->
                        <div class="pe-section" v-if="space.amenities && space.amenities.length">
                            <h2>Amenities</h2>
                            <div class="pe-amenities">
                                <span v-for="amenity in space.amenities" :key="amenity" class="pe-amenity-tag">
                                    {{ amenity }}
                                </span>
                            </div>
                        </div>

                        <!-- Operating Hours -->
                        <div class="pe-section">
                            <h2>Operating Hours</h2>
                            <div class="pe-hours-list">
                                <div v-for="(hours, day) in space.operating_hours" :key="day" class="pe-hours-row">
                                    <span class="pe-day">{{ day }}</span>
                                    <span class="pe-time">{{ hours }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Map -->
                        <div class="pe-section">
                            <h2>Location</h2>
                            <div class="pe-map">
                                <div class="pe-map-placeholder">
                                    <span>🗺️</span>
                                    <p>Map View</p>
                                    <small>Lat: {{ space.latitude }}, Long: {{ space.longitude }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Booking Card -->
                    <div class="pe-booking-card">
                        <div class="pe-price-tag">
                            <span class="pe-price">₹{{ space.price_per_hour }}</span>
                            <span class="pe-price-period">/hour</span>
                        </div>

                        <div class="pe-availability" :class="availableSlots > 0 ? 'available' : 'full'">
                            <span v-if="availableSlots > 0">{{ availableSlots }} slots available</span>
                            <span v-else>Fully booked</span>
                        </div>

                        <form @submit.prevent="proceedToCheckout" class="pe-booking-form">
                            <div class="pe-form-group">
                                <label>Select Date</label>
                                <input v-model="booking.date" type="date" :min="minDate" required />
                            </div>

                            <div class="pe-form-row">
                                <div class="pe-form-group">
                                    <label>Check In</label>
                                    <input v-model="booking.check_in" type="time" required />
                                </div>
                                <div class="pe-form-group">
                                    <label>Check Out</label>
                                    <input v-model="booking.check_out" type="time" required />
                                </div>
                            </div>

                            <div class="pe-form-group">
                                <label>Vehicle Type</label>
                                <select v-model="booking.vehicle_type" required>
                                    <option value="car">Car</option>
                                    <option value="bike">Bike</option>
                                    <option value="suv">SUV</option>
                                    <option value="bus">Bus</option>
                                </select>
                            </div>

                            <div class="pe-form-group">
                                <label>Vehicle Number *</label>
                                <input v-model="booking.vehicle_number" type="text" placeholder="e.g., HP01A1234" required />
                            </div>

                            <div class="pe-price-estimate" v-if="estimatedPrice">
                                <div class="pe-price-row">
                                    <span>Duration</span>
                                    <span>{{ duration }} hours</span>
                                </div>
                                <div class="pe-price-row">
                                    <span>Rate</span>
                                    <span>₹{{ space.price_per_hour }}/hr</span>
                                </div>
                                <div class="pe-price-total">
                                    <span>Estimated Total</span>
                                    <span>₹{{ estimatedPrice }}</span>
                                </div>
                            </div>

                            <button type="submit" class="pe-btn pe-btn--neon pe-btn--lg" :disabled="availableSlots === 0">
                                {{ availableSlots > 0 ? 'Book Now' : 'Not Available' }}
                            </button>
                        </form>

                        <p class="pe-secure-text">🔒 Secure payment via UPI, Card & Wallet</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Payment Modal -->
        <div v-if="showPaymentModal" class="pe-modal-overlay">
            <div class="pe-modal">
                <div class="pe-modal-header">
                    <h3>Complete Payment</h3>
                    <button @click="cancelReservation" class="pe-modal-close">×</button>
                </div>
                <div class="pe-modal-body">
                    <div class="pe-reservation-timer">
                        <span class="pe-timer-label">Time remaining to complete payment:</span>
                        <span class="pe-timer-value">{{ formatTime(timeRemaining) }}</span>
                    </div>
                    <div class="pe-reservation-summary">
                        <p><strong>Slot:</strong> {{ reservationData?.slot_number }}</p>
                        <p><strong>Amount:</strong> ₹{{ reservationData?.amount }}</p>
                    </div>
                    <div class="pe-payment-info">
                        <p>Payment integration (Razorpay) would be initialized here.</p>
                        <p class="pe-payment-note">Click confirm to simulate a successful payment.</p>
                    </div>
                    <button @click="confirmPayment" class="pe-btn pe-btn--neon pe-btn--lg" :disabled="paymentProcessing">
                        {{ paymentProcessing ? 'Processing...' : `Pay ₹${reservationData?.amount}` }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'

// Get route function from window (available via Ziggy)
const route = window.route

const props = defineProps({
    space: Object,
    user: Object,
    flash: Object,
    reservation: Object,
})

const booking = reactive({
    date: '',
    check_in: '',
    check_out: '',
    vehicle_type: 'car',
    vehicle_number: '',
})

const minDate = computed(() => {
    const today = new Date()
    return today.toISOString().split('T')[0]
})

const availableSlots = computed(() => {
    // Would be calculated from backend based on bookings for selected date
    return props.space.total_slots - (props.space.current_bookings || 0)
})

const duration = computed(() => {
    if (!booking.check_in || !booking.check_out) return 0

    const inTime = parseTime(booking.check_in)
    const outTime = parseTime(booking.check_out)

    if (outTime <= inTime) return 0
    return (outTime - inTime) / (1000 * 60 * 60)
})

const estimatedPrice = computed(() => {
    if (duration.value <= 0) return 0
    return Math.round(duration.value * props.space.price_per_hour)
})

function parseTime(timeStr) {
    const [hours, minutes] = timeStr.split(':').map(Number)
    const date = new Date()
    date.setHours(hours, minutes, 0, 0)
    return date.getTime()
}

const userInitials = computed(() => {
    if (!props.user) return 'U'
    const names = props.user.name?.split(' ') || ['U']
    return names.map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

function proceedToCheckout() {
    // Post to reserve the slot first
    const checkInDateTime = `${booking.date}T${booking.check_in}:00`
    const checkOutDateTime = `${booking.date}T${booking.check_out}:00`

    const form = useForm({
        space_id: props.space.id,
        check_in: checkInDateTime,
        check_out: checkOutDateTime,
        vehicle_type: booking.vehicle_type,
        vehicle_number: booking.vehicle_number,
        payment_method: 'upi',
    })

    form.post(window.route('driver.bookings.reserve'), {
        onSuccess: () => {
            // The server returns a redirect to the space detail page with reservation data
            // Inertia will handle the redirect automatically
            // The reservation will be available as a prop when the page loads
        },
        onError: (errors) => {
            console.error('Reservation errors:', errors)
            alert('Reservation failed: ' + Object.values(errors).join(', '))
        }
    })
}

// Payment modal state
const showPaymentModal = ref(false)
const reservationData = ref(null)
const paymentProcessing = ref(false)
const paymentTimer = ref(null)
const timeRemaining = ref(0)

function startPaymentTimer(expiresInSec) {
    timeRemaining.value = expiresInSec
    paymentTimer.value = setInterval(() => {
        timeRemaining.value--
        if (timeRemaining.value <= 0) {
            clearInterval(paymentTimer.value)
            showPaymentModal.value = false
            alert('Reservation expired. Please try again.')
        }
    }, 1000)
}

function formatTime(seconds) {
    const mins = Math.floor(seconds / 60)
    const secs = seconds % 60
    return `${mins}:${secs.toString().padStart(2, '0')}`
}

async function confirmPayment() {
    paymentProcessing.value = true

    // In a real implementation, this would create a Razorpay order
    // For now, we'll simulate the payment and call the confirm endpoint
    try {
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        if (!csrfToken) {
            alert('CSRF token not found. Please refresh the page.')
            paymentProcessing.value = false
            return
        }

        // Call the confirm endpoint (simulating successful payment)
        const response = await fetch(window.route('driver.bookings.confirm'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                razorpay_payment_id: 'pay_' + Date.now(),
                razorpay_order_id: 'order_' + Date.now(),
                razorpay_signature: 'signature_placeholder'
            })
        })

        if (response.ok) {
            window.location.href = window.route('driver.bookings.index') + '?success=true'
        } else {
            const data = await response.json()
            alert('Payment confirmation failed: ' + (data.errors?.booking || 'Unknown error'))
            paymentProcessing.value = false
        }
    } catch (error) {
        console.error('Payment error:', error)
        alert('Payment failed. Please try again.')
        paymentProcessing.value = false
    }
}

function cancelReservation() {
    if (paymentTimer.value) {
        clearInterval(paymentTimer.value)
    }
    showPaymentModal.value = false
    reservationData.value = null
}

// Check for reservation on mount - if reservation prop exists, show modal
onMounted(() => {
    if (props.reservation) {
        showPaymentModal.value = true
        reservationData.value = props.reservation
        startPaymentTimer(props.reservation.expires_in_sec)
    }
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

.pe-nav__actions {
    display: flex;
    align-items: center;
    gap: 16px;
}

.pe-user-menu {
    display: flex;
    align-items: center;
    gap: 10px;
}

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

.pe-space-detail {
    padding: 100px 24px 60px;
}

.pe-container {
    max-width: 1100px;
    margin: 0 auto;
}

.pe-back-link {
    display: inline-block;
    color: rgba(232, 237, 245, 0.5);
    text-decoration: none;
    font-size: 0.9rem;
    margin-bottom: 24px;
}
.pe-back-link:hover { color: #00D4FF; }

.pe-space-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 32px;
}

.pe-space-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.pe-space-header h1 {
    font-family: 'Syne', sans-serif;
    font-size: 2rem;
    font-weight: 800;
    color: #E8EDF5;
}

.pe-verified-badge {
    background: rgba(0, 229, 160, 0.15);
    color: #00E5A0;
    padding: 6px 12px;
    border-radius: 100px;
    font-size: 0.8rem;
    font-weight: 600;
}

.pe-space-address {
    color: rgba(232, 237, 245, 0.6);
    font-size: 1rem;
    margin-bottom: 24px;
}

.pe-space-stats {
    display: flex;
    gap: 24px;
    margin-bottom: 32px;
    padding: 20px;
    background: rgba(10, 14, 24, 0.9);
    border: 1px solid rgba(0, 212, 255, 0.1);
    border-radius: 16px;
}

.pe-stat {
    display: flex;
    align-items: center;
    gap: 12px;
}

.pe-stat__icon {
    width: 44px;
    height: 44px;
    background: rgba(0, 212, 255, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.pe-stat__value {
    display: block;
    font-family: 'Syne', sans-serif;
    font-size: 1.3rem;
    font-weight: 700;
    color: #E8EDF5;
}

.pe-stat__label {
    display: block;
    font-size: 0.75rem;
    color: rgba(232, 237, 245, 0.5);
}

.pe-section {
    margin-bottom: 28px;
}

.pe-section h2 {
    font-family: 'Syne', sans-serif;
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 14px;
    color: #E8EDF5;
}

.pe-section p {
    color: rgba(232, 237, 245, 0.7);
    line-height: 1.7;
}

.pe-amenities {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.pe-amenity-tag {
    background: rgba(0, 212, 255, 0.1);
    border: 1px solid rgba(0, 212, 255, 0.2);
    color: #00D4FF;
    padding: 8px 16px;
    border-radius: 100px;
    font-size: 0.85rem;
}

.pe-hours-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.pe-hours-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 16px;
    background: rgba(10, 14, 24, 0.8);
    border-radius: 8px;
}

.pe-day { font-weight: 500; color: #E8EDF5; }
.pe-time { color: rgba(232, 237, 245, 0.6); }

.pe-map {
    height: 200px;
    background: rgba(10, 14, 24, 0.9);
    border: 1px solid rgba(0, 212, 255, 0.1);
    border-radius: 16px;
    overflow: hidden;
}

.pe-map-placeholder {
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: rgba(232, 237, 245, 0.4);
}
.pe-map-placeholder span { font-size: 2.5rem; margin-bottom: 8px; }
.pe-map-placeholder small { font-size: 0.8rem; margin-top: 4px; }

/* Booking Card */
.pe-booking-card {
    background: rgba(10, 14, 24, 0.95);
    border: 1px solid rgba(0, 212, 255, 0.15);
    border-radius: 24px;
    padding: 28px;
    position: sticky;
    top: 100px;
}

.pe-price-tag {
    display: flex;
    align-items: baseline;
    gap: 4px;
    margin-bottom: 12px;
}

.pe-price {
    font-family: 'Syne', sans-serif;
    font-size: 2.2rem;
    font-weight: 800;
    color: #E8EDF5;
}

.pe-price-period {
    font-size: 1rem;
    color: rgba(232, 237, 245, 0.5);
}

.pe-availability {
    padding: 10px 16px;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 24px;
    text-align: center;
}

.pe-availability.available {
    background: rgba(0, 229, 160, 0.12);
    color: #00E5A0;
}

.pe-availability.full {
    background: rgba(255, 107, 107, 0.12);
    color: #FF6B6B;
}

.pe-booking-form { margin-bottom: 20px; }

.pe-form-group { margin-bottom: 16px; }
.pe-form-group label {
    display: block;
    color: rgba(232, 237, 245, 0.7);
    font-size: 0.85rem;
    margin-bottom: 8px;
}

.pe-form-group input,
.pe-form-group select {
    width: 100%;
    background: rgba(15, 20, 33, 0.9);
    border: 1px solid rgba(0, 212, 255, 0.15);
    border-radius: 12px;
    padding: 14px 16px;
    color: #E8EDF5;
    font-size: 1rem;
}
.pe-form-group input:focus,
.pe-form-group select:focus {
    outline: none;
    border-color: #00D4FF;
}

.pe-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.pe-price-estimate {
    background: rgba(0, 212, 255, 0.08);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 20px;
}

.pe-price-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
    color: rgba(232, 237, 245, 0.6);
    margin-bottom: 8px;
}

.pe-price-total {
    display: flex;
    justify-content: space-between;
    font-weight: 700;
    font-size: 1.1rem;
    color: #E8EDF5;
    padding-top: 12px;
    border-top: 1px solid rgba(0, 212, 255, 0.15);
}

.pe-btn--lg {
    width: 100%;
    padding: 16px;
    font-size: 1.05rem;
    justify-content: center;
}

.pe-secure-text {
    text-align: center;
    font-size: 0.8rem;
    color: rgba(232, 237, 245, 0.4);
}

@media (max-width: 900px) {
    .pe-space-grid {
        grid-template-columns: 1fr;
    }
    .pe-booking-card {
        position: static;
    }
    .pe-space-stats {
        flex-wrap: wrap;
    }
}

/* Modal Styles */
.pe-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    backdrop-filter: blur(4px);
}

.pe-modal {
    background: #0a0e18;
    border: 1px solid rgba(0, 212, 255, 0.2);
    border-radius: 24px;
    width: 100%;
    max-width: 420px;
    padding: 0;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.pe-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.pe-modal-header h3 {
    font-family: 'Syne', sans-serif;
    font-size: 1.2rem;
    font-weight: 700;
    color: #E8EDF5;
    margin: 0;
}

.pe-modal-close {
    background: none;
    border: none;
    color: rgba(232, 237, 245, 0.5);
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    line-height: 1;
}

.pe-modal-close:hover {
    color: #E8EDF5;
}

.pe-modal-body {
    padding: 24px;
}

.pe-reservation-timer {
    background: rgba(255, 107, 107, 0.1);
    border: 1px solid rgba(255, 107, 107, 0.2);
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    margin-bottom: 20px;
}

.pe-timer-label {
    display: block;
    font-size: 0.85rem;
    color: rgba(232, 237, 245, 0.6);
    margin-bottom: 8px;
}

.pe-timer-value {
    font-family: 'Syne', sans-serif;
    font-size: 1.8rem;
    font-weight: 800;
    color: #FF6B6B;
}

.pe-reservation-summary {
    background: rgba(0, 212, 255, 0.08);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 20px;
}

.pe-reservation-summary p {
    display: flex;
    justify-content: space-between;
    margin: 0 0 8px 0;
    color: rgba(232, 237, 245, 0.7);
}

.pe-reservation-summary p:last-child {
    margin-bottom: 0;
}

.pe-reservation-summary strong {
    color: #E8EDF5;
}

.pe-payment-info {
    text-align: center;
    margin-bottom: 20px;
    color: rgba(232, 237, 245, 0.6);
}

.pe-payment-note {
    font-size: 0.85rem;
    color: rgba(0, 212, 255, 0.7);
    margin-top: 8px;
}
</style>
