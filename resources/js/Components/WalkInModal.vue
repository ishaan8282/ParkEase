<template>
  <div class="pe-modal-overlay" v-if="show" @click.self="$emit('close')">
    <div class="pe-modal-card">
      <!-- Modal Header -->
      <div class="pe-modal-header">
        <div>
          <div class="pe-modal-badge">Direct Gate Entry</div>
          <h3 class="pe-modal-title">Register Walk-In Customer</h3>
        </div>
        <button class="pe-modal-close" @click="$emit('close')" type="button">✕</button>
      </div>

      <!-- Errors alert -->
      <div v-if="errorMessage" class="pe-form-error-banner">
        <span>⚠️ {{ errorMessage }}</span>
      </div>

      <form @submit.prevent="submit" class="pe-modal-body">
        <!-- Parking Space & Slot Selection -->
        <div class="pe-form-row">
          <div class="pe-form-group">
            <label>Available Slot *</label>
            <select v-model="form.parking_slot_id" required class="pe-select">
              <option :value="null" disabled>Select available slot</option>
              <option v-for="slot in availableSlots" :key="slot.id" :value="slot.id">
                {{ slot.parking_space?.name ? slot.parking_space.name + ' — ' : '' }}{{ slot.slot_number }} ({{ slot.type }})
              </option>
            </select>
            <small v-if="selectedSlot">
              Rate: ₹{{ selectedSlot.parking_space?.price_per_hour || 0 }}/hr • Slot type: {{ selectedSlot.type }}
            </small>
            <small v-else-if="!availableSlots || availableSlots.length === 0" style="color: #FF6B6B;">
              No available slots found. All slots are currently occupied or reserved.
            </small>
          </div>

          <div class="pe-form-group">
            <label>Vehicle Number *</label>
            <input
              v-model="form.vehicle_number"
              type="text"
              placeholder="e.g. HP01A1234"
              required
              class="pe-input pe-input--uppercase"
            />
          </div>
        </div>

        <!-- Vehicle Type & Duration -->
        <div class="pe-form-row">
          <div class="pe-form-group">
            <label>Vehicle Type</label>
            <select v-model="form.vehicle_type" class="pe-select">
              <option value="car">Car (Standard)</option>
              <option value="bike">Bike / Two Wheeler</option>
              <option value="suv">SUV / MUV</option>
              <option value="bus">Bus / Heavy Vehicle</option>
            </select>
          </div>

          <div class="pe-form-group">
            <label>Expected Duration</label>
            <select v-model.number="form.duration_hours" class="pe-select">
              <option :value="1">1 Hour</option>
              <option :value="2">2 Hours (Standard)</option>
              <option :value="3">3 Hours</option>
              <option :value="4">4 Hours</option>
              <option :value="6">6 Hours</option>
              <option :value="8">8 Hours</option>
              <option :value="12">12 Hours</option>
              <option :value="24">24 Hours (Full Day)</option>
            </select>
          </div>
        </div>

        <!-- Customer Name & Phone -->
        <div class="pe-form-row">
          <div class="pe-form-group">
            <label>Customer Name *</label>
            <input
              v-model="form.customer_name"
              type="text"
              placeholder="e.g. Rahul Sharma"
              required
              class="pe-input"
            />
          </div>

          <div class="pe-form-group">
            <label>Phone Number *</label>
            <input
              v-model="form.customer_phone"
              type="tel"
              placeholder="e.g. 9876543210"
              required
              class="pe-input"
            />
          </div>
        </div>

        <!-- Email & Notes -->
        <div class="pe-form-row">
          <div class="pe-form-group">
            <label>Customer Email (Optional)</label>
            <input
              v-model="form.customer_email"
              type="email"
              placeholder="e.g. customer@example.com"
              class="pe-input"
            />
          </div>

          <div class="pe-form-group">
            <label>Payment Method & Status *</label>
            <select v-model="form.payment_status" required class="pe-select">
              <option value="cash">💵 Cash Received (Paid)</option>
              <option value="paid">💳 UPI / Card / Online (Paid)</option>
              <option value="pending">⏳ Pay on Departure (Pending)</option>
              <option value="waived">🎟️ Complimentary / Waived</option>
            </select>
          </div>
        </div>

        <!-- Calculation Summary Box -->
        <div class="pe-calc-box">
          <div class="pe-calc-row">
            <span>Hourly Rate:</span>
            <span>₹{{ hourlyRate.toFixed(2) }}</span>
          </div>
          <div class="pe-calc-row">
            <span>Estimated Duration:</span>
            <span>{{ form.duration_hours }} hr(s)</span>
          </div>
          <div class="pe-calc-row pe-calc-row--highlight">
            <span>Estimated Parking Fee:</span>
            <span class="pe-calc-amount">₹{{ calculatedAmount.toFixed(2) }}</span>
          </div>
        </div>

        <!-- Optional custom amount override & notes -->
        <div class="pe-form-row" style="margin-top: 14px;">
          <div class="pe-form-group">
            <label>Custom Amount Override (Optional)</label>
            <input
              v-model.number="form.custom_amount"
              type="number"
              step="0.01"
              min="0"
              :placeholder="'Default ₹' + calculatedAmount.toFixed(2)"
              class="pe-input"
            />
          </div>

          <div class="pe-form-group">
            <label>Notes / Gate Remarks</label>
            <input
              v-model="form.notes"
              type="text"
              placeholder="e.g. Paid cash at entry booth"
              class="pe-input"
            />
          </div>
        </div>

        <!-- Actions -->
        <div class="pe-modal-actions">
          <button type="button" class="pe-btn pe-btn--ghost" @click="$emit('close')">
            Cancel
          </button>
          <button
            type="submit"
            class="pe-btn pe-btn--neon"
            :disabled="submitting || !form.parking_slot_id || !form.vehicle_number || !form.customer_name"
          >
            <span v-if="submitting">Processing Check-In...</span>
            <span v-else>✓ Check In & Issue Slip (₹{{ finalAmount.toFixed(2) }})</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  availableSlots: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['close', 'created'])

