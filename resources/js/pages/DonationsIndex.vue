<script setup>
import { ref } from 'vue';

const props = defineProps({
    donations: Array,
    columns: Array,
    type: String,
    status: String,
    currency: String,
    canCancel: Boolean,
    canRefund: Boolean,
});

const showConfirm = ref(false);
const confirmAction = ref(null);
const confirmMethod = ref('POST');
const confirmMessage = ref('');
const confirmDanger = ref(false);

function promptAction(url, message, { danger = false, method = 'POST' } = {}) {
    confirmAction.value = url;
    confirmMethod.value = method;
    confirmMessage.value = message;
    confirmDanger.value = danger;
    showConfirm.value = true;
}

function executeAction() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = confirmAction.value;

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = Statamic.$config.get('csrfToken');
    form.appendChild(csrf);

    if (confirmMethod.value !== 'POST') {
        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = confirmMethod.value;
        form.appendChild(method);
    }

    document.body.appendChild(form);
    form.submit();
}

function statusVariant(donation) {
    if (donation.paused) return 'warning';
    if (['succeeded', 'active'].includes(donation.status)) return 'positive';
    if (['canceled', 'refunded'].includes(donation.status)) return 'danger';
    if (donation.status === 'partially_refunded') return 'warning';
    return 'default';
}

function statusLabel(donation) {
    if (donation.paused) return 'Paused';
    const labels = {
        canceled: 'Cancelled',
        refunded: 'Refunded',
        partially_refunded: 'Partially Refunded',
    };
    return labels[donation.status] || donation.status.charAt(0).toUpperCase() + donation.status.slice(1);
}
</script>

<template>
    <ui-header title="Donations" icon="gift-present-surprise" />

    <ui-listing
        :items="donations"
        :columns="columns"
        sort-column="date"
        sort-direction="desc"
    >
        <template #cell-name="{ row: donation }">
            <inertia-link :href="donation.donor_url" class="text-blue-600 hover:text-blue-800" @click.stop>
                {{ donation.name }}
            </inertia-link>
            <span class="block text-2xs text-gray-500">{{ donation.email }}</span>
        </template>

        <template #cell-type="{ row: donation }">
            <ui-badge>{{ donation.type === 'recurring' ? 'Recurring' : 'Single' }}</ui-badge>
        </template>

        <template #cell-amount="{ row: donation }">
            {{ donation.currency.toUpperCase() }} {{ Number(donation.amount).toFixed(2) }}
        </template>

        <template #cell-status="{ row: donation }">
            <ui-badge :variant="statusVariant(donation)">{{ statusLabel(donation) }}</ui-badge>
        </template>

        <template #prepended-row-actions="{ row: donation }">
            <ui-dropdown-item
                v-if="donation.donor_url"
                :text="__('View Donor')"
                :href="donation.donor_url"
                icon="eye"
            />
            <template v-if="donation.type === 'recurring' && donation.status === 'active' && canCancel">
                <ui-dropdown-item
                    v-if="donation.paused"
                    :text="__('Resume')"
                    @click="promptAction(donation.resume_url, 'Resume this subscription?', { method: 'PUT' })"
                    icon="padlock-unlocked"
                />
                <ui-dropdown-item
                    v-else
                    :text="__('Pause')"
                    @click="promptAction(donation.pause_url, 'Pause this subscription? Invoices will be voided while paused.', { method: 'PUT' })"
                    icon="padlock-locked"
                />
                <ui-dropdown-item
                    :text="__('Cancel')"
                    @click="promptAction(donation.cancel_url, 'Cancel this subscription? This cannot be undone.', { danger: true, method: 'DELETE' })"
                    icon="trash"
                    variant="destructive"
                />
            </template>
            <ui-dropdown-item
                v-if="donation.type === 'single' && donation.status === 'succeeded' && canRefund"
                :text="__('Refund')"
                @click="promptAction(donation.refund_url, 'Refund this payment? This cannot be undone.', { danger: true })"
                icon="undo"
                variant="destructive"
            />
        </template>
    </ui-listing>

    <ui-confirmation-modal
        :open="showConfirm"
        :title="__('Confirm')"
        :body-text="confirmMessage"
        :button-text="__('Confirm')"
        :danger="confirmDanger"
        @confirm="executeAction"
        @cancel="showConfirm = false"
    />
</template>
