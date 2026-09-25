<template>
    <div class="pe-root">
        <!-- Navbar -->
        <nav class="pe-nav" :class="{ 'pe-nav--scrolled': scrolled }">
            <div class="pe-nav__inner">
                <Link href="/" class="pe-nav__logo">
                    <svg class="pe-logo-svg" viewBox="0 0 200 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="iG" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#00D4FF"/>
                                <stop offset="100%" style="stop-color:#7B4FFF"/>
                            </linearGradient>
                            <linearGradient id="tG" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#00D4FF"/>
                                <stop offset="100%" style="stop-color:#7B4FFF"/>
                            </linearGradient>
                            <filter id="ng">
                                <feGaussianBlur stdDeviation="2" result="b"/>
                                <feMerge>
                                    <feMergeNode in="b"/>
                                    <feMergeNode in="SourceGraphic"/>
                                </feMerge>
                            </filter>
                        </defs>
                        <rect x="4" y="4" width="42" height="42" rx="12" fill="url(#iG)" filter="url(#ng)"/>
                        <rect x="4" y="4" width="42" height="42" rx="12" fill="none" stroke="rgba(0,212,255,0.4)" stroke-width="1"/>
                        <text x="14" y="35" font-family="Arial Black,sans-serif" font-weight="900" font-size="26" fill="#080B14">P</text>
                        <text x="56" y="30" font-family="Arial Black,sans-serif" font-weight="900" font-size="20" fill="white" letter-spacing="-0.5">Park</text>
                        <text x="103" y="30" font-family="Arial Black,sans-serif" font-weight="900" font-size="20" fill="url(#tG)" filter="url(#ng)" letter-spacing="-0.5">Ease</text>
                    </svg>
                </Link>
                <div class="pe-nav__links">
                    <Link :href="route('search')" class="neon-cyan">Search</Link>
                    <Link v-if="$page.props.auth?.user" :href="route('driver.bookings.index')" class="text-white">My Bookings</Link>
                    <div class="pe-nav__sep"></div>
                    <template v-if="$page.props.auth?.user">
                        <Link :href="route('logout')" method="post" class="pe-btn pe-btn--ghost">Logout</Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="pe-btn pe-btn--ghost">Sign In</Link>
                        <Link :href="route('register')" class="pe-btn pe-btn--neon">Get Started</Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Search Hero -->
        <section class="pe-search-hero">
            <div class="pe-bg">
                <div class="pe-orb pe-orb--1"></div>
                <div class="pe-orb pe-orb--2"></div>
                <div class="pe-grid"></div>
            </div>
            <div class="pe-container">
                <h1 class="pe-h1">Find <span class="neon-cyan">Parking</span> Near You</h1>
                <p class="pe-sub">Search parking spaces in hill stations across India</p>

                <!-- Search Form -->
                <form @submit.prevent="search" class="pe-search-form">
                    <div class="pe-search-row">
                        <div class="pe-search-field pe-search-field--large">
                            <label>Location</label>
                            <div class="pe-search-input-wrap">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                <input
                                    v-model="form.location"
                                    type="text"
                                    placeholder="Enter city or location"
                                />
                            </div>
                        </div>
                        <div class="pe-search-field">
                            <label>Date</label>
                            <input v-model="form.date" type="date" />
                        </div>
                        <div class="pe-search-field">
                            <label>Time</label>
                            <input v-model="form.time" type="time" />
                        </div>
                        <button type="submit" class="pe-btn pe-btn--neon pe-search-submit">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>
                            Search
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Results -->
        <section class="pe-results">
            <div class="pe-container">
                <div class="pe-results__header">
                    <div class="pe-results__count">
                        <span class="neon-cyan">{{ spaces.data.length }}</span> parking spaces found
                        <span v-if="filters.location"> in "{{ filters.location }}"</span>
                    </div>
                    <div class="pe-results__sort">
                        <select v-model="sortOption" @change="applySort">
                            <option value="price_asc">Price: Low to High</option>
                            <option value="price_desc">Price: High to Low</option>
                            <option value="slots_desc">Most Slots</option>
                            <option value="recent">Most Recent</option>
                        </select>
                    </div>
                </div>

                <div v-if="spaces.data.length > 0" class="pe-spaces-grid">
                    <div v-for="space in spaces.data" :key="space.id" class="pe-space-card">
                        <div class="pe-space-card__image">
                            <img v-if="space.images && space.images.length" :src="space.images[0]" :alt="space.name" />
                            <div v-else class="pe-space-card__placeholder">🅿️</div>
                            <div class="pe-space-card__badge" :class="space.total_slots > 5 ? 'green' : 'yellow'">
                                {{ space.total_slots }} slots
                            </div>
                        </div>
                        <div class="pe-space-card__content">
                            <h3>{{ space.name }}</h3>
                            <p class="pe-space-card__address">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                </svg>
                                {{ space.address }}, {{ space.city }}
                            </p>
                            <div class="pe-space-card__amenities">
                                <span v-for="amenity in (space.amenities || []).slice(0, 3)" :key="amenity" class="pe-amenity-tag">
                                    {{ amenity }}
                                </span>
                            </div>
                            <div class="pe-space-card__footer">
                                <div class="pe-space-card__price">
                                    <span class="neon-cyan">₹{{ space.price_per_hour }}</span>/hour
                                </div>
                                <Link :href="route('spaces.show', space.id)" class="pe-btn pe-btn--neon pe-btn--sm">
                                    View Details
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="pe-no-results">
                    <div class="pe-no-results__icon">🔍</div>
                    <h3>No parking spaces found</h3>
                    <p>Try adjusting your search criteria or check back later</p>
                </div>

                <!-- Pagination -->
                <div v-if="spaces.links && spaces.links.length > 3" class="pe-pagination">
                    <Link
                        v-for="link in spaces.links"
                        :key="link.label"
                        :href="link.url"
                        class="pe-pagination__link"
                        :class="{ 'active': link.active, 'disabled': !link.url }"
                        v-html="link.label"
                    />
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="pe-footer">
            <div class="pe-container">
                <div class="pe-footer__bottom">
                    <span>© 2025 ParkEase · Built with ❤️ in Shimla, India</span>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    spaces: Object,
    filters: Object,
    cities: Array,
})

