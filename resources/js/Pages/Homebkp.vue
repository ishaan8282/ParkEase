<template>
    <div class="pe-root">

        <!-- Navbar -->
        <nav class="pe-nav" :class="{ 'pe-nav--scrolled': scrolled }">
            <div class="pe-nav__inner">
                <a href="/" class="pe-nav__logo">
                    <svg class="pe-nav__logo-svg" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="logoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#00D4FF"/>
                                <stop offset="100%" stop-color="#7B4FFF"/>
                            </linearGradient>
                            <filter id="neonGlow">
                                <feGaussianBlur stdDeviation="2" result="coloredBlur"/>
                                <feMerge>
                                    <feMergeNode in="coloredBlur"/>
                                    <feMergeNode in="SourceGraphic"/>
                                </feMerge>
                            </filter>
                        </defs>
                        <rect width="40" height="40" rx="10" fill="url(#logoGrad)" filter="url(#neonGlow)"/>
                        <path d="M12 28V12h4l6 10 6-10h4v16h-4V18l-6 10-6-10v10h-4z" fill="#080B14" stroke="#00D4FF" stroke-width="0.5"/>
                    </svg>
                    <span>Park<em>Ease</em></span>
                </a>
                <div class="pe-nav__links" :class="{ 'pe-nav__links--open': menuOpen }">
                    <a href="#how-it-works" @click="menuOpen = false">How it works</a>
                    <a href="#features" @click="menuOpen = false">Features</a>
                    <a href="#cities" @click="menuOpen = false">Cities</a>
                    <div class="pe-nav__divider"></div>
                    <Link :href="route('login')" class="pe-btn pe-btn--ghost">Sign In</Link>
                    <Link :href="route('register')" class="pe-btn pe-btn--primary">Get Started</Link>
                </div>
                <button class="pe-nav__burger" @click="menuOpen = !menuOpen">
                    <span :class="{ open: menuOpen }"></span>
                    <span :class="{ open: menuOpen }"></span>
                    <span :class="{ open: menuOpen }"></span>
                </button>
            </div>
        </nav>

        <!-- Hero -->
        <section class="pe-hero">
            <div class="pe-hero__bg">
                <div class="pe-hero__orb pe-hero__orb--1"></div>
                <div class="pe-hero__orb pe-hero__orb--2"></div>
                <div class="pe-hero__orb pe-hero__orb--3"></div>
                <div class="pe-hero__grid"></div>
            </div>
            <div class="pe-hero__content">
                <div class="pe-hero__badge">
                    <span class="pe-badge-dot"></span>
                    Now live in Shimla · Manali · Mussoorie
                </div>
                <h1 class="pe-hero__title">
                    Park Smarter,<br>
                    <span class="pe-gradient-text">Explore Freely.</span>
                </h1>
                <p class="pe-hero__subtitle">
                    Real-time parking availability at India's most-loved hill stations.
                    Book your spot before you arrive — no more circling, no more stress.
                </p>
                <div class="pe-hero__search">
                    <div class="pe-search-box">
                        <div class="pe-search-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        </div>
                        <input type="text" placeholder="Where are you headed?" class="pe-search-input" />
                        <Link :href="route('register')" class="pe-search-btn">Find Parking</Link>
                    </div>
                </div>
                <div class="pe-hero__stats">
                    <div class="pe-stat">
                        <span class="pe-stat__num">2,400+</span>
                        <span class="pe-stat__label">Parking Slots</span>
                    </div>
                    <div class="pe-stat__sep"></div>
                    <div class="pe-stat">
                        <span class="pe-stat__num">180+</span>
                        <span class="pe-stat__label">Locations</span>
                    </div>
                    <div class="pe-stat__sep"></div>
                    <div class="pe-stat">
                        <span class="pe-stat__num">12k+</span>
                        <span class="pe-stat__label">Happy Drivers</span>
                    </div>
                </div>
            </div>
            <div class="pe-hero__visual">
                <div class="pe-card-float pe-card-float--1">
                    <div class="pe-card-float__icon">🏔️</div>
                    <div>
                        <div class="pe-card-float__title">Mall Road, Shimla</div>
                        <div class="pe-card-float__sub">14 spots available</div>
                    </div>
                    <div class="pe-card-float__badge">Open</div>
                </div>
                <div class="pe-map-mock">
                    <div class="pe-map-pin pe-map-pin--1">
                        <div class="pe-pin-pulse"></div>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                    </div>
                    <div class="pe-map-pin pe-map-pin--2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                    </div>
                    <div class="pe-map-pin pe-map-pin--3">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                    </div>
                    <div class="pe-map-roads">
                        <svg width="100%" height="100%" viewBox="0 0 300 300" fill="none">
                            <path d="M0 150 Q75 120 150 150 Q225 180 300 150" stroke="rgba(255,255,255,0.08)" stroke-width="12" fill="none"/>
                            <path d="M150 0 Q120 75 150 150 Q180 225 150 300" stroke="rgba(255,255,255,0.08)" stroke-width="12" fill="none"/>
                            <path d="M0 80 Q100 60 200 100 Q260 120 300 80" stroke="rgba(255,255,255,0.05)" stroke-width="8" fill="none"/>
                            <path d="M0 220 Q80 200 160 220 Q230 240 300 210" stroke="rgba(255,255,255,0.05)" stroke-width="8" fill="none"/>
                        </svg>
                    </div>
                </div>
                <div class="pe-card-float pe-card-float--2">
                    <div class="pe-card-float__icon">✅</div>
                    <div>
                        <div class="pe-card-float__title">Booking Confirmed!</div>
                        <div class="pe-card-float__sub">Slot B-4 · ₹40/hr</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="pe-section" id="how-it-works">
            <div class="pe-container">
                <div class="pe-section__label">Simple Process</div>
                <h2 class="pe-section__title">Parking in <span class="pe-gradient-text">3 steps</span></h2>
                <div class="pe-steps">
                    <div class="pe-step">
                        <div class="pe-step__num">01</div>
                        <div class="pe-step__icon">🔍</div>
                        <h3>Search</h3>
                        <p>Enter your destination and find available parking spots nearby in real-time.</p>
                    </div>
                    <div class="pe-step__arrow">→</div>
                    <div class="pe-step">
                        <div class="pe-step__num">02</div>
                        <div class="pe-step__icon">📅</div>
                        <h3>Book</h3>
                        <p>Choose your slot, pick your time, and pay securely via UPI, card or wallet.</p>
                    </div>
                    <div class="pe-step__arrow">→</div>
                    <div class="pe-step">
                        <div class="pe-step__num">03</div>
                        <div class="pe-step__icon">🚗</div>
                        <h3>Park</h3>
                        <p>Show your QR code on arrival. Your spot is reserved and waiting for you.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="pe-section pe-section--dark" id="features">
            <div class="pe-container">
                <div class="pe-section__label">Why ParkEase</div>
                <h2 class="pe-section__title">Everything you need,<br><span class="pe-gradient-text">nothing you don't.</span></h2>
                <div class="pe-features">
                    <div class="pe-feature pe-feature--large">
                        <div class="pe-feature__icon">⚡</div>
                        <h3>Real-time Availability</h3>
                        <p>Live slot updates so you always know what's available before you drive there. No surprises.</p>
                    </div>
                    <div class="pe-feature">
                        <div class="pe-feature__icon">🔒</div>
                        <h3>Secure Payments</h3>
                        <p>Pay via UPI, card, or wallet. Instant refunds on cancellations.</p>
                    </div>
                    <div class="pe-feature">
                        <div class="pe-feature__icon">📍</div>
                        <h3>GPS Navigation</h3>
                        <p>Get turn-by-turn directions straight to your booked parking spot.</p>
                    </div>
                    <div class="pe-feature">
                        <div class="pe-feature__icon">🏨</div>
                        <h3>Hotel Partnerships</h3>
                        <p>Exclusive parking deals with hotels near your destination.</p>
                    </div>
                    <div class="pe-feature">
                        <div class="pe-feature__icon">🌙</div>
                        <h3>24/7 Availability</h3>
                        <p>Book anytime, day or night. Our platform never sleeps.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cities -->
        <section class="pe-section" id="cities">
            <div class="pe-container">
                <div class="pe-section__label">Coverage</div>
                <h2 class="pe-section__title">Available in <span class="pe-gradient-text">hill stations</span> near you</h2>
                <div class="pe-cities">
                    <div class="pe-city" v-for="city in cities" :key="city.name">
                        <div class="pe-city__emoji">{{ city.emoji }}</div>
                        <div class="pe-city__info">
                            <h4>{{ city.name }}</h4>
                            <span>{{ city.spots }} spots</span>
                        </div>
                        <div class="pe-city__tag" :class="city.status === 'Live' ? 'live' : 'soon'">
                            {{ city.status }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="pe-cta">
            <div class="pe-cta__orb"></div>
            <div class="pe-container pe-cta__inner">
                <h2>Own a parking space?<br><span class="pe-gradient-text">Start earning today.</span></h2>
                <p>List your space in minutes. Hotels, private lots, residential — everyone can earn with ParkEase.</p>
                <div class="pe-cta__actions">
                    <Link :href="route('register')" class="pe-btn pe-btn--primary pe-btn--lg">List Your Space</Link>
                    <Link :href="route('register')" class="pe-btn pe-btn--ghost pe-btn--lg">Find Parking</Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="pe-footer">
            <div class="pe-container">
                <div class="pe-footer__top">
                    <div class="pe-footer__brand">
                        <div class="pe-nav__logo">
                            <svg class="pe-nav__logo-svg" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="logoGrad2" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#00D4FF"/>
                                        <stop offset="100%" stop-color="#7B4FFF"/>
                                    </linearGradient>
                                    <filter id="neonGlow2">
                                        <feGaussianBlur stdDeviation="2" result="coloredBlur"/>
                                        <feMerge>
                                            <feMergeNode in="coloredBlur"/>
                                            <feMergeNode in="SourceGraphic"/>
                                        </feMerge>
                                    </filter>
                                </defs>
                                <rect width="40" height="40" rx="10" fill="url(#logoGrad2)" filter="url(#neonGlow2)"/>
                                <path d="M12 28V12h4l6 10 6-10h4v16h-4V18l-6 10-6-10v10h-4z" fill="#080B14" stroke="#00D4FF" stroke-width="0.5"/>
                            </svg>
                            <span>Park<em>Ease</em></span>
                        </div>
                        <p>Making parking effortless across India's most beautiful destinations.</p>
                    </div>
                    <div class="pe-footer__links">
                        <div class="pe-footer__col">
                            <h5>Product</h5>
                            <a href="#how-it-works">How it works</a>
                            <a href="#features">Features</a>
                            <a href="#cities">Cities</a>
                        </div>
                        <div class="pe-footer__col">
                            <h5>For Owners</h5>
                            <Link :href="route('register')">List a space</Link>
                            <a href="#">Pricing</a>
                            <a href="#">Partner with us</a>
                        </div>
                        <div class="pe-footer__col">
                            <h5>Company</h5>
                            <a href="#">About</a>
                            <a href="#">Contact</a>
                            <a href="#">Privacy Policy</a>
                        </div>
                    </div>
                </div>
                <div class="pe-footer__bottom">
                    <span>© 2025 ParkEase. Built with ❤️ in Shimla, India.</span>
                    <div class="pe-footer__social">
                        <a href="#" aria-label="Twitter">𝕏</a>
                        <a href="#" aria-label="Instagram">📸</a>
                        <a href="#" aria-label="LinkedIn">in</a>
                    </div>
                </div>
            </div>
        </footer>

    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'

const scrolled = ref(false)
const menuOpen = ref(false)

function onScroll() {
    scrolled.value = window.scrollY > 50
}

onMounted(() => window.addEventListener('scroll', onScroll))
onUnmounted(() => window.removeEventListener('scroll', onScroll))

const cities = [
    { name: 'Shimla', emoji: '🏔️', spots: '840+', status: 'Live' },
    { name: 'Manali', emoji: '❄️', spots: '620+', status: 'Live' },
    { name: 'Mussoorie', emoji: '🌿', spots: '480+', status: 'Live' },
    { name: 'Nainital', emoji: '🏞️', spots: '390+', status: 'Coming Soon' },
    { name: 'Dharamshala', emoji: '☁️', spots: '310+', status: 'Coming Soon' },
    { name: 'Dalhousie', emoji: '🌲', spots: '200+', status: 'Coming Soon' },
]
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Outfit:wght@300;400;500;600&display=swap');

/* ── Root & Theme ───────────────────────────────── */
.pe-root {
    font-family: 'Outfit', sans-serif;
    background: #080B14;
    color: #E8EDF5;
    overflow-x: hidden;
}

:root {
    --accent: #00D4FF;
    --accent2: #7B4FFF;
    --green: #00E5A0;
    --neon-pink: #FF00FF;
    --neon-yellow: #FFFF00;
    --neon-orange: #FF6B00;
    --surface: #0F1421;
    --surface2: #161C2D;
    --border: rgba(255,255,255,0.07);
    --text-muted: rgba(232,237,245,0.5);
}

/* ── Navbar ─────────────────────────────────────── */
.pe-nav {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 100;
    padding: 20px 0;
    transition: all 0.3s ease;
}

.pe-nav--scrolled {
    background: rgba(8, 11, 20, 0.92);
    backdrop-filter: blur(20px);
    padding: 12px 0;
    border-bottom: 1px solid var(--border);
}

.pe-nav__inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.pe-nav__logo {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: #E8EDF5;
    font-family: 'Syne', sans-serif;
    font-size: 1.3rem;
    font-weight: 700;
}

.pe-nav__logo em {
    color: var(--accent);
    font-style: normal;
}

.pe-nav__logo-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.1rem;
    color: #080B14;
    box-shadow: 0 0 20px rgba(0, 212, 255, 0.5), 0 0 40px rgba(123, 79, 255, 0.3);
    animation: logoGlow 3s ease-in-out infinite;
}

