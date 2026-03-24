<template>
    <div class="pe-root">
        <section class="pe-auth">
            <div class="pe-bg">
                <div class="pe-orb pe-orb--1"></div>
                <div class="pe-orb pe-orb--2"></div>
                <div class="pe-grid"></div>
                <div class="pe-scan"></div>
            </div>

            <div class="pe-auth__wrap">
                <div class="pe-auth__card">
                    <div class="text-center mb-8">
                        <div class="pe-badge mx-auto" style="margin-bottom:20px">
                            <span class="pe-dot pe-dot--green"></span>
                            Welcome back
                        </div>
                        <h1 class="pe-h1-auth">Sign In</h1>
                        <p class="pe-sub-auth">Continue your smart parking journey</p>
                    </div>

                    <form @submit.prevent="submit">
                        <div class="pe-input-group">
                            <label class="pe-form-label">Email Address</label>
                            <input v-model="form.email" type="email" class="pe-input" placeholder="you@example.com" :class="{ 'pe-input--error': form.errors.email }">
                            <div v-if="form.errors.email" class="pe-error">{{ form.errors.email }}</div>
                        </div>
                        <div class="pe-input-group">
                            <label class="pe-form-label">Password</label>
                            <input v-model="form.password" type="password" class="pe-input" placeholder="••••••••">
                            <div v-if="form.errors.password" class="pe-error">{{ form.errors.password }}</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <label class="pe-remember">
                                <input v-model="form.remember" type="checkbox" /> Remember me
                            </label>
                            <a href="#" class="pe-link" style="font-size:0.85rem">Forgot password?</a>
                        </div>

                        <button type="submit" class="pe-btn pe-btn--ghost pe-btn--lg w-100 mt-5" :disabled="form.processing">
                            <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                            Sign In
                        </button>
                    </form>

                    <div class="pe-auth-footer">
                        Don't have an account?
                        <Link :href="route('register')" class="pe-link">Create one free</Link>
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
    email: '',
    password: '',
    remember: false,
})

function submit() {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<style scoped>
/* Auth-specific styles - shared styles are imported via app.js from pe-styles.css */

.pe-root { font-family: 'Outfit', sans-serif; background: #060910; color: #E8EDF5; overflow-x: hidden; }

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

.pe-remember {
    display: flex;
    align-items: center;
    gap: 8px;
    color: rgba(232, 237, 245, 0.5);
    font-size: 0.9rem;
    cursor: pointer;
}

.pe-remember input { accent-color: #00D4FF; }

.pe-input-group { margin-bottom: 20px; }

.pe-btn { width: 100%; justify-content: center; }
</style>