const scrolled = ref(false)
const form = ref({
    location: props.filters?.location || '',
    date: '',
    time: '',
})
const sortOption = ref(props.filters?.sort || 'price_asc')

onMounted(() => {
    window.addEventListener('scroll', () => {
        scrolled.value = window.scrollY > 50
    })
})

function search() {
    const params = new URLSearchParams()
    if (form.value.location) params.set('location', form.value.location)
    if (form.value.date) params.set('date', form.value.date)
    if (form.value.time) params.set('time', form.value.time)
    params.set('sort', sortOption.value)

    window.location.href = route('search') + '?' + params.toString()
}

function applySort() {
    search()
}
</script>

<style scoped>
/* Using shared styles from pe-styles.css imported in app.js */

.pe-search-hero {
    padding: 160px 24px 80px;
    text-align: center;
    position: relative;
}

.pe-search-hero .pe-h1 {
    font-size: clamp(2.5rem, 5vw, 4rem);
    margin-bottom: 12px;
}

.pe-search-hero .pe-sub {
    color: rgba(232, 237, 245, 0.6);
    font-size: 1.1rem;
    margin-bottom: 40px;
}

.pe-search-form {
    max-width: 900px;
    margin: 0 auto;
    background: rgba(10, 14, 24, 0.9);
    border: 1px solid rgba(0, 212, 255, 0.2);
    border-radius: 20px;
    padding: 24px;
    backdrop-filter: blur(20px);
}

.pe-search-row {
    display: flex;
    gap: 16px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.pe-search-field {
    flex: 1;
    min-width: 150px;
}

.pe-search-field--large {
    flex: 2;
    min-width: 280px;
}

.pe-search-field label {
    display: block;
    color: #00D4FF;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.pe-search-input-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(15, 20, 33, 0.9);
    border: 1px solid rgba(0, 212, 255, 0.15);
    border-radius: 12px;
    padding: 12px 16px;
}

.pe-search-input-wrap svg {
    color: rgba(0, 212, 255, 0.5);
    flex-shrink: 0;
}

.pe-search-input-wrap input {
    flex: 1;
    background: none;
    border: none;
    outline: none;
    color: #E8EDF5;
    font-size: 1rem;
}

.pe-search-input-wrap input::placeholder {
    color: rgba(232, 237, 245, 0.4);
}

.pe-search-field input[type="date"],
.pe-search-field input[type="time"] {
    width: 100%;
    background: rgba(15, 20, 33, 0.9);
    border: 1px solid rgba(0, 212, 255, 0.15);
    border-radius: 12px;
    padding: 12px 16px;
    color: #E8EDF5;
    font-size: 1rem;
}

.pe-search-field input:focus {
    border-color: #00D4FF;
    outline: none;
}

.pe-search-submit {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 14px 28px;
    white-space: nowrap;
}

.pe-results {
    padding: 60px 24px 100px;
}

.pe-results__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
}