@keyframes logoGlow {
    0%, 100% { box-shadow: 0 0 20px rgba(0, 212, 255, 0.5), 0 0 40px rgba(123, 79, 255, 0.3); }
    50% { box-shadow: 0 0 30px rgba(0, 212, 255, 0.7), 0 0 60px rgba(123, 79, 255, 0.5); }
}

.pe-nav__logo-svg {
    width: 40px;
    height: 40px;
    animation: logoFloat 4s ease-in-out infinite;
}

@keyframes logoFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-2px); }
}

.pe-nav__links {
    display: flex;
    align-items: center;
    gap: 32px;
}

.pe-nav__links a {
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.2s;
}

.pe-nav__links a:hover { color: #E8EDF5; }

.pe-nav__divider {
    width: 1px;
    height: 20px;
    background: var(--border);
}

.pe-nav__burger {
    display: none;
    flex-direction: column;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
}

.pe-nav__burger span {
    display: block;
    width: 24px;
    height: 2px;
    background: #E8EDF5;
    transition: all 0.3s;
    transform-origin: center;
}

.pe-nav__burger span.open:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.pe-nav__burger span.open:nth-child(2) { opacity: 0; }
.pe-nav__burger span.open:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

/* ── Buttons ────────────────────────────────────── */
.pe-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 10px;
    font-family: 'Outfit', sans-serif;
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.25s ease;
    cursor: pointer;
    border: none;
}

