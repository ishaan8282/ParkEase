<template>
    <div v-if="success || error" class="pe-flash-messages">
        <div v-if="success" class="pe-flash pe-flash--success">
            <span>✓</span> {{ success }}
        </div>
        <div v-if="error" class="pe-flash pe-flash--error">
            <span>✕</span> {{ error }}
        </div>
    </div>
</template>

<script setup>
defineProps({
    success: {
        type: String,
        default: null
    },
    error: {
        type: String,
        default: null
    }
})
</script>

<style scoped>
.pe-flash-messages {
    position: relative;
    z-index: 1;
    margin-bottom: 20px;
}

.pe-flash {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 16px; border-radius: 12px;
    font-size: .9rem;
}

.pe-flash--success {
    background: rgba(0,229,160,.08);
    border: 1px solid rgba(0,229,160,.25);
    color: #00E5A0;
}

.pe-flash--error {
    background: rgba(255,107,107,.08);
    border: 1px solid rgba(255,107,107,.25);
    color: #FF6B6B;
}
</style>
