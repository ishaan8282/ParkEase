<template>
    <AdminLayout>
        <template #header>Parking Spaces</template>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <input v-model="filters.search" @input="search" type="text"
                               class="form-control" placeholder="Search by name or address...">
                    </div>
                    <div class="col-md-4">
                        <select v-model="filters.status" @change="search" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending Approval</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="rejected">Rejected</option>
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
                <h6 class="mb-0">Spaces <span class="text-muted fw-normal">({{ spaces.total }})</span></h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Owner</th>
                                <th>City</th>
                                <th>Slots</th>
                                <th>Price/hr</th>
                                <th>Status</th>
                                <th>Verified</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="space in spaces.data" :key="space.id">
                                <td class="fw-medium">{{ space.name }}</td>
                                <td class="text-muted small">{{ space.owner }}</td>
                                <td class="text-muted small">{{ space.city }}</td>
                                <td>{{ space.total_slots }}</td>
                                <td>₹{{ space.price_per_hour }}</td>
                                <td><StatusBadge :status="space.status" /></td>
                                <td>
                                    <i v-if="space.is_verified" class="bi bi-patch-check-fill text-primary"></i>
                                    <i v-else class="bi bi-patch-question text-muted"></i>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <Link :href="route('admin.spaces.show', space.id)"
                                              class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </Link>
                                        <button v-if="space.status === 'pending'"
                                                @click="approveSpace(space.id)"
                                                class="btn btn-sm btn-success">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button v-if="space.status === 'pending'"
                                                @click="rejectSpace(space.id)"
                                                class="btn btn-sm btn-danger">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <button @click="deleteSpace(space.id, space.name)"
                                                class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="spaces.data.length === 0">
                                <td colspan="8" class="text-center text-muted py-5">No spaces found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ spaces.from }} to {{ spaces.to }} of {{ spaces.total }} spaces
                </small>
                <div class="d-flex gap-1">
                    <Link v-for="link in spaces.links" :key="link.label"
                          :href="link.url ?? '#'"
                          :class="['btn btn-sm', link.active ? 'btn-primary' : 'btn-outline-secondary', !link.url ? 'disabled' : '']"
                          v-html="link.label" />
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Parking Space</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Reason for rejection (optional)</label>
                        <textarea v-model="rejectReason" class="form-control" rows="3"
                                  placeholder="Provide a reason to help the owner improve their listing..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger" @click="confirmReject">Reject Space</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Link, router } from '@inertiajs/vue3'
import { reactive, ref } from 'vue'

const props = defineProps({
    spaces: Object,
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
        router.get(route('admin.spaces.index'), filters, { preserveState: true, replace: true })
    }, 300)
}

function resetFilters() {
    filters.search = ''
    filters.status = ''
    search()
}

function approveSpace(spaceId) {
    if (confirm('Approve this parking space?')) {
        router.patch(route('admin.spaces.verify', spaceId), { action: 'approve' })
    }
}

const rejectReason = ref('')
let rejectingSpaceId = null

function rejectSpace(spaceId) {
    rejectingSpaceId = spaceId
    rejectReason.value = ''
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'))
    modal.show()
}

function confirmReject() {
    router.patch(route('admin.spaces.verify', rejectingSpaceId), {
        action: 'reject',
        reason: rejectReason.value,
    }, {
        onSuccess: () => {
            bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide()
        }
    })
}

function deleteSpace(spaceId, name) {
    if (confirm(`Delete "${name}"? This cannot be undone.`)) {
        router.delete(route('admin.spaces.destroy', spaceId))
    }
}
</script>
