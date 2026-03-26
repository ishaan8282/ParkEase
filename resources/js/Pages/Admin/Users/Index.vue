<template>
    <AdminLayout>
        <template #header>User Management</template>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-5">
                        <input
                            v-model="filters.search"
                            @input="search"
                            type="text"
                            class="form-control"
                            placeholder="Search by name or email..."
                        >
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
                <div class="d-flex gap-2">
                    <select v-model="filters.per_page" @change="search" class="form-select form-select-sm" style="width: auto;">
                        <option :value="5">5</option>
                        <option :value="10">10</option>
                        <option :value="15">15</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                        <option :value="100">100</option>
                    </select>
                </div>
            </div>

            <DataTable
                :columns="columns"
                :data="users.data"
                :pagination="users"
                :loading="loading"
                @update:search="handleSearch"
                @sort="handleSort"
                tableId="users-table"
            >
                <template #cell-id="{ row }">
                    <td class="text-muted small">{{ row.id }}</td>
                </template>

                <template #cell-name="{ row }">
                    <td class="fw-medium">{{ row.name }}</td>
                </template>

                <template #cell-email="{ row }">
                    <td class="text-muted small">{{ row.email }}</td>
                </template>

                <template #cell-phone="{ row }">
                    <td class="text-muted small">{{ row.phone ?? '-' }}</td>
                </template>

                <template #cell-role="{ row }">
                    <td>
                        <span
                            class="badge"
                            :class="roleBadge(row.role)"
                        >{{ row.role }}</span>
                    </td>
                </template>

                <template #cell-status="{ row }">
                    <td>
                        <StatusBadge :status="row.status" />
                    </td>
                </template>

                <template #cell-created_at="{ row }">
                    <td class="text-muted small">{{ row.created_at }}</td>
                </template>

                <template #cell-actions="{ row }">
                    <td>
                        <div class="dropdown">
                            <button
                                class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                data-bs-toggle="dropdown"
                            >
                                Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <h6 class="dropdown-header">Change Status</h6>
                                </li>
                                <li
                                    v-for="status in ['active', 'inactive', 'banned']"
                                    :key="status"
                                >
                                    <button
                                        class="dropdown-item"
                                        :class="{ 'text-danger': status === 'banned' }"
                                        :disabled="row.status === status"
                                        @click="updateStatus(row.id, status)"
                                    >
                                        <i
                                            class="bi bi-circle-fill me-2"
                                            style="font-size:8px;"
                                            :class="statusDot(status)"
                                        ></i>
                                        {{ capitalize(status) }}
                                    </button>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <h6 class="dropdown-header">Change Role</h6>
                                </li>
                                <li
                                    v-for="role in ['admin', 'owner', 'driver']"
                                    :key="role"
                                >
                                    <button
                                        class="dropdown-item"
                                        :disabled="row.role === role"
                                        @click="updateRole(row.id, role)"
                                    >
                                        {{ capitalize(role) }}
                                    </button>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <Link
                                        :href="route('admin.users.edit', row.id)"
                                        class="dropdown-item"
                                    >
                                        <i class="bi bi-pencil me-2"></i>Edit
                                    </Link>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <button
                                        class="dropdown-item text-danger"
                                        @click="deleteUser(row.id, row.name)"
                                    >
                                        <i class="bi bi-trash me-2"></i>Delete
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </td>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import DataTable from '@/Components/DataTable.vue'
import { Link, router } from '@inertiajs/vue3'
import { reactive, ref } from 'vue'

const props = defineProps({
    users: Object,
    filters: Object,
})

const loading = ref(false)

const columns = [
    { key: 'id', label: '#', sortable: true },
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'phone', label: 'Phone', sortable: false },
    { key: 'role', label: 'Role', sortable: false },
    { key: 'status', label: 'Status', sortable: false },
    { key: 'created_at', label: 'Joined', sortable: true },
    { key: 'actions', label: 'Actions', sortable: false },
]

const filters = reactive({
    search: props.filters.search ?? '',
    role: props.filters.role ?? '',
    status: props.filters.status ?? '',
    sort: props.filters.sort ?? '',
    dir: props.filters.dir ?? '',
    per_page: parseInt(props.filters.per_page) || 15,
})

let searchTimeout
function search() {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        loading.value = true
        router.get(route('admin.users.index'), filters, {
            preserveState: true,
            replace: true,
            onFinish: () => {
                loading.value = false
            }
        })
    }, 300)
}

function handleSearch(value) {
    filters.search = value
    search()
}

function handleSort({ column, direction }) {
    filters.sort = column
    filters.dir = direction
    search()
}

function resetFilters() {
    filters.search = ''
    filters.role = ''
    filters.status = ''
    filters.sort = ''
    filters.dir = ''
    search()
}

function updateStatus(userId, status) {
    loading.value = true
    router.patch(route('admin.users.update-status', userId), { status }, {
        onFinish: () => {
            loading.value = false
        }
    })
}

function updateRole(userId, role) {
    loading.value = true
    router.patch(route('admin.users.update-role', userId), { role }, {
        onFinish: () => {
            loading.value = false
        }
    })
}

function deleteUser(userId, name) {
    if (confirm(`Are you sure you want to delete ${name}? This cannot be undone.`)) {
        loading.value = true
        router.delete(route('admin.users.destroy', userId), {
            onFinish: () => {
                loading.value = false
            }
        })
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