.pe-results__count {
    font-size: 1.1rem;
    color: rgba(232, 237, 245, 0.7);
}

.pe-results__count span:first-child {
    font-weight: 700;
    font-size: 1.3rem;
}

.pe-results__sort select {
    background: rgba(15, 20, 33, 0.9);
    border: 1px solid rgba(0, 212, 255, 0.2);
    border-radius: 10px;
    padding: 10px 16px;
    color: #E8EDF5;
    font-size: 0.9rem;
    cursor: pointer;
}

.pe-spaces-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
}

.pe-space-card {
    background: rgba(10, 14, 24, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.3s;
}

.pe-space-card:hover {
    border-color: rgba(0, 212, 255, 0.4);
    transform: translateY(-4px);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.pe-space-card__image {
    height: 180px;
    background: rgba(15, 20, 33, 0.9);
    position: relative;
    overflow: hidden;
}

.pe-space-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.pe-space-card__placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    background: linear-gradient(135deg, rgba(0, 212, 255, 0.1), rgba(123, 79, 255, 0.1));
}

.pe-space-card__badge {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 6px 12px;
    border-radius: 100px;
    font-size: 0.75rem;
    font-weight: 600;
}

.pe-space-card__badge.green {
    background: rgba(0, 229, 160, 0.2);
    color: #00E5A0;
    border: 1px solid rgba(0, 229, 160, 0.3);
}

.pe-space-card__badge.yellow {
    background: rgba(255, 217, 61, 0.2);
    color: #FFD93D;
    border: 1px solid rgba(255, 217, 61, 0.3);
}

.pe-space-card__content {
    padding: 20px;
}

.pe-space-card__content h3 {
    font-family: 'Syne', sans-serif;
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 8px;
    color: #E8EDF5;
}

.pe-space-card__address {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    color: rgba(232, 237, 245, 0.5);
    margin-bottom: 12px;
}

.pe-space-card__amenities {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 16px;
}

.pe-amenity-tag {
    font-size: 0.7rem;
    padding: 4px 10px;
    border-radius: 100px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: rgba(232, 237, 245, 0.5);
}

.pe-space-card__footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.pe-space-card__price {
    font-size: 0.9rem;
    color: rgba(232, 237, 245, 0.6);
}

.pe-space-card__price span {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 1.3rem;
}

.pe-btn--sm {
    padding: 8px 16px;
    font-size: 0.85rem;
}

.pe-no-results {
    text-align: center;
    padding: 80px 24px;
}

.pe-no-results__icon {
    font-size: 4rem;
    margin-bottom: 20px;
}

.pe-no-results h3 {
    font-family: 'Syne', sans-serif;
    font-size: 1.5rem;
    margin-bottom: 8px;
    color: #E8EDF5;
}

.pe-no-results p {
    color: rgba(232, 237, 245, 0.5);
}

.pe-pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 48px;
}

.pe-pagination__link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 12px;
    border-radius: 10px;
    background: rgba(15, 20, 33, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: rgba(232, 237, 245, 0.6);
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.2s;
}

.pe-pagination__link:hover:not(.disabled) {
    border-color: rgba(0, 212, 255, 0.4);
    color: #00D4FF;
}

.pe-pagination__link.active {
    background: linear-gradient(135deg, #00D4FF, #7B4FFF);
    color: #080B14;
    font-weight: 600;
    border: none;
}

.pe-pagination__link.disabled {
    opacity: 0.3;
    pointer-events: none;
}

@media (max-width: 768px) {
    .pe-search-row {
        flex-direction: column;
    }

    .pe-search-field,
    .pe-search-field--large {
        width: 100%;
        min-width: 100%;
    }

    .pe-search-submit {
        width: 100%;
        justify-content: center;
    }
}
</style>
