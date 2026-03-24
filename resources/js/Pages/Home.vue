<template>
    <div class="pe-root">

        <!-- Navbar -->
        <nav
            class="pe-nav"
            :class="{ 'pe-nav--scrolled': scrolled }"
        >
            <div class="pe-nav__inner">
                <Link
                    href="/"
                    class="pe-nav__logo"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 200 60"
                        fill="none"
                        class="pe-logo-svg"
                    >
                        <defs>
                            <linearGradient
                                id="iG"
                                x1="0%"
                                y1="0%"
                                x2="100%"
                                y2="100%"
                            >
                                <stop
                                    offset="0%"
                                    style="stop-color:#00D4FF"
                                />
                                <stop
                                    offset="100%"
                                    style="stop-color:#7B4FFF"
                                />
                            </linearGradient>
                            <linearGradient
                                id="tG"
                                x1="0%"
                                y1="0%"
                                x2="100%"
                                y2="100%"
                            >
                                <stop
                                    offset="0%"
                                    style="stop-color:#00D4FF"
                                />
                                <stop
                                    offset="100%"
                                    style="stop-color:#7B4FFF"
                                />
                            </linearGradient>
                            <filter id="ng">
                                <feGaussianBlur
                                    stdDeviation="2"
                                    result="b"
                                />
                                <feMerge>
                                    <feMergeNode in="b" />
                                    <feMergeNode in="SourceGraphic" />
                                </feMerge>
                            </filter>
                        </defs>
                        <rect
                            x="4"
                            y="4"
                            width="42"
                            height="42"
                            rx="12"
                            fill="url(#iG)"
                            filter="url(#ng)"
                        />
                        <rect
                            x="4"
                            y="4"
                            width="42"
                            height="42"
                            rx="12"
                            fill="none"
                            stroke="rgba(0,212,255,0.4)"
                            stroke-width="1"
                        />
                        <text
                            x="14"
                            y="35"
                            font-family="Arial Black,sans-serif"
                            font-weight="900"
                            font-size="26"
                            fill="#080B14"
                        >P</text>
                        <text
                            x="56"
                            y="30"
                            font-family="Arial Black,sans-serif"
                            font-weight="900"
                            font-size="20"
                            fill="white"
                            letter-spacing="-0.5"
                        >Park</text>
                        <text
                            x="103"
                            y="30"
                            font-family="Arial Black,sans-serif"
                            font-weight="900"
                            font-size="20"
                            fill="url(#tG)"
                            filter="url(#ng)"
                            letter-spacing="-0.5"
                        >Ease</text>
                        <text
                            x="57"
                            y="44"
                            font-family="Arial,sans-serif"
                            font-size="8"
                            fill="rgba(232,237,245,0.4)"
                            letter-spacing="2.5"
                        >SMART PARKING</text>
                    </svg>
                </Link>

                <div
                    class="pe-nav__links"
                    :class="{ 'pe-nav__links--open': menuOpen }"
                >
                    <a
                        href="#how-it-works"
                        @click="menuOpen = false"
                    >How it works</a>
                    <a
                        href="#features"
                        @click="menuOpen = false"
                    >Features</a>
                    <a
                        href="#cities"
                        @click="menuOpen = false"
                    >Cities</a>
                    <div class="pe-nav__sep"></div>
                    <Link
                        :href="route('login')"
                        class="pe-btn pe-btn--ghost"
                    >Sign In</Link>
                    <Link
                        :href="route('register')"
                        class="pe-btn pe-btn--neon"
                    >Get Started</Link>
                </div>
                <button
                    class="pe-burger"
                    @click="menuOpen = !menuOpen"
                >
                    <span :class="{ open: menuOpen }"></span>
                    <span :class="{ open: menuOpen }"></span>
                    <span :class="{ open: menuOpen }"></span>
                </button>
            </div>
        </nav>

        <!-- Hero -->
        <section class="pe-hero">
            <div class="pe-bg">
                <div class="pe-orb pe-orb--1"></div>
                <div class="pe-orb pe-orb--2"></div>
                <div class="pe-orb pe-orb--3"></div>
                <div class="pe-grid"></div>
                <div class="pe-scan"></div>
            </div>

            <div class="pe-hero__left">
                <div class="pe-badge">
                    <span class="pe-dot pe-dot--green"></span>
                    Live in Shimla · Manali · Mussoorie
                </div>
                <h1 class="pe-h1">
                    Park Smarter,<br>
                    <span class="neon-cyan">Explore</span> <span class="neon-purple">Freely.</span>
                </h1>
                <p class="pe-sub">
                    Real-time parking availability at India's most-loved hill stations.
                    Book your spot before you arrive — no circling, no stress.
                </p>
                <div class="pe-search-wrap">
                    <div class="pe-search">
                        <svg
                            width="18"
                            height="18"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            style="color:rgba(0,212,255,0.5);flex-shrink:0"
                        >
                            <circle
                                cx="11"
                                cy="11"
                                r="8"
                            />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                        <input
                            type="text"
                            placeholder="Where are you headed?"
                        />
                        <Link
                            :href="route('register')"
                            class="pe-sbtn"
                        >Find Parking →</Link>
                    </div>
                    <div class="pe-tags">
                        <span
                            v-for="t in tags"
                            :key="t"
                            class="pe-tag"
                        >{{ t }}</span>
                    </div>
                </div>
                <div class="pe-stats">
                    <div
                        v-for="s in stats"
                        :key="s.label"
                        class="pe-stat"
                    >
                        <span
                            class="pe-stat__n"
                            :style="{ color: s.color, textShadow: `0 0 15px ${s.color}` }"
                        >{{ s.n }}</span>
                        <span class="pe-stat__l">{{ s.label }}</span>
                    </div>
                </div>
            </div>

            <div class="pe-hero__right">
                <div class="pe-fc pe-fc--top">
                    <span
                        class="pe-dot pe-dot--green"
                        style="flex-shrink:0"
                    ></span>
                    <div>
                        <div class="pe-fc__title">Mall Road, Shimla</div>
                        <div class="pe-fc__sub">14 spots available</div>
                    </div>
                    <div class="pe-fc__pill">OPEN</div>
                </div>

                <div class="pe-mapbox">
                    <svg
                        class="pe-maproads"
                        viewBox="0 0 400 400"
                        fill="none"
                    >
                        <path
                            d="M0 200 Q100 160 200 200 Q300 240 400 200"
                            stroke="rgba(0,212,255,0.06)"
                            stroke-width="18"
                        />
                        <path
                            d="M200 0 Q160 100 200 200 Q240 300 200 400"
                            stroke="rgba(0,212,255,0.06)"
                            stroke-width="18"
                        />
                        <path
                            d="M0 100 Q120 80 240 120 Q320 140 400 100"
                            stroke="rgba(123,79,255,0.05)"
                            stroke-width="10"
                        />
                        <path
                            d="M0 300 Q100 280 200 300 Q300 320 400 290"
                            stroke="rgba(123,79,255,0.05)"
                            stroke-width="10"
                        />
                    </svg>
                    <div
                        class="pe-pin pe-pin--cyan"
                        style="top:28%;left:42%"
                    >
                        <div class="pe-pin__ring"></div>
                        <div class="pe-pin__dot"></div>
                    </div>
                    <div
                        class="pe-pin pe-pin--purple"
                        style="top:52%;left:24%"
                    >
                        <div class="pe-pin__ring"></div>
                        <div class="pe-pin__dot"></div>
                    </div>
                    <div
                        class="pe-pin pe-pin--green"
                        style="top:38%;left:65%"
                    >
                        <div class="pe-pin__ring"></div>
                        <div class="pe-pin__dot"></div>
                    </div>
                    <div
                        class="pe-pin pe-pin--cyan"
                        style="top:65%;left:55%"
                    >
                        <div class="pe-pin__ring"></div>
                        <div class="pe-pin__dot"></div>
                    </div>
                    <div class="pe-avail">
                        <div
                            class="pe-avail__row"
                            v-for="a in avails"
                            :key="a.name"
                        >
                            <span>{{ a.name }}</span>
                            <div class="pe-avail__bar">
                                <div
                                    class="pe-avail__fill"
                                    :style="{ width: a.pct + '%', background: a.color, boxShadow: `0 0 6px ${a.color}` }"
                                ></div>
                            </div>
                            <span :style="{ color: a.color }">{{ a.slots }}</span>
                        </div>
                    </div>
                </div>

                <div class="pe-fc pe-fc--bot">
                    <div class="pe-fc__check">✓</div>
                    <div>
                        <div class="pe-fc__title">Booking Confirmed!</div>
                        <div class="pe-fc__sub">Slot B-4 · ₹40/hr · 2 hrs</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section
            class="pe-section"
            id="how-it-works"
        >
            <div class="pe-container">
                <div class="pe-eyebrow">Simple Process</div>
                <h2 class="pe-h2">Parking in <span class="neon-cyan">3 steps</span></h2>
                <div class="pe-steps">
                    <div
                        class="pe-step"
                        v-for="(s, i) in steps"
                        :key="i"
                        :style="{ '--c': s.color }"
                    >
                        <!-- <div class="pe-step__bg-num">0{{ i + 1 }}</div> -->
                        <div class="pe-step__icon">{{ s.icon }}</div>
                        <h3>{{ s.title }}</h3>
                        <p>{{ s.desc }}</p>
                        <div class="pe-step__shine"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section
            class="pe-section pe-section--alt"
            id="features"
        >
            <div class="pe-container">
                <div class="pe-eyebrow">Why ParkEase</div>
                <h2 class="pe-h2">Everything you need,<br><span class="neon-purple">nothing you don't.</span></h2>
                <div class="pe-feats">
                    <div
                        v-for="(f, i) in features"
                        :key="i"
                        class="pe-feat"
                        :class="{ 'pe-feat--wide': f.wide }"
                        :style="{ '--c': f.color }"
                    >
                        <div class="pe-feat__icon">{{ f.icon }}</div>
                        <h3>{{ f.title }}</h3>
                        <p>{{ f.desc }}</p>
                        <div class="pe-feat__shine"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cities -->
        <section
            class="pe-section"
            id="cities"
        >
            <div class="pe-container">
                <div class="pe-eyebrow">Coverage</div>
                <h2 class="pe-h2">Hill stations <span class="neon-green">covered</span></h2>
                <div class="pe-cities">
                    <div
                        v-for="c in cities"
                        :key="c.name"
                        class="pe-city"
                        :class="c.live ? 'pe-city--live' : 'pe-city--soon'"
                    >
                        <span class="pe-city__emoji">{{ c.emoji }}</span>
                        <div>
                            <h4>{{ c.name }}</h4>
                            <small>{{ c.spots }} spots</small>
                        </div>
                        <div class="pe-city__status">
                            <span
                                v-if="c.live"
                                class="pe-dot pe-dot--green"
                            ></span>
                            {{ c.live ? 'Live' : 'Soon' }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="pe-cta">
            <div class="pe-cta__orbs">
                <div
                    class="pe-orb"
                    style="width:500px;height:500px;background:rgba(0,212,255,0.07);top:50%;left:50%;transform:translate(-50%,-50%)"
                ></div>
                <div
                    class="pe-orb"
                    style="width:300px;height:300px;background:rgba(123,79,255,0.09);top:30%;left:55%"
                ></div>
            </div>
            <div class="pe-container pe-cta__inner">
                <div class="pe-cta__badge">For Space Owners</div>
                <h2 class="pe-h2">Own a parking space?<br><span class="neon-green">Start earning today.</span></h2>
                <p>List in minutes. Hotels, private lots, residential — everyone earns.</p>
                <div class="pe-cta__btns">
                    <Link
                        :href="route('register')"
                        class="pe-btn pe-btn--neon pe-btn--lg"
                    >List Your Space</Link>
                    <Link
                        :href="route('register')"
                        class="pe-btn pe-btn--ghost pe-btn--lg"
                    >Find Parking</Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="pe-footer">
            <div class="pe-container">
                <div class="pe-footer__top">
                    <div class="pe-footer__brand">
                        <Link href="/">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 200 60"
                                fill="none"
                                class="pe-logo-svg"
                            >
                                <defs>
                                    <linearGradient
                                        id="iG2"
                                        x1="0%"
                                        y1="0%"
                                        x2="100%"
                                        y2="100%"
                                    >
                                        <stop
                                            offset="0%"
                                            style="stop-color:#00D4FF"
                                        />
                                        <stop
                                            offset="100%"
                                            style="stop-color:#7B4FFF"
                                        />
                                    </linearGradient>
                                    <filter id="ng2">
                                        <feGaussianBlur
                                            stdDeviation="2"
                                            result="b"
                                        />
                                        <feMerge>
                                            <feMergeNode in="b" />
                                            <feMergeNode in="SourceGraphic" />
                                        </feMerge>
                                    </filter>
                                </defs>
                                <rect
                                    x="4"
                                    y="4"
                                    width="42"
                                    height="42"
                                    rx="12"
                                    fill="url(#iG2)"
                                    filter="url(#ng2)"
                                />
                                <rect
                                    x="4"
                                    y="4"
                                    width="42"
                                    height="42"
                                    rx="12"
                                    fill="none"
                                    stroke="rgba(0,212,255,0.4)"
                                    stroke-width="1"
                                />
                                <text
                                    x="14"
                                    y="35"
                                    font-family="Arial Black,sans-serif"
                                    font-weight="900"
                                    font-size="26"
                                    fill="#080B14"
                                >P</text>
                                <text
                                    x="56"
                                    y="30"
                                    font-family="Arial Black,sans-serif"
                                    font-weight="900"
                                    font-size="20"
                                    fill="white"
                                    letter-spacing="-0.5"
                                >Park</text>
                                <text
                                    x="103"
                                    y="30"
                                    font-family="Arial Black,sans-serif"
                                    font-weight="900"
                                    font-size="20"
                                    fill="url(#iG2)"
                                    filter="url(#ng2)"
                                    letter-spacing="-0.5"
                                >Ease</text>
                                <text
                                    x="57"
                                    y="44"
                                    font-family="Arial,sans-serif"
                                    font-size="8"
                                    fill="rgba(232,237,245,0.4)"
                                    letter-spacing="2.5"
                                >SMART PARKING</text>
                            </svg>
                        </Link>
                        <p>Making parking effortless across India's most beautiful hill stations.</p>
                        <div class="pe-socials">
                            <a
                                href="#"
                                class="pe-social"
                            >𝕏</a>
                            <a
                                href="#"
                                class="pe-social"
                            >in</a>
                            <a
                                href="#"
                                class="pe-social"
                            >ig</a>
                        </div>
                    </div>
                    <div class="pe-footer__nav">
                        <div
                            class="pe-fcol"
                            v-for="col in footerCols"
                            :key="col.title"
                        >
                            <h5>{{ col.title }}</h5>
                            <a
                                v-for="l in col.links"
                                :key="l.label"
                                :href="l.href"
                            >{{ l.label }}</a>
                        </div>
                    </div>
                </div>
                <div class="pe-footer__bottom">
                    <span>© 2025 ParkEase · Built with ❤️ in Shimla, India</span>
                    <span>Parking, <span class="neon-cyan">reimagined.</span></span>
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
    onMounted(() => window.addEventListener('scroll', () => { scrolled.value = window.scrollY > 50 }))
    onUnmounted(() => window.removeEventListener('scroll', () => { }))

    const tags = ['Mall Road Shimla', 'Manali Bus Stand', 'Mussoorie Lake', 'Jakhu Temple']
    const stats = [
        { n: '2,400+', label: 'Parking Slots', color: '#00D4FF' },
        { n: '180+', label: 'Locations', color: '#B57BFF' },
        { n: '12k+', label: 'Happy Drivers', color: '#00E5A0' },
    ]
    const avails = [
        { name: 'Mall Road', pct: 75, slots: '14 free', color: '#00D4FF' },
        { name: 'Jakhu Temple', pct: 40, slots: '6 free', color: '#B57BFF' },
        { name: 'The Ridge', pct: 90, slots: '21 free', color: '#00E5A0' },
    ]
    const steps = [
        { icon: '🔍', title: 'Search', color: '#00D4FF', desc: 'Enter your destination and find available spots in real-time.' },
        { icon: '📅', title: 'Book', color: '#B57BFF', desc: 'Pick your slot, set your time, pay via UPI, card or wallet.' },
        { icon: '🚗', title: 'Park', color: '#00E5A0', desc: 'Show your QR code on arrival. Your reserved spot is waiting.' },
    ]
    const features = [
        { icon: '⚡', title: 'Real-time Availability', color: '#00D4FF', wide: true, desc: 'Live slot updates so you always know what\'s open before you drive. No surprises, ever.' },
        { icon: '🔒', title: 'Secure Payments', color: '#B57BFF', wide: false, desc: 'UPI, card, wallet. Instant refunds on cancellations.' },
        { icon: '📍', title: 'GPS Navigation', color: '#00E5A0', wide: false, desc: 'Turn-by-turn directions to your exact booked spot.' },
        { icon: '🏨', title: 'Hotel Deals', color: '#FF6B6B', wide: false, desc: 'Exclusive rates at hotel parking near your destination.' },
        { icon: '🌙', title: '24/7 Booking', color: '#FFD93D', wide: false, desc: 'Platform never sleeps. Book any time, day or night.' },
    ]
    const cities = [
        { name: 'Shimla', emoji: '🏔️', spots: '840+', live: true },
        { name: 'Manali', emoji: '❄️', spots: '620+', live: true },
        { name: 'Mussoorie', emoji: '🌿', spots: '480+', live: true },
        { name: 'Nainital', emoji: '🏞️', spots: '390+', live: false },
        { name: 'Dharamshala', emoji: '☁️', spots: '310+', live: false },
        { name: 'Dalhousie', emoji: '🌲', spots: '200+', live: false },
    ]
    const footerCols = [
        { title: 'Product', links: [{ label: 'How it works', href: '#how-it-works' }, { label: 'Features', href: '#features' }, { label: 'Cities', href: '#cities' }] },
        { title: 'For Owners', links: [{ label: 'List a space', href: '/register' }, { label: 'Pricing', href: '#' }, { label: 'Partner with us', href: '#' }] },
        { title: 'Company', links: [{ label: 'About', href: '#' }, { label: 'Contact', href: '#' }, { label: 'Privacy', href: '#' }] },
    ]
</script>
