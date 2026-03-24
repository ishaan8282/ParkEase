<template>
    <span class="badge" :class="badgeClass">{{ label }}</span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    status: { type: String, required: true }
})

const map = {
    // Booking statuses
    pending:    { class: 'bg-warning text-dark', label: 'Pending' },
    confirmed:  { class: 'bg-primary', label: 'Confirmed' },
    checked_in: { class: 'bg-info text-dark', label: 'Checked In' },
    completed:  { class: 'bg-success', label: 'Completed' },
    cancelled:  { class: 'bg-danger', label: 'Cancelled' },
    no_show:    { class: 'bg-secondary', label: 'No Show' },
    // Space statuses
    active:     { class: 'bg-success', label: 'Active' },
    inactive:   { class: 'bg-secondary', label: 'Inactive' },
    rejected:   { class: 'bg-danger', label: 'Rejected' },
    // User statuses
    banned:     { class: 'bg-danger', label: 'Banned' },
}

const badgeClass = computed(() => map[props.status]?.class ?? 'bg-secondary')
const label = computed(() => map[props.status]?.label ?? props.status)
</script>