.pe-btn--primary {
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    color: #080B14;
    font-weight: 600;
    box-shadow: 0 0 25px rgba(0, 212, 255, 0.4), 0 0 50px rgba(123, 79, 255, 0.2);
}

.pe-btn--primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 40px rgba(0, 212, 255, 0.6), 0 0 70px rgba(123, 79, 255, 0.4);
    color: #080B14;
}

.pe-btn--ghost {
    background: transparent;
    color: #E8EDF5;
    border: 1px solid var(--border);
}

.pe-btn--ghost:hover {
    border-color: var(--accent);
    color: var(--accent);
}

.pe-btn--lg {
    padding: 14px 32px;
    font-size: 1rem;
    border-radius: 12px;
}

/* ── Hero ───────────────────────────────────────── */
.pe-hero {
    min-height: 100vh;
    display: flex;
    align-items: center;
    padding: 120px 24px 80px;
    max-width: 1200px;
    margin: 0 auto;
    gap: 60px;
    position: relative;
}

.pe-hero__bg {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
}

.pe-hero__orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.25;
}

.pe-hero__orb--1 {
    width: 600px; height: 600px;
    background: var(--accent2);
    top: -200px; left: -100px;
    animation: orbFloat 8s ease-in-out infinite;
    box-shadow: 0 0 150px rgba(123, 79, 255, 0.6);
}