const submitting = ref(false)
const errorMessage = ref('')

const form = ref({
  parking_slot_id: null,
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  vehicle_number: '',
  vehicle_type: 'car',
  duration_hours: 2,
  custom_amount: null,
  payment_status: 'cash',
  notes: '',
})

// Auto-select first slot if available and nothing selected
watch(
  () => props.show,
  (newVal) => {
    if (newVal) {
      errorMessage.value = ''
      if (props.availableSlots && props.availableSlots.length > 0 && !form.value.parking_slot_id) {
        form.value.parking_slot_id = props.availableSlots[0].id
        form.value.vehicle_type = props.availableSlots[0].type || 'car'
      }
    }
  }
)

const selectedSlot = computed(() => {
  if (!props.availableSlots || !form.value.parking_slot_id) return null
  return props.availableSlots.find((s) => s.id === form.value.parking_slot_id) || null
})

// When slot changes, default vehicle type to slot type
watch(
  () => form.value.parking_slot_id,
  (newId) => {
    if (newId && selectedSlot.value) {
      form.value.vehicle_type = selectedSlot.value.type || 'car'
    }
  }
)

const hourlyRate = computed(() => {
  return parseFloat(selectedSlot.value?.parking_space?.price_per_hour || 0)
})

const calculatedAmount = computed(() => {
  const hrs = parseFloat(form.value.duration_hours) || 1
  return Math.round(hrs * hourlyRate.value * 100) / 100
})

const finalAmount = computed(() => {
  if (form.value.custom_amount !== null && form.value.custom_amount !== '' && !isNaN(form.value.custom_amount)) {
    return parseFloat(form.value.custom_amount)
  }
  return calculatedAmount.value
})

