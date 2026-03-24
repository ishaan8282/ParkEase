<template>
    <AdminLayout>
        <template #header>Dashboard Overview</template>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                            <i class="bi bi-people fs-4 text-primary"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Users</div>
                            <div class="fs-4 fw-bold">{{ stats.total_users }}</div>
                            <div class="text-muted" style="font-size:11px;">
                                {{ stats.total_drivers }} drivers · {{ stats.total_owners }} owners
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-success bg-opacity-10 p-3">
                            <i class="bi bi-building fs-4 text-success"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Parking Spaces</div>
                            <div class="fs-4 fw-bold">{{ stats.total_spaces }}</div>
                            <div class="text-warning" style="font-size:11px;" v-if="stats.pending_spaces > 0">
                                {{ stats.pending_spaces }} pending approval
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-warning bg-opacity-10 p-3">
                            <i class="bi bi-calendar-check fs-4 text-warning"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Bookings</div>
                            <div class="fs-4 fw-bold">{{ stats.total_bookings }}</div>
                            <div class="text-muted" style="font-size:11px;">{{ stats.active_bookings }} active</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-danger bg-opacity-10 p-3">
                            <i class="bi bi-currency-rupee fs-4 text-danger"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Revenue</div>
                            <div class="fs-4 fw-bold">₹{{ Number(stats.total_revenue).toLocaleString('en-IN') }}</div>
                            <div class="text-muted" style="font-size:11px;">₹{{ Number(stats.today_revenue).toLocaleString('en-IN') }} today</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Recent Bookings -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Recent Bookings</h6>
                        <Link :href="route('admin.bookings.index')" class="btn btn-sm btn-outline-primary">View All</Link>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ref</th>
                                        <th>User</th>
                                        <th>Space</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="booking in recentBookings" :key="booking.id">
                                        <td><span class="font-monospace small">{{ booking.booking_ref }}</span></td>
                                        <td>{{ booking.user }}</td>
                                        <td class="text-truncate" style="max-width:120px;">{{ booking.space }}</td>
                                        <td>₹{{ booking.total_amount }}</td>
                                        <td><StatusBadge :status="booking.status" /></td>
                                    </tr>
                                    <tr v-if="recentBookings.length === 0">
                                        <td colspan="5" class="text-center text-muted py-4">No bookings yet</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Recent Users</h6>
                        <Link :href="route('admin.users.index')" class="btn btn-sm btn-outline-primary">View All</Link>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li v-for="user in recentUsers" :key="user.id"
                                class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center"
                                         style="width:36px;height:36px;">
                                        <span class="fw-bold text-secondary small">{{ user.name.charAt(0).toUpperCase() }}</span>
                                    </div>
                                    <div>
                                        <div class="fw-medium small">{{ user.name }}</div>
                                        <div class="text-muted" style="font-size:11px;">{{ user.email }}</div>
                                    </div>
                                </div>
                                <span class="badge" :class="roleBadge(user.role)">{{ user.role }}</span>
                            </li>
                            <li v-if="recentUsers.length === 0" class="list-group-item text-center text-muted py-4">
                                No users yet
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
import StatusBadge from '@/Components/StatusBadge.vue'

defineProps({
    stats: Object,
    recentBookings: Array,
    recentUsers: Array,
})

function roleBadge(role) {
    return {
        'bg-danger': role === 'admin',
        'bg-primary': role === 'owner',
        'bg-secondary': role === 'driver',
    }
}
</script>