.pe-hero__orb--2 {
    width: 400px; height: 400px;
    background: var(--accent);
    top: 200px; right: -50px;
    animation: orbFloat 10s ease-in-out infinite reverse;
    box-shadow: 0 0 100px rgba(0, 212, 255, 0.6);
}

.pe-hero__orb--3 {
    width: 300px; height: 300px;
    background: var(--green);
    bottom: 100px; left: 40%;
    animation: orbFloat 12s ease-in-out infinite;
    box-shadow: 0 0 80px rgba(0, 229, 160, 0.6);
}

@keyframes orbFloat {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(30px, -20px) scale(1.05); }
    66% { transform: translate(-20px, 30px) scale(0.95); }
}

.pe-hero__grid {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
    background-size: 60px 60px;
}

.pe-hero__content {
    flex: 1;
    position: relative;
    z-index: 1;
    animation: fadeUp 0.8s ease both;
}

.pe-hero__visual {
    flex: 1;
    position: relative;
    z-index: 1;
    animation: fadeUp 0.8s ease 0.2s both;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.pe-hero__badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(0, 212, 255, 0.08);
    border: 1px solid rgba(0, 212, 255, 0.2);
    padding: 6px 14px;
    border-radius: 100px;
    font-size: 0.8rem;
    color: var(--accent);
    margin-bottom: 24px;
}

