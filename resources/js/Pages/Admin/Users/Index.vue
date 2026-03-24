<template>
    <AdminLayout>
        <template #header>User Management</template>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-5">
                        <input v-model="filters.search" @input="search" type="text"
                               class="form-control" placeholder="Search by name or email...">
                    </div>
                    <div class="col-md-3">
                        <select v-model="filters.role" @change="search" class="form-select">
                            <option value="">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="owner">Owner</option>
                            <option value="driver">Driver</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select v-model="filters.status" @change="search" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="banned">Banned</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button @click="resetFilters" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Users <span class="text-muted fw-normal">({{ users.total }})</span></h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users.data" :key="user.id">
                                <td class="text-muted small">{{ user.id }}</td>
                                <td class="fw-medium">{{ user.name }}</td>
                                <td class="text-muted small">{{ user.email }}</td>
                                <td class="text-muted small">{{ user.phone ?? '-' }}</td>
                                <td>
                                    <span class="badge" :class="roleBadge(user.role)">{{ user.role }}</span>
                                </td>
                                <td><StatusBadge :status="user.status" /></td>
                                <td class="text-muted small">{{ user.created_at }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <!-- Change Status -->
                                            <li><h6 class="dropdown-header">Change Status</h6></li>
                                            <li v-for="status in ['active','inactive','banned']" :key="status">
                                                <button class="dropdown-item"
                                                        :class="{ 'text-danger': status === 'banned' }"
                                                        :disabled="user.status === status"
                                                        @click="updateStatus(user.id, status)">
                                                    <i class="bi bi-circle-fill me-2" style="font-size:8px;"
                                                       :class="statusDot(status)"></i>
                                                    {{ capitalize(status) }}
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <!-- Change Role -->
                                            <li><h6 class="dropdown-header">Change Role</h6></li>
                                            <li v-for="role in ['admin','owner','driver']" :key="role">
                                                <button class="dropdown-item"
                                                        :disabled="user.role === role"
                                                        @click="updateRole(user.id, role)">
                                                    {{ capitalize(role) }}
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-danger"
                                                        @click="deleteUser(user.id, user.name)">
                                                    <i class="bi bi-trash me-2"></i>Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="8" class="text-center text-muted py-5">No users found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ users.from }} to {{ users.to }} of {{ users.total }} users
                </small>
                <div class="d-flex gap-1">
                    <Link v-for="link in users.links" :key="link.label"
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
    users: Object,
    filters: Object,
})

const filters = reactive({
    search: props.filters.search ?? '',
    role:   props.filters.role ?? '',
    status: props.filters.status ?? '',
})

let searchTimeout
function search() {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        router.get(route('admin.users.index'), filters, { preserveState: true, replace: true })
    }, 300)
}

function resetFilters() {
    filters.search = ''
    filters.role   = ''
    filters.status = ''
    search()
}

function updateStatus(userId, status) {
    router.patch(route('admin.users.update-status', userId), { status })
}

function updateRole(userId, role) {
    router.patch(route('admin.users.update-role', userId), { role })
}

function deleteUser(userId, name) {
    if (confirm(`Are you sure you want to delete ${name}? This cannot be undone.`)) {
        router.delete(route('admin.users.destroy', userId))
    }
}

function roleBadge(role) {
    return { 'bg-danger': role === 'admin', 'bg-primary': role === 'owner', 'bg-secondary': role === 'driver' }
}

function statusDot(status) {
    return { 'text-success': status === 'active', 'text-warning': status === 'inactive', 'text-danger': status === 'banned' }
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1)
}
</script>
