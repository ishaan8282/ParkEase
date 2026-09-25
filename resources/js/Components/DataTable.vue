<template>
    <div class="table-responsive">
        <table class="table table-hover mb-0" :id="tableId">
            <thead class="table-light">
                <tr>
                    <th
                        v-for="column in columns"
                        :key="column.key"
                        :class="column.sortable ? 'sortable' : ''"
                        @click="column.sortable && sort(column.key)"
                        style="cursor: pointer;"
                    >
                        {{ column.label }}
                        <span v-if="column.sortable" class="sort-indicator">
                            <i v-if="sortColumn === column.key" :class="sortDirection === 'asc' ? 'bi bi-chevron-up' : 'bi bi-chevron-down'"></i>
                            <i v-else class="bi bi-chevron-expand"></i>
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="loading">
                    <td :colspan="columns.length" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2">Loading...</span>
                    </td>
                </tr>
                <tr v-else-if="data.length === 0">
                    <td :colspan="columns.length" class="text-center text-muted py-4">
                        {{ emptyText }}
                    </td>
                </tr>
                <tr
                    v-else
                    v-for="(row, index) in data"
                    :key="row.id || index"
                >
                    <template
                        v-for="column in columns"
                        :key="column.key"
                    >
                        <slot
                            :name="`cell-${column.key}`"
                            :row="row"
                            :column="column"
                        >
                            <td>{{ row[column.key] }}</td>
                        </slot>
                    </template>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination" class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted small">
            Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total || 0 }} entries
        </div>
        <div class="d-flex gap-1">
            <button
                v-for="link in pagination.links"
                :key="link.label"
                @click="link.url && visitLink(link.url)"
                :class="[
                    'btn btn-sm',
                    link.active ? 'btn-primary' : 'btn-outline-secondary',
                    !link.url ? 'disabled' : ''
                ]"
                :disabled="!link.url"
                v-html="link.label"
            ></button>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    columns: {
        type: Array,
        required: true
    },
    data: {
        type: Array,
        default: () => []
    },
    pagination: {
        type: Object,
        default: null
    },
    loading: {
        type: Boolean,
        default: false
    },
    emptyText: {
        type: String,
        default: 'No data found'
    },
    search: {
        type: String,
        default: ''
    },
    tableId: {
        type: String,
        default: 'data-table'
    }
})

const emit = defineEmits(['update:search'])

const sortColumn = ref('')
const sortDirection = ref('asc')
const searchTimeout = ref(null)

function sort(column) {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortColumn.value = column
        sortDirection.value = 'asc'
    }
    fetchData()
}

function visitLink(url) {
    router.get(url, {}, { replace: true, preserveState: true })
}

function fetchData() {
    // The parent component handles the actual data fetching
    // This is triggered by sort/pagination events
    emit('sort', { column: sortColumn.value, direction: sortDirection.value })
}

function onSearch(value) {
    clearTimeout(searchTimeout.value)
    searchTimeout.value = setTimeout(() => {
        emit('update:search', value)
    }, 300)
}

defineExpose({
    sortColumn,
    sortDirection
})
</script>

<style scoped>
.sortable {
    user-select: none;
}
.sort-indicator {
    font-size: 10px;
    margin-left: 4px;
}
</style>