.pe-badge-dot {
    width: 6px; height: 6px;
    background: var(--green);
    border-radius: 50%;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.3); }
}

.pe-hero__title {
    font-family: 'Syne', sans-serif;
    font-size: clamp(2.8rem, 5vw, 4.5rem);
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 20px;
    letter-spacing: -1px;
}

.pe-gradient-text {
    background: linear-gradient(135deg, var(--accent), var(--accent2), var(--neon-pink));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: gradientShift 5s ease infinite;
    background-size: 200% 200%;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.pe-hero__subtitle {
    font-size: 1.1rem;
    color: var(--text-muted);
    line-height: 1.7;
    max-width: 500px;
    margin-bottom: 36px;
}

.pe-hero__search {
    margin-bottom: 40px;
}

.pe-search-box {
    display: flex;
    align-items: center;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 6px 6px 6px 16px;
    max-width: 520px;
    transition: border-color 0.2s;
}

.pe-search-box:focus-within {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.15), 0 0 30px rgba(0, 212, 255, 0.2);
}

.pe-search-icon { color: var(--text-muted); display: flex; }

.pe-search-input {
    flex: 1;
    background: none;
    border: none;
    outline: none;
    color: #E8EDF5;
    font-family: 'Outfit', sans-serif;
    font-size: 0.95rem;
    padding: 8px 12px;
}

.pe-search-input::placeholder { color: var(--text-muted); }

.pe-search-btn {
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    color: #080B14;
    font-family: 'Outfit', sans-serif;
    font-weight: 600;
    font-size: 0.9rem;
    border: none;
    padding: 12px 22px;
    border-radius: 10px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
    white-space: nowrap;
    box-shadow: 0 0 20px rgba(0, 212, 255, 0.35);
}

.pe-search-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 35px rgba(0, 212, 255, 0.5), 0 0 50px rgba(123, 79, 255, 0.3);
    color: #080B14;
}

.pe-hero__stats {
    display: flex;
    align-items: center;
    gap: 24px;
}

.pe-stat__num {
    display: block;
    font-family: 'Syne', sans-serif;
    font-size: 1.5rem;
    font-weight: 700;
    color: #E8EDF5;
}

.pe-stat__label {
    display: block;
    font-size: 0.78rem;
    color: var(--text-muted);
}

.pe-stat__sep {
    width: 1px;
    height: 36px;
    background: var(--border);
}

/* ── Map Visual ─────────────────────────────────── */
.pe-map-mock {
    width: 100%;
    aspect-ratio: 1;
    max-width: 420px;
    margin: 0 auto;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 24px;
    position: relative;
    overflow: hidden;
    background-image: radial-gradient(circle at 30% 40%, rgba(123, 79, 255, 0.08) 0%, transparent 60%),
                      radial-gradient(circle at 70% 70%, rgba(0, 212, 255, 0.06) 0%, transparent 50%);
}

.pe-map-roads { position: absolute; inset: 0; }

.pe-map-pin {
    position: absolute;
    color: var(--accent);
    filter: drop-shadow(0 0 12px var(--accent)) drop-shadow(0 0 25px var(--accent));
    animation: pinBounce 3s ease-in-out infinite;
}

.pe-map-pin--1 { top: 30%; left: 40%; }
.pe-map-pin--2 { top: 55%; left: 25%; color: var(--accent2); filter: drop-shadow(0 0 12px var(--accent2)) drop-shadow(0 0 25px var(--accent2)); animation-delay: 1s; }
.pe-map-pin--3 { top: 40%; left: 62%; color: var(--green); filter: drop-shadow(0 0 12px var(--green)) drop-shadow(0 0 25px var(--green)); animation-delay: 2s; }

