<template>
    <AdminLayout>
        <template #header>Bookings Overview</template>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <input v-model="filters.search" @input="search" type="text"
                               class="form-control" placeholder="Search by booking ref or vehicle number...">
                    </div>
                    <div class="col-md-4">
                        <select v-model="filters.status" @change="search" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="checked_in">Checked In</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="no_show">No Show</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button @click="resetFilters" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-lg"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">Bookings <span class="text-muted fw-normal">({{ bookings.total }})</span></h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ref</th>
                                <th>User</th>
                                <th>Space</th>
                                <th>Vehicle</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="booking in bookings.data" :key="booking.id">
                                <td><span class="font-monospace small text-primary">{{ booking.booking_ref }}</span></td>
                                <td class="fw-medium">{{ booking.user }}</td>
                                <td class="text-muted small text-truncate" style="max-width:120px;">{{ booking.space }}</td>
                                <td class="text-muted small">{{ booking.vehicle_number }}</td>
                                <td class="small">{{ formatDate(booking.check_in) }}</td>
                                <td class="small">{{ formatDate(booking.check_out) }}</td>
                                <td class="fw-medium">₹{{ booking.total_amount }}</td>
                                <td><StatusBadge :status="booking.status" /></td>
                                <td>
                                    <Link :href="route('admin.bookings.show', booking.id)"
                                          class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="bookings.data.length === 0">
                                <td colspan="9" class="text-center text-muted py-5">No bookings found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ bookings.from ?? 0 }} to {{ bookings.to ?? 0 }} of {{ bookings.total }} bookings
                </small>
                <div class="d-flex gap-1">
                    <Link v-for="link in bookings.links" :key="link.label"
                          :href="link.url ?? '#'"
                          :class="['btn btn-sm', link.active ? 'btn-primary' : 'btn-outline-secondary', !link.url ? 'disabled' : '']"
                          v-html="link.label" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Link, router } from '@inertiajs/vue3'
import { reactive } from 'vue'

const props = defineProps({
    bookings: Object,
    filters: Object,
})

const filters = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
})

let searchTimeout
function search() {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        router.get(route('admin.bookings.index'), filters, { preserveState: true, replace: true })
    }, 300)
}

function resetFilters() {
    filters.search = ''
    filters.status = ''
    search()
}

function formatDate(datetime) {
    if (!datetime) return '-'
    return new Date(datetime).toLocaleString('en-IN', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    })
}
</script>
