<template>
    <AdminLayout>
        <template #header>{{ user.is_self ? 'Edit My Profile' : 'Edit User' }}</template>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input v-model="form.name" type="text" class="form-control" :class="{ 'is-invalid': errors.name }">
                        <div v-if="errors.name" class="invalid-feedback">{{ errors.name }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input v-model="form.email" type="email" class="form-control" :class="{ 'is-invalid': errors.email }">
                        <div v-if="errors.email" class="invalid-feedback">{{ errors.email }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input v-model="form.phone" type="text" class="form-control" :class="{ 'is-invalid': errors.phone }">
                        <div v-if="errors.phone" class="invalid-feedback">{{ errors.phone }}</div>
                    </div>

                    <div v-if="!user.is_self" class="mb-3">
                        <label class="form-label">Role</label>
                        <select v-model="form.role" class="form-select" disabled>
                            <option value="admin">Admin</option>
                            <option value="owner">Owner</option>
                            <option value="driver">Driver</option>
                        </select>
                        <small class="text-muted">Role cannot be changed here. Use the actions menu in users list.</small>
                    </div>

                    <div v-if="!user.is_self" class="mb-3">
                        <label class="form-label">Status</label>
                        <select v-model="form.status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="banned">Banned</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input v-model="form.password" type="password" class="form-control" :class="{ 'is-invalid': errors.password }" placeholder="Leave blank to keep current password">
                        <div v-if="errors.password" class="invalid-feedback">{{ errors.password }}</div>
                        <small class="text-muted">Leave blank to keep current password</small>
                    </div>

                    <!-- Current password only shown when editing own account -->
                    <div v-if="user.is_self" class="mb-3">
                        <label class="form-label">Current Password <span class="text-danger">*</span></label>
                        <input v-model="form.current_password" type="password" class="form-control" :class="{ 'is-invalid': errors.current_password }" placeholder="Enter current password to change password">
                        <div v-if="errors.current_password" class="invalid-feedback">{{ errors.current_password }}</div>
                        <small class="text-muted">Required to change password</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" :disabled="processing">
                            {{ processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                        <Link :href="route('admin.users.index')" class="btn btn-outline-secondary">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    user: Object,
    errors: Object,
})

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone,
    role: props.user.role,
    ...(props.user.is_self ? {} : { status: props.user.status }),
    password: '',
    current_password: '',
})

let processing = false

function submit() {
    processing = true
    form.patch(route('admin.users.update', props.user.id), {
        onSuccess: () => {
            processing = false
        },
        onError: () => {
            processing = false
        }
    })
}
</script>