@keyframes pinBounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-6px); }
}

.pe-pin-pulse {
    position: absolute;
    width: 30px; height: 30px;
    border-radius: 50%;
    background: var(--accent);
    opacity: 0.2;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    animation: pinPulse 2s ease-out infinite;
}

@keyframes pinPulse {
    0% { transform: translate(-50%, -50%) scale(0.5); opacity: 0.4; }
    100% { transform: translate(-50%, -50%) scale(2.5); opacity: 0; }
}

.pe-card-float {
    position: absolute;
    background: rgba(22, 28, 45, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    z-index: 2;
    font-size: 0.82rem;
    min-width: 220px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    animation: floatCard 4s ease-in-out infinite;
}

.pe-card-float--1 { top: 10%; right: -20px; animation-delay: 0s; }
.pe-card-float--2 { bottom: 15%; left: -20px; animation-delay: 2s; }

@keyframes floatCard {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-8px); }
}

.pe-card-float__icon { font-size: 1.4rem; }
.pe-card-float__title { font-weight: 600; color: #E8EDF5; }
.pe-card-float__sub { color: var(--text-muted); font-size: 0.75rem; }
.pe-card-float__badge {
    margin-left: auto;
    background: rgba(0, 229, 160, 0.15);
    color: var(--green);
    padding: 3px 10px;
    border-radius: 100px;
    font-size: 0.72rem;
    font-weight: 600;
}

/* ── Sections ───────────────────────────────────── */
.pe-section {
    padding: 100px 24px;
    position: relative;
}

.pe-section--dark {
    background: var(--surface);
}

.pe-container {
    max-width: 1200px;
    margin: 0 auto;
}

.pe-section__label {
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--accent);
    margin-bottom: 12px;
}

.pe-section__title {
    font-family: 'Syne', sans-serif;
    font-size: clamp(2rem, 3.5vw, 3rem);
    font-weight: 800;
    line-height: 1.15;
    margin-bottom: 60px;
    letter-spacing: -0.5px;
}

/* ── Steps ──────────────────────────────────────── */
.pe-steps {
    display: flex;
    align-items: flex-start;
    gap: 16px;
}

.pe-step {
    flex: 1;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 32px;
    position: relative;
    transition: border-color 0.3s, transform 0.3s;
}

.pe-step:hover {
    border-color: var(--accent);
    transform: translateY(-4px);
    box-shadow: 0 15px 50px rgba(0, 212, 255, 0.15), 0 0 25px rgba(0, 212, 255, 0.1);
}

.pe-step__num {
    font-family: 'Syne', sans-serif;
    font-size: 2.5rem;
    font-weight: 800;
    color: rgba(255,255,255,0.04);
    position: absolute;
    top: 16px; right: 20px;
    line-height: 1;
}

.pe-step__icon { font-size: 2rem; margin-bottom: 16px; }

