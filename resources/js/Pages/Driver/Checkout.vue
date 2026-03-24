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
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="pe-checkout">
            <div class="pe-container">
                <h1>Complete Your Booking</h1>

                <div class="pe-checkout-grid">
                    <!-- Booking Summary -->
                    <div class="pe-summary-card">
                        <h2>Booking Summary</h2>

                        <div class="pe-space-preview">
                            <div class="pe-space-icon">🅿️</div>
                            <div class="pe-space-details">
                                <h3>{{ space.name }}</h3>
                                <p>{{ space.address }}, {{ space.city }}</p>
                            </div>
                        </div>

                        <div class="pe-booking-details">
                            <div class="pe-detail-row">
                                <span>Date</span>
                                <span>{{ booking.date }}</span>
                            </div>
                            <div class="pe-detail-row">
                                <span>Check In</span>
                                <span>{{ booking.check_in }}</span>
                            </div>
                            <div class="pe-detail-row">
                                <span>Check Out</span>
                                <span>{{ booking.check_out }}</span>
                            </div>
                            <div class="pe-detail-row">
                                <span>Duration</span>
                                <span>{{ duration }} hours</span>
                            </div>
                            <div class="pe-detail-row">
                                <span>Vehicle</span>
                                <span class="pe-vehicle-type">{{ booking.vehicle_type }}</span>
                            </div>
                        </div>

                        <div class="pe-price-breakdown">
                            <div class="pe-price-row">
                                <span>Parking Fee</span>
                                <span>₹{{ totalPrice }}</span>
                            </div>
                            <div class="pe-price-row">
                                <span>Service Fee</span>
                                <span>₹10</span>
                            </div>
                            <div class="pe-price-total">
                                <span>Total</span>
                                <span>₹{{ totalPrice + 10 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="pe-payment-card">
                        <h2>Payment Method</h2>

                        <div class="pe-payment-options">
                            <label class="pe-payment-option" :class="{ selected: paymentMethod === 'upi' }">
                                <input type="radio" v-model="paymentMethod" value="upi" />
                                <span class="pe-payment-icon">📱</span>
                                <span class="pe-payment-label">UPI</span>
                            </label>
                            <label class="pe-payment-option" :class="{ selected: paymentMethod === 'card' }">
                                <input type="radio" v-model="paymentMethod" value="card" />
                                <span class="pe-payment-icon">💳</span>
                                <span class="pe-payment-label">Card</span>
                            </label>
                            <label class="pe-payment-option" :class="{ selected: paymentMethod === 'wallet' }">
                                <input type="radio" v-model="paymentMethod" value="wallet" />
                                <span class="pe-payment-icon">👛</span>
                                <span class="pe-payment-label">Wallet</span>
                            </label>
                        </div>

                        <!-- UPI Form -->
                        <div v-if="paymentMethod === 'upi'" class="pe-payment-form">
                            <div class="pe-form-group">
                                <label>UPI ID</label>
                                <input v-model="upiId" type="text" placeholder="yourname@upi" />
                            </div>
                        </div>

                        <!-- Card Form -->
                        <div v-if="paymentMethod === 'card'" class="pe-payment-form">
                            <div class="pe-form-group">
                                <label>Card Number</label>
                                <input v-model="cardNumber" type="text" placeholder="1234 5678 9012 3456" maxlength="19" />
                            </div>
                            <div class="pe-form-row">
                                <div class="pe-form-group">
                                    <label>Expiry</label>
                                    <input v-model="cardExpiry" type="text" placeholder="MM/YY" maxlength="5" />
                                </div>
                                <div class="pe-form-group">
                                    <label>CVV</label>
                                    <input v-model="cardCvv" type="text" placeholder="123" maxlength="4" />
                                </div>
                            </div>
                            <div class="pe-form-group">
                                <label>Cardholder Name</label>
                                <input v-model="cardName" type="text" placeholder="John Doe" />
                            </div>
                        </div>

                        <!-- Wallet -->
                        <div v-if="paymentMethod === 'wallet'" class="pe-payment-form">
                            <p class="pe-wallet-info">Your wallet balance will be used for this payment.</p>
                        </div>

                        <button @click="processPayment" class="pe-btn pe-btn--neon pe-btn--lg" :disabled="processing">
                            {{ processing ? 'Processing...' : `Pay ₹${totalPrice + 10}` }}
                        </button>

                        <p class="pe-terms">
                            By booking, you agree to our <a href="#">Terms of Service</a> and <a href="#">Cancellation Policy</a>
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    space: Object,
    bookingData: Object,
})

const paymentMethod = ref('upi')
const processing = ref(false)

// Payment form fields
const upiId = ref('')
const cardNumber = ref('')
const cardExpiry = ref('')
const cardCvv = ref('')
const cardName = ref('')

