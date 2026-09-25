<template>
    <div class="pe-root">
        <!-- Auth Section -->
        <section class="pe-auth">
            <div class="pe-bg">
                <div class="pe-orb pe-orb--1"></div>
                <div class="pe-orb pe-orb--2"></div>
                <div class="pe-orb pe-orb--3"></div>
                <div class="pe-grid"></div>
                <div class="pe-scan"></div>
            </div>

            <div class="pe-auth__wrap">
                <div class="pe-auth__card">
                    <div class="text-center mb-8">
                        <div class="pe-badge mx-auto" style="margin-bottom:20px">
                            <span class="pe-dot pe-dot--green"></span>
                            Live in Shimla · Manali · Mussoorie
                        </div>
                        <h1 class="pe-h1-auth">Create Account</h1>
                        <p class="pe-sub-auth">Park smarter. Explore freely.</p>
                    </div>

                    <!-- Role Selection -->
                    <div class="pe-role-select mb-8">
                        <label class="pe-role-label">I want to</label>
                        <div class="pe-role-grid">
                            <button type="button" @click="form.role = 'driver'" :class="{ 'pe-role-active': form.role === 'driver' }" class="pe-role-btn">
                                <i class="bi bi-car-front fs-2 mb-2"></i><br>
                                <strong>Find Parking</strong>
                                <small> As a Driver</small>
                            </button>
                            <button type="button" @click="form.role = 'owner'" :class="{ 'pe-role-active': form.role === 'owner' }" class="pe-role-btn">
                                <i class="bi bi-building fs-2 mb-2"></i><br>
                                <strong>List My Space</strong>
                                <small> As an Owner</small>
                            </button>
                        </div>
                    </div>

                    <form @submit.prevent="submit">
                        <div class="pe-input-group">
                            <label class="pe-form-label">Full Name</label>
                            <input v-model="form.name" type="text" class="pe-input" placeholder="John Doe" :class="{ 'pe-input--error': form.errors.name }">
                            <div v-if="form.errors.name" class="pe-error">{{ form.errors.name }}</div>
                        </div>
                        <div class="pe-input-group">
                            <label class="pe-form-label">Email Address</label>
                            <input v-model="form.email" type="email" class="pe-input" placeholder="you@example.com" :class="{ 'pe-input--error': form.errors.email }">
                            <div v-if="form.errors.email" class="pe-error">{{ form.errors.email }}</div>
                        </div>
                        <div class="pe-input-group">
                            <label class="pe-form-label">Phone Number</label>
                            <input v-model="form.phone" type="tel" class="pe-input" placeholder="+91 98765 43210" :class="{ 'pe-input--error': form.errors.phone }">
                            <div v-if="form.errors.phone" class="pe-error">{{ form.errors.phone }}</div>
                        </div>
                        <div class="pe-input-group">
                            <label class="pe-form-label">Password</label>
                            <input v-model="form.password" type="password" class="pe-input" placeholder="Min. 8 characters" :class="{ 'pe-input--error': form.errors.password }">
                            <div v-if="form.errors.password" class="pe-error">{{ form.errors.password }}</div>
                        </div>
                        <div class="pe-input-group">
                            <label class="pe-form-label">Confirm Password</label>
                            <input v-model="form.password_confirmation" type="password" class="pe-input" placeholder="Repeat password">
                        </div>

                        <button type="submit" class="pe-btn pe-btn--ghost pe-btn--lg w-100 mt-5" :disabled="form.processing">
                            <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                            Create Account
                        </button>
                    </form>

                    <div class="pe-auth-footer">
                        Already have an account?
                        <Link :href="route('login')" class="pe-link">Sign In</Link>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'

const scrolled = ref(false)
const menuOpen = ref(false)

onMounted(() => window.addEventListener('scroll', () => { scrolled.value = window.scrollY > 50 }))
onUnmounted(() => window.removeEventListener('scroll', () => {}))

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    role: 'driver',
})

function submit() {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<style scoped>
/* Auth-specific styles - shared styles are imported via app.js from pe-styles.css */

.pe-root { font-family: 'Outfit', sans-serif; background: #060910; color: #E8EDF5; overflow-x: hidden; }

.pe-role-select { margin-bottom: 32px; }

.pe-role-btn strong {
    display: block;
    font-size: 0.95rem;
    margin-bottom: 4px;
}

.pe-role-btn small {
    display: block;
    font-size: 0.75rem;
    color: rgba(232, 237, 245, 0.4);
    margin-top: 4px;
}

.pe-form-label {
    color: #00D4FF;
    font-size: 0.82rem;
    font-weight: 500;
    margin-bottom: 8px;
    letter-spacing: 0.5px;
    display: block;
}

.pe-input {
    width: 100%;
    background: rgba(15, 20, 33, 0.9);
    border: 1px solid rgba(0, 212, 255, 0.15);
    border-radius: 12px;
    padding: 14px 18px;
    color: #E8EDF5;
    font-size: 1rem;
    transition: all .3s;
}

.pe-input:focus {
    border-color: #00D4FF;
    box-shadow: 0 0 0 4px rgba(0, 212, 255, 0.15);
    background: rgba(15, 20, 33, 1);
    outline: none;
}

.pe-input--error { border-color: #FF6B6B !important; }

.pe-error { color: #FF6B6B; font-size: 0.8rem; margin-top: 4px; }

.pe-input-group { margin-bottom: 20px; }

.pe-btn { width: 100%; justify-content: center; }
</style>
