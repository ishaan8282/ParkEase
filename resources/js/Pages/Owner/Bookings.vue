<template>
    <OwnerLayout
        page-title="Bookings"
        page-title-highlight="Overview"
        page-subtitle="Manage and track all your parking space reservations"
        :pending-bookings="stats?.pending || 0"
    >
        <!-- Stat Pills -->
        <div class="pe-pills">
            <button class="pe-pill" :class="{ active: !filters.status }" @click="filterBy('')">
                <span class="pe-pill__num">{{ stats.total }}</span>
                <span class="pe-pill__label">All Bookings</span>
            </button>
            <button class="pe-pill" :class="{ active: filters.status === 'pending' }" @click="filterBy('pending')">
                <span class="pe-pill__num" style="color:#FFD93D;text-shadow:0 0 12px rgba(255,217,61,.7)">{{ stats.pending }}</span>
                <span class="pe-pill__label">Pending</span>
            </button>
            <button class="pe-pill" :class="{ active: filters.status === 'confirmed' }" @click="filterBy('confirmed')">
                <span class="pe-pill__num" style="color:#00E5A0;text-shadow:0 0 12px rgba(0,229,160,.7)">{{ stats.confirmed }}</span>
                <span class="pe-pill__label">Confirmed</span>
            </button>
            <button class="pe-pill" :class="{ active: filters.status === 'checked_in' }" @click="filterBy('checked_in')">
                <span class="pe-pill__num" style="color:#B57BFF;text-shadow:0 0 12px rgba(181,123,255,.7)">{{ stats.checked_in ?? 0 }}</span>
                <span class="pe-pill__label">Checked In</span>
            </button>
            <button class="pe-pill" :class="{ active: filters.status === 'completed' }" @click="filterBy('completed')">
                <span class="pe-pill__num" style="color:#00D4FF;text-shadow:0 0 12px rgba(0,212,255,.7)">{{ stats.completed }}</span>
                <span class="pe-pill__label">Completed</span>
            </button>
        </div>

        <!-- Filters bar -->
        <div class="pe-filterbar">
            <div class="pe-filterbar__dates">
                <div class="pe-date-input">
                    <span class="pe-date-input__icon">📅</span>
                    <input v-model="dateFrom" type="date" @change="applyFilters" />
                </div>
                <span class="pe-filterbar__sep">→</span>
                <div class="pe-date-input">
                    <span class="pe-date-input__icon">📅</span>
                    <input v-model="dateTo" type="date" @change="applyFilters" />
                </div>
            </div>
            <button v-if="hasFilters" @click="clearFilters" class="pe-clear-btn">
                ✕ Clear filters
            </button>
        </div>

        <!-- Table card -->
        <div class="pe-card">
            <div class="pe-card__glow"></div>
            <div class="pe-table-wrap">
                <table class="pe-table">
                    <thead>
                        <tr>
                            <th>Ref</th>
                            <th>Customer</th>
                            <th>Space</th>
                            <th>Slot</th>
                            <th>Vehicle</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="booking in bookings.data" :key="booking.id" class="pe-tr">
                            <td>
                                <span class="pe-ref">{{ booking.booking_ref }}</span>
                            </td>
                            <td>
                                <div class="pe-customer">
                                    <div class="pe-customer__avatar">{{ booking.user?.name?.charAt(0) }}</div>
                                    <div>
                                        <div class="pe-customer__name">{{ booking.user?.name }}</div>
                                        <div class="pe-customer__phone">{{ booking.user?.phone }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="pe-td-muted">{{ booking.parking_space?.name }}</td>
                            <td>
                                <span class="pe-slot-badge">{{ booking.parking_slot?.slot_number ?? '-' }}</span>
                            </td>
                            <td>
                                <div class="pe-vehicle">
                                    <span>{{ booking.vehicle_number }}</span>
                                    <small>{{ booking.vehicle_type }}</small>
                                </div>
                            </td>
                            <td class="pe-td-muted pe-td-date">{{ formatDate(booking.check_in) }}</td>
                            <td class="pe-td-muted pe-td-date">{{ formatDate(booking.check_out) }}</td>
                            <td>
                                <span class="pe-amount">₹{{ booking.total_amount }}</span>
                            </td>
                            <td>
                                <span class="pe-status-badge" :class="'pe-status--' + booking.status">
                                    {{ booking.status.replace('_', ' ') }}
                                </span>
                            </td>
                            <td>
                                <div class="pe-actions">
                                    <button v-if="booking.status === 'pending'"
                                            @click="confirmBooking(booking.id)"
                                            class="pe-act pe-act--confirm" title="Confirm">✓</button>
                                    <button v-if="!['cancelled','completed'].includes(booking.status)"
                                            @click="cancelBooking(booking.id)"
                                            class="pe-act pe-act--cancel" title="Cancel">✕</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="bookings.data.length === 0" class="pe-empty">
                    <div class="pe-empty__icon">📋</div>
                    <p>No bookings found</p>
                    <small>Try adjusting your filters</small>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <OwnerPagination :links="bookings.links" />
    </OwnerLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import OwnerLayout from '@/Components/Owner/OwnerLayout.vue'
import OwnerPagination from '@/Components/Owner/OwnerPagination.vue'

const page = usePage()
const flash = computed(() => page.props.flash)

const props = defineProps({
    bookings: Object,
    stats:    Object,
    filters:  Object,
})

const dateFrom    = ref(props.filters?.from_date || '')
const dateTo      = ref(props.filters?.to_date || '')

const hasFilters = computed(() => props.filters?.status || dateFrom.value || dateTo.value)

function filterBy(status) {
    const params = new URLSearchParams()
    if (status) params.set('status', status)
    if (dateFrom.value) params.set('from_date', dateFrom.value)
    if (dateTo.value)   params.set('to_date', dateTo.value)
    window.location.href = route('owner.bookings.index') + '?' + params.toString()
}

function applyFilters() { filterBy(props.filters?.status || '') }
function clearFilters()  { window.location.href = route('owner.bookings.index') }

function formatDate(d) {
    if (!d) return '-'
    return new Date(d).toLocaleString('en-IN', { day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit' })
}

function confirmBooking(id) {
    if (confirm('Confirm this booking?')) {
        console.log('confirm', id)
    }
}

function cancelBooking(id) {
    const reason = prompt('Reason for cancellation:')
    if (reason !== null) {
        console.log('cancel', id, reason)
    }
}
</script>

<style scoped>
/* Page-specific styles only */

/* Pills */
.pe-pills { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; position: relative; z-index: 1; }
.pe-pill {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 20px;
    background: rgba(10,14,24,.9);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 100px;
    cursor: pointer; transition: all .25s;
    font-family: 'Outfit', sans-serif;
}
.pe-pill:hover { border-color: rgba(0,212,255,.3); background: rgba(0,212,255,.04); }
.pe-pill.active { background: rgba(0,212,255,.08); border-color: rgba(0,212,255,.35); box-shadow: 0 0 20px rgba(0,212,255,.07); }
.pe-pill__num { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1.1rem; color: #E8EDF5; }
.pe-pill__label { font-size: .82rem; color: rgba(232,237,245,.45); }

/* Filter bar */
.pe-filterbar { display: flex; align-items: center; gap: 16px; margin-bottom: 24px; position: relative; z-index: 1; }
.pe-date-input {
    display: flex; align-items: center; gap: 8px;
    background: rgba(10,14,24,.9);
    border: 1px solid rgba(0,212,255,.12);
    border-radius: 10px; padding: 9px 14px;
    transition: border-color .2s;
}
.pe-date-input:focus-within { border-color: rgba(0,212,255,.4); box-shadow: 0 0 0 2px rgba(0,212,255,.06); }
.pe-date-input__icon { font-size: .85rem; }
.pe-date-input input { background: none; border: none; outline: none; color: #E8EDF5; font-family: 'Outfit', sans-serif; font-size: .85rem; }
.pe-date-input input::-webkit-calendar-picker-indicator { filter: invert(1) opacity(.4); }
.pe-filterbar__sep { color: rgba(232,237,245,.3); font-size: .85rem; }
.pe-clear-btn {
    padding: 9px 16px; border-radius: 10px;
    background: rgba(255,107,107,.08); border: 1px solid rgba(255,107,107,.2);
    color: #FF6B6B; font-family: 'Outfit', sans-serif; font-size: .82rem;
    cursor: pointer; transition: all .2s;
}
.pe-clear-btn:hover { background: rgba(255,107,107,.15); }

/* Card */
.pe-card { background: rgba(10,14,24,.9); border: 1px solid rgba(0,212,255,.08); border-radius: 20px; overflow: hidden; position: relative; z-index: 1; box-shadow: 0 0 60px rgba(0,212,255,.04); }
.pe-card__glow { position: absolute; top: 0; left: 0; right: 0; height: 1px; background: linear-gradient(90deg, transparent, rgba(0,212,255,.4), transparent); pointer-events: none; }

/* Table */
.pe-table-wrap { overflow-x: auto; }
.pe-table { width: 100%; border-collapse: collapse; min-width: 960px; }
.pe-table thead tr { background: rgba(6,9,16,.8); border-bottom: 1px solid rgba(255,255,255,.05); }
.pe-table th { text-align: left; padding: 14px 16px; font-size: .68rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: rgba(232,237,245,.35); }
.pe-tr { border-bottom: 1px solid rgba(255,255,255,.03); transition: background .2s; }
.pe-tr:hover { background: rgba(0,212,255,.025); }
.pe-tr:last-child { border-bottom: none; }
.pe-table td { padding: 14px 16px; font-size: .88rem; }

/* Customer */
.pe-ref { font-family: monospace; font-size: .82rem; font-weight: 700; color: #00D4FF; text-shadow: 0 0 8px rgba(0,212,255,.4); }
.pe-customer { display: flex; align-items: center; gap: 10px; }
.pe-customer__avatar { width: 32px; height: 32px; border-radius: 8px; background: rgba(0,212,255,.1); border: 1px solid rgba(0,212,255,.2); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; color: #00D4FF; flex-shrink: 0; }
.pe-customer__name { font-weight: 500; font-size: .88rem; }
.pe-customer__phone { font-size: .75rem; color: rgba(232,237,245,.4); margin-top: 1px; }
.pe-td-muted { color: rgba(232,237,245,.55); }
.pe-td-date { font-size: .8rem !important; }

/* Slot badge */
.pe-slot-badge { display: inline-flex; align-items: center; justify-content: center; padding: 3px 10px; border-radius: 6px; background: rgba(181,123,255,.1); border: 1px solid rgba(181,123,255,.2); color: #B57BFF; font-size: .75rem; font-weight: 600; }
.pe-vehicle { display: flex; flex-direction: column; }
.pe-vehicle small { font-size: .72rem; color: rgba(232,237,245,.4); text-transform: capitalize; margin-top: 2px; }
.pe-amount { font-family: 'Syne', sans-serif; font-weight: 800; font-size: .95rem; color: #00E5A0; text-shadow: 0 0 10px rgba(0,229,160,.4); }

/* Status badges */
.pe-status-badge { display: inline-block; padding: 4px 12px; border-radius: 100px; font-size: .72rem; font-weight: 700; text-transform: capitalize; letter-spacing: .3px; }
.pe-status--pending    { background: rgba(255,217,61,.12);  color: #FFD93D;  border: 1px solid rgba(255,217,61,.25);  box-shadow: 0 0 8px rgba(255,217,61,.1); }
.pe-status--confirmed  { background: rgba(0,229,160,.12);   color: #00E5A0;  border: 1px solid rgba(0,229,160,.25);   box-shadow: 0 0 8px rgba(0,229,160,.1); }
.pe-status--checked_in { background: rgba(181,123,255,.12); color: #B57BFF;  border: 1px solid rgba(181,123,255,.25); box-shadow: 0 0 8px rgba(181,123,255,.1); }
.pe-status--completed  { background: rgba(0,212,255,.12);   color: #00D4FF;  border: 1px solid rgba(0,212,255,.25);   box-shadow: 0 0 8px rgba(0,212,255,.1); }
.pe-status--cancelled  { background: rgba(255,107,107,.12); color: #FF6B6B;  border: 1px solid rgba(255,107,107,.25); box-shadow: 0 0 8px rgba(255,107,107,.1); }
.pe-status--no_show    { background: rgba(255,255,255,.06); color: rgba(232,237,245,.4); border: 1px solid rgba(255,255,255,.1); }

/* Actions */
.pe-actions { display: flex; gap: 6px; }
.pe-act { width: 30px; height: 30px; border-radius: 8px; border: none; cursor: pointer; font-size: .85rem; font-weight: 700; transition: all .2s; display: flex; align-items: center; justify-content: center; }
.pe-act--confirm { background: rgba(0,229,160,.12); color: #00E5A0; border: 1px solid rgba(0,229,160,.2); }
.pe-act--confirm:hover { background: #00E5A0; color: #060910; box-shadow: 0 0 16px rgba(0,229,160,.4); }
.pe-act--cancel  { background: rgba(255,107,107,.12); color: #FF6B6B; border: 1px solid rgba(255,107,107,.2); }
.pe-act--cancel:hover  { background: #FF6B6B; color: #060910; box-shadow: 0 0 16px rgba(255,107,107,.4); }

/* Empty */
.pe-empty { text-align: center; padding: 72px 24px; }
.pe-empty__icon { font-size: 3rem; margin-bottom: 12px; }
.pe-empty p { color: rgba(232,237,245,.45); font-size: 1rem; margin-bottom: 6px; }
.pe-empty small { color: rgba(232,237,245,.25); font-size: .82rem; }
</style>