const booking = computed(() => props.bookingData || {
    date: '2025-02-28',
    check_in: '10:00',
    check_out: '14:00',
    vehicle_type: 'car',
})

const duration = computed(() => {
    const inTime = parseTime(booking.value.check_in)
    const outTime = parseTime(booking.value.check_out)
    return (outTime - inTime) / (1000 * 60 * 60)
})

const totalPrice = computed(() => {
    return Math.round(duration.value * (props.space?.price_per_hour || 50))
})

function parseTime(timeStr) {
    const [hours, minutes] = timeStr.split(':').map(Number)
    const date = new Date()
    date.setHours(hours, minutes, 0, 0)
    return date.getTime()
}

function processPayment() {
    processing.value = true

    // Simulate payment processing
    setTimeout(() => {
        // Would POST to backend to create booking
        const bookingId = Math.floor(Math.random() * 10000)

        // Redirect to success page or my bookings
        window.location.href = route('driver.bookings.index') + '?success=true'
    }, 2000)
}
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
.pe-nav__actions { display: flex; gap: 16px; }

.pe-checkout {
    padding: 100px 24px 60px;
}

.pe-container { max-width: 900px; margin: 0 auto; }

.pe-checkout h1 {
    font-family: 'Syne', sans-serif;
    font-size: 1.8rem;
    font-weight: 800;
    margin-bottom: 32px;
    text-align: center;
}

.pe-checkout-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.pe-summary-card,
.pe-payment-card {
    background: rgba(10, 14, 24, 0.95);
    border: 1px solid rgba(0, 212, 255, 0.15);
    border-radius: 24px;
    padding: 28px;
}

.pe-summary-card h2,
.pe-payment-card h2 {
    font-family: 'Syne', sans-serif;
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.pe-space-preview {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: rgba(15, 20, 33, 0.8);
    border-radius: 16px;
    margin-bottom: 24px;
}

.pe-space-icon {
    width: 56px;
    height: 56px;
    background: rgba(0, 212, 255, 0.1);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.pe-space-details h3 {
    font-family: 'Syne', sans-serif;
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 4px;
}

.pe-space-details p {
    font-size: 0.85rem;
    color: rgba(232, 237, 245, 0.5);
}

.pe-booking-details {
    margin-bottom: 24px;
}

.pe-detail-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.03);
}

.pe-detail-row span:first-child {
    color: rgba(232, 237, 245, 0.5);
}

.pe-vehicle-type {
    text-transform: capitalize;
    color: #00D4FF;
}

.pe-price-breakdown {
    background: rgba(0, 212, 255, 0.08);
    border-radius: 14px;
    padding: 16px;
}

.pe-price-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 0.95rem;
    color: rgba(232, 237, 245, 0.7);
}

.pe-price-total {
    display: flex;
    justify-content: space-between;
    padding-top: 12px;
    margin-top: 12px;
    border-top: 1px solid rgba(0, 212, 255, 0.15);
    font-weight: 700;
    font-size: 1.2rem;
}

.pe-payment-options {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
}

.pe-payment-option {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 16px;
    background: rgba(15, 20, 33, 0.8);
    border: 2px solid transparent;
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.pe-payment-option:hover { border-color: rgba(0, 212, 255, 0.3); }
.pe-payment-option.selected { border-color: #00D4FF; background: rgba(0, 212, 255, 0.1); }
.pe-payment-option input { display: none; }

.pe-payment-icon { font-size: 1.5rem; }
.pe-payment-label { font-size: 0.85rem; color: #E8EDF5; }

.pe-payment-form { margin-bottom: 24px; }

.pe-form-group { margin-bottom: 16px; }
.pe-form-group label {
    display: block;
    color: rgba(232, 237, 245, 0.7);
    font-size: 0.85rem;
    margin-bottom: 8px;
}

.pe-form-group input {
    width: 100%;
    background: rgba(15, 20, 33, 0.9);
    border: 1px solid rgba(0, 212, 255, 0.15);
    border-radius: 12px;
    padding: 14px 16px;
    color: #E8EDF5;
    font-size: 1rem;
}
.pe-form-group input:focus {
    outline: none;
    border-color: #00D4FF;
}

.pe-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.pe-wallet-info {
    text-align: center;
    color: rgba(232, 237, 245, 0.6);
    padding: 20px;
}

.pe-btn--lg {
    width: 100%;
    padding: 16px;
    font-size: 1.05rem;
    justify-content: center;
    margin-bottom: 16px;
}

.pe-terms {
    text-align: center;
    font-size: 0.8rem;
    color: rgba(232, 237, 245, 0.4);
}

.pe-terms a { color: #00D4FF; text-decoration: none; }

@media (max-width: 768px) {
    .pe-checkout-grid { grid-template-columns: 1fr; }
}
</style>
