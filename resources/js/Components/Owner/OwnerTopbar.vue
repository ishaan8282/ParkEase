<template>
    <header class="pe-topbar">
        <button v-if="showBurger" class="pe-topbar__burger" @click="$emit('toggleSidebar')">
            <span></span><span></span><span></span>
        </button>
        <div class="pe-topbar__title">{{ title }}</div>
        <div v-if="showLiveIndicator" class="pe-topbar__right">
            <div class="pe-topbar__dot"></div>
            <span class="pe-topbar__live">Live</span>
        </div>
        <slot name="actions"></slot>
    </header>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true
    },
    showBurger: {
        type: Boolean,
        default: true
    },
    showLiveIndicator: {
        type: Boolean,
        default: false
    }
})

defineEmits(['toggleSidebar'])
</script>

<style scoped>
.pe-topbar {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 18px 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    margin-bottom: 28px;
    position: relative;
    z-index: 1;
}

.pe-topbar__burger {
    display: none;
    flex-direction: column;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
}

.pe-topbar__burger span {
    display: block; width: 22px; height: 2px;
    background: rgba(232,237,245,.6); border-radius: 2px;
}

.pe-topbar__title {
    font-family: 'Syne', sans-serif;
    font-size: .85rem;
    font-weight: 700;
    color: rgba(232,237,245,.4);
    letter-spacing: .5px;
}

.pe-topbar__right {
    margin-left: auto;
    display: flex; align-items: center; gap: 8px;
    font-size: .78rem; color: rgba(232,237,245,.4);
}

.pe-topbar__dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: #00E5A0;
    box-shadow: 0 0 6px #00E5A0, 0 0 14px #00E5A0;
    animation: blip 2s ease-in-out infinite;
}

@keyframes blip {
    0%,100%{opacity:1;transform:scale(1)}
    50%{opacity:.5;transform:scale(1.4)}
}

@media (max-width: 1024px) {
    .pe-topbar__burger { display: flex; }
}
</style>