.pe-step h3 {
    font-family: 'Syne', sans-serif;
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.pe-step p { color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; }

.pe-step__arrow {
    font-size: 1.5rem;
    color: var(--border);
    margin-top: 60px;
    flex-shrink: 0;
}

/* ── Features ───────────────────────────────────── */
.pe-features {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    grid-template-rows: auto auto;
    gap: 16px;
}

.pe-feature {
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 28px;
    transition: border-color 0.3s, transform 0.3s;
}

.pe-feature:hover {
    border-color: rgba(0,212,255,0.6);
    transform: translateY(-3px);
    box-shadow: 0 15px 50px rgba(0, 212, 255, 0.2), 0 0 30px rgba(0, 212, 255, 0.1);
}

.pe-feature--large {
    grid-column: span 2;
    background: linear-gradient(135deg, rgba(0,212,255,0.12), rgba(123,79,255,0.12));
    border-color: rgba(0,212,255,0.35);
    box-shadow: 0 0 40px rgba(0, 212, 255, 0.1);
}

.pe-feature__icon { font-size: 2rem; margin-bottom: 16px; }

.pe-feature h3 {
    font-family: 'Syne', sans-serif;
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 8px;
}

.pe-feature p { color: var(--text-muted); font-size: 0.88rem; line-height: 1.6; }

/* ── Cities ─────────────────────────────────────── */
.pe-cities {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.pe-city {
    display: flex;
    align-items: center;
    gap: 14px;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 20px;
    transition: all 0.3s;
}

.pe-city:hover {
    border-color: var(--accent);
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(0, 212, 255, 0.2), 0 0 20px rgba(0, 212, 255, 0.1);
}

.pe-city__emoji { font-size: 1.8rem; }

.pe-city__info h4 {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    margin-bottom: 2px;
    font-size: 1rem;
}

.pe-city__info span { font-size: 0.78rem; color: var(--text-muted); }

.pe-city__tag {
    margin-left: auto;
    padding: 4px 12px;
    border-radius: 100px;
    font-size: 0.72rem;
    font-weight: 600;
}

.pe-city__tag.live {
    background: rgba(0,229,160,0.12);
    color: var(--green);
}

.pe-city__tag.soon {
    background: rgba(255,255,255,0.06);
    color: var(--text-muted);
}

/* ── CTA ────────────────────────────────────────── */
.pe-cta {
    padding: 100px 24px;
    position: relative;
    overflow: hidden;
    text-align: center;
}

.pe-cta__orb {
    position: absolute;
    width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(123,79,255,0.15) 0%, transparent 70%);
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    pointer-events: none;
}

.pe-cta__inner {
    position: relative;
    z-index: 1;
}

.pe-cta h2 {
    font-family: 'Syne', sans-serif;
    font-size: clamp(2rem, 3.5vw, 3rem);
    font-weight: 800;
    margin-bottom: 16px;
    letter-spacing: -0.5px;
}

.pe-cta p {
    color: var(--text-muted);
    font-size: 1rem;
    margin-bottom: 40px;
    max-width: 480px;
    margin-left: auto;
    margin-right: auto;
}

.pe-cta__actions { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }

/* ── Footer ─────────────────────────────────────── */
.pe-footer {
    background: var(--surface);
    border-top: 1px solid var(--border);
    padding: 60px 24px 30px;
}

.pe-footer__top {
    display: flex;
    gap: 80px;
    margin-bottom: 48px;
}

.pe-footer__brand {
    flex: 1;
}

.pe-footer__brand p {
    color: var(--text-muted);
    font-size: 0.88rem;
    line-height: 1.6;
    margin-top: 16px;
    max-width: 260px;
}

.pe-footer__links {
    display: flex;
    gap: 60px;
}

.pe-footer__col h5 {
    font-family: 'Syne', sans-serif;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #E8EDF5;
    margin-bottom: 16px;
}

.pe-footer__col a {
    display: block;
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.88rem;
    margin-bottom: 10px;
    transition: color 0.2s;
}

.pe-footer__col a:hover { color: var(--accent); }

.pe-footer__bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 24px;
    border-top: 1px solid var(--border);
    font-size: 0.82rem;
    color: var(--text-muted);
}

.pe-footer__social {
    display: flex;
    gap: 20px;
}

.pe-footer__social a {
    color: var(--text-muted);
    text-decoration: none;
    transition: color 0.2s;
}

.pe-footer__social a:hover { color: var(--accent); }

/* ── Responsive ─────────────────────────────────── */
@media (max-width: 900px) {
    .pe-hero {
        flex-direction: column;
        padding-top: 100px;
        text-align: center;
    }

    .pe-hero__subtitle { margin: 0 auto 36px; }
    .pe-hero__stats { justify-content: center; }
    .pe-search-box { max-width: 100%; }

    .pe-hero__visual { width: 100%; max-width: 380px; margin: 0 auto; }

    .pe-steps { flex-direction: column; }
    .pe-step__arrow { display: none; }

    .pe-features { grid-template-columns: 1fr; }
    .pe-feature--large { grid-column: span 1; }

    .pe-cities { grid-template-columns: 1fr 1fr; }

    .pe-footer__top { flex-direction: column; gap: 40px; }
    .pe-footer__links { flex-wrap: wrap; gap: 32px; }

    .pe-nav__links {
        display: none;
        position: fixed;
        inset: 0;
        top: 64px;
        background: rgba(8, 11, 20, 0.98);
        backdrop-filter: blur(20px);
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 24px;
        font-size: 1.2rem;
    }

    .pe-nav__links--open { display: flex; }
    .pe-nav__burger { display: flex; }
    .pe-nav__divider { display: none; }
}

@media (max-width: 600px) {
    .pe-cities { grid-template-columns: 1fr; }
    .pe-footer__bottom { flex-direction: column; gap: 16px; text-align: center; }
}
</style>