async function submit() {
  submitting.value = true
  errorMessage.value = ''

  try {
    const payload = {
      ...form.value,
      vehicle_number: form.value.vehicle_number.toUpperCase().trim(),
    }

    const response = await axios.post('/owner/bookings/walk-in', payload)
    emit('created', response.data)
    emit('close')

    // Reset form for next entry
    form.value = {
      parking_slot_id: props.availableSlots?.[0]?.id || null,
      customer_name: '',
      customer_phone: '',
      customer_email: '',
      vehicle_number: '',
      vehicle_type: props.availableSlots?.[0]?.type || 'car',
      duration_hours: 2,
      custom_amount: null,
      payment_status: 'cash',
      notes: '',
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      const firstKey = Object.keys(error.response.data.errors)[0]
      errorMessage.value = error.response.data.errors[firstKey][0]
    } else if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message
    } else {
      errorMessage.value = 'Failed to register walk-in. Please try again.'
    }
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.pe-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 999;
  background: rgba(3, 6, 12, 0.85);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.pe-modal-card {
  background: #0a0f1d;
  border: 1px solid rgba(0, 212, 255, 0.25);
  border-radius: 20px;
  width: 100%;
  max-width: 680px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.7), 0 0 40px rgba(0, 212, 255, 0.1);
  overflow: hidden;
  max-height: 92vh;
  display: flex;
  flex-direction: column;
}

.pe-modal-header {
  padding: 20px 26px;
  background: rgba(15, 23, 42, 0.6);
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.pe-modal-badge {
  display: inline-block;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #00d4ff;
  background: rgba(0, 212, 255, 0.12);
  padding: 2px 8px;
  border-radius: 4px;
  margin-bottom: 6px;
}

.pe-modal-title {
  font-family: 'Syne', sans-serif;
  font-size: 1.35rem;
  font-weight: 800;
  color: #e8edf5;
  margin: 0;
}

.pe-modal-close {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(232, 237, 245, 0.6);
  width: 32px;
  height: 32px;
  border-radius: 8px;
  font-size: 0.9rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.pe-modal-close:hover {
  background: rgba(255, 107, 107, 0.15);
  color: #ff6b6b;
  border-color: rgba(255, 107, 107, 0.3);
}

.pe-form-error-banner {
  margin: 16px 26px 0;
  padding: 12px 16px;
  background: rgba(255, 107, 107, 0.12);
  border: 1px solid rgba(255, 107, 107, 0.3);
  border-radius: 10px;
  color: #ff6b6b;
  font-size: 0.85rem;
}

.pe-modal-body {
  padding: 22px 26px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}

.pe-form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 14px;
}

.pe-form-group {
  display: flex;
  flex-direction: column;
}

.pe-form-group label {
  font-size: 0.76rem;
  font-weight: 600;
  color: #00d4ff;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 6px;
}

.pe-input,
.pe-select {
  width: 100%;
  background: rgba(15, 20, 33, 0.95);
  border: 1px solid rgba(0, 212, 255, 0.2);
  border-radius: 10px;
  padding: 11px 14px;
  color: #e8edf5;
  font-family: 'Outfit', sans-serif;
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.pe-input:focus,
.pe-select:focus {
  border-color: #00d4ff;
  box-shadow: 0 0 12px rgba(0, 212, 255, 0.2);
}

.pe-input--uppercase {
  text-transform: uppercase;
  font-family: monospace;
  font-weight: bold;
}

.pe-form-group small {
  margin-top: 5px;
  font-size: 0.75rem;
  color: rgba(232, 237, 245, 0.5);
}

.pe-calc-box {
  background: rgba(0, 212, 255, 0.04);
  border: 1px dashed rgba(0, 212, 255, 0.25);
  border-radius: 12px;
  padding: 14px 18px;
  margin-top: 6px;
}

.pe-calc-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.85rem;
  color: rgba(232, 237, 245, 0.7);
  margin-bottom: 6px;
}

.pe-calc-row:last-child {
  margin-bottom: 0;
}

.pe-calc-row--highlight {
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 0.95rem;
  font-weight: 600;
  color: #e8edf5;
}

.pe-calc-amount {
  font-family: 'Syne', sans-serif;
  font-size: 1.25rem;
  font-weight: 800;
  color: #00e5a0;
  text-shadow: 0 0 10px rgba(0, 229, 160, 0.4);
}

.pe-modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
  padding-top: 18px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

@media (max-width: 640px) {
  .pe-form-row {
    grid-template-columns: 1fr;
  }
}
</style>
