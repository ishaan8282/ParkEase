<template>
    <div v-if="links && links.length > 3" class="pe-pagination">
        <Link v-for="link in links" :key="link.label"
              :href="link.url ?? '#'"
              class="pe-page-btn"
              :class="{
                  'pe-page-btn--active': link.active,
                  'pe-page-btn--disabled': !link.url
              }"
              v-html="link.label" />
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    links: {
        type: Array,
        default: () => []
    }
})
</script>

<style scoped>
.pe-pagination {
    display: flex; justify-content: center; gap: 6px;
    margin-top: 24px; position: relative; z-index: 1;
}

.pe-page-btn {
    display: flex; align-items: center; justify-content: center;
    min-width: 38px; height: 38px; padding: 0 12px;
    border-radius: 10px;
    background: rgba(10,14,24,.9);
    border: 1px solid rgba(255,255,255,.07);
    color: rgba(232,237,245,.5);
    text-decoration: none; font-size: .85rem; transition: all .2s;
}

.pe-page-btn:hover:not(.pe-page-btn--disabled) {
    border-color: rgba(0,212,255,.35);
    color: #00D4FF;
    box-shadow: 0 0 12px rgba(0,212,255,.12);
}

.pe-page-btn--active {
    background: linear-gradient(135deg, #00D4FF, #7B4FFF);
    color: #060910; font-weight: 700; border: none;
    box-shadow: 0 0 20px rgba(0,212,255,.3);
}

.pe-page-btn--disabled { opacity: .3; pointer-events: none; }
</style>
