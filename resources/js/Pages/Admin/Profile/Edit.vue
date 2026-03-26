<template>
    <AdminLayout>
        <template #header>Edit Profile</template>

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Personal Information</h6>
                    </div>
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

                            <button type="submit" class="btn btn-primary" :disabled="processing">
                                {{ processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Change Password</h6>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submitPassword">
                            <div class="mb-3">
                                <label class="form-label">Current Password <span class="text-danger">*</span></label>
                                <input v-model="passwordForm.current_password" type="password" class="form-control" :class="{ 'is-invalid': errors.current_password }" placeholder="Enter current password">
                                <div v-if="errors.current_password" class="invalid-feedback">{{ errors.current_password }}</div>
                                <small class="text-muted">Required to change password</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input v-model="passwordForm.new_password" type="password" class="form-control" :class="{ 'is-invalid': errors.new_password }" placeholder="Leave blank to keep current">
                                <div v-if="errors.new_password" class="invalid-feedback">{{ errors.new_password }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input v-model="passwordForm.new_password_confirmation" type="password" class="form-control" :class="{ 'is-invalid': errors.new_password_confirmation }" placeholder="Confirm new password">
                                <div v-if="errors.new_password_confirmation" class="invalid-feedback">{{ errors.new_password_confirmation }}</div>
                            </div>

                            <button type="submit" class="btn btn-warning" :disabled="processingPassword">
                                {{ processingPassword ? 'Updating...' : 'Change Password' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <Link :href="route('admin.dashboard')" class="btn btn-outline-secondary w-100 mb-2">
                            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                        </Link>
                    </div>
                </div>
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
})

const passwordForm = useForm({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
})

let processing = false
let processingPassword = false

function submit() {
    processing = true
    form.patch(route('admin.profile.update'), {
        onSuccess: () => {
            processing = false
        },
        onError: () => {
            processing = false
        }
    })
}

function submitPassword() {
    processingPassword = true
    passwordForm.patch(route('admin.profile.update'), {
        onSuccess: () => {
            processingPassword = false
            passwordForm.current_password = ''
            passwordForm.new_password = ''
            passwordForm.new_password_confirmation = ''
        },
        onError: () => {
            processingPassword = false
        }
    })
}
</script>
