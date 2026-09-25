<template>
    <AdminLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <span>Booking Details</span>
                <Link :href="route('admin.bookings.index')" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Bookings
                </Link>
            </div>
        </template>

        <div class="row g-4">
            <!-- Booking Info -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Booking Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Booking Reference</label>
                                <div class="fw-medium font-monospace">{{ booking.booking_ref }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Status</label>
                                <div><StatusBadge :status="booking.status" /></div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Vehicle Number</label>
                                <div class="fw-medium">{{ booking.vehicle_number }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Vehicle Type</label>
                                <div class="fw-medium text-capitalize">{{ booking.vehicle_type }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Check In</label>
                                <div class="fw-medium">{{ formatDate(booking.check_in) }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Check Out</label>
                                <div class="fw-medium">{{ formatDate(booking.check_out) }}</div>
                            </div>
                            <div class="col-md-6" v-if="booking.actual_check_in">
                                <label class="text-muted small">Actual Check In</label>
                                <div class="fw-medium">{{ formatDate(booking.actual_check_in) }}</div>
                            </div>
                            <div class="col-md-6" v-if="booking.actual_check_out">
                                <label class="text-muted small">Actual Check Out</label>
                                <div class="fw-medium">{{ formatDate(booking.actual_check_out) }}</div>
                            </div>
                            <div class="col-12">
                                <label class="text-muted small">Created At</label>
                                <div class="fw-medium">{{ booking.created_at }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Payment Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="text-muted small">Amount</label>
                                <div class="fw-medium">₹{{ booking.amount }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Platform Fee</label>
                                <div class="fw-medium">₹{{ booking.platform_fee }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Total Amount</label>
                                <div class="fw-bold fs-5">₹{{ booking.total_amount }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Payment Status</label>
                                <div>
                                    <span class="badge" :class="paymentStatusBadge(booking.payment_status)">
                                        {{ booking.payment_status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- User Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">User Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                                 style="width:48px;height:48px;">
                                <span class="fw-bold text-primary">{{ booking.user.name.charAt(0).toUpperCase() }}</span>
                            </div>
                            <div>
                                <div class="fw-medium">{{ booking.user.name }}</div>
                                <div class="text-muted small">{{ booking.user.email }}</div>
                            </div>
                        </div>
                        <div v-if="booking.user.phone" class="text-muted small">
                            <i class="bi bi-telephone me-1"></i>{{ booking.user.phone }}
                        </div>
                    </div>
                </div>

                <!-- Space Info -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Parking Space</h6>
                    </div>
                    <div class="card-body">
                        <div class="fw-medium mb-1">{{ booking.space.name }}</div>
                        <div class="text-muted small mb-2">{{ booking.space.address }}</div>
                        <div class="text-muted small">
                            <i class="bi bi-geo-alt me-1"></i>Slot: {{ booking.slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
    booking: Object,
})

function formatDate(datetime) {
    if (!datetime) return '-'
    return new Date(datetime).toLocaleString('en-IN', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    })
}

function paymentStatusBadge(status) {
    return {
        'bg-success': status === 'paid',
        'bg-warning': status === 'pending',
        'bg-danger': status === 'failed',
        'bg-info': status === 'refunded',
    }
}
</script>
