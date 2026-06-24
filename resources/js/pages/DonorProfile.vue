<script setup>
import { ref } from 'vue';

const props = defineProps({
    user: Object,
    donations: Array,
    donationColumns: Array,
    subscriptions: Array,
    subscriptionColumns: Array,
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

function statusVariant(item) {
    if (item.paused) return 'warning';
    if (['succeeded', 'active'].includes(item.status)) return 'positive';
    if (['canceled', 'refunded'].includes(item.status)) return 'danger';
    if (item.status === 'partially_refunded') return 'warning';
    return 'default';
}

function statusLabel(item) {
    if (item.paused) return 'Paused';
    const labels = {
        canceled: 'Cancelled',
        refunded: 'Refunded',
        partially_refunded: 'Partially Refunded',
    };
    return labels[item.status] || item.status.charAt(0).toUpperCase() + item.status.slice(1);
}
</script>

<template>
    <div>
        <ui-header :title="user.name || user.email">
            <template #title>
                <div class="flex flex-col">
                    <span>{{ user.name || user.email }}</span>
                    <span v-if="user.name" class="text-sm font-normal text-gray-500">{{ user.email }}</span>
                </div>
            </template>
        </ui-header>

        <div v-if="subscriptions.length" class="mb-8">
            <ui-heading size="lg" :text="__('Recurring Donations')" class="mb-3" />
            <ui-listing
                :items="subscriptions"
                :columns="subscriptionColumns"
                sort-column="date"
                sort-direction="desc"
                :allow-search="false"
                :allow-customizing-columns="false"
            >
                <template #cell-amount="{ row: sub }">
                    {{ sub.currency.toUpperCase() }} {{ Number(sub.amount).toFixed(2) }}/mo
                </template>

                <template #cell-status="{ row: sub }">
                    <ui-badge :variant="statusVariant(sub)">{{ statusLabel(sub) }}</ui-badge>
                </template>

                <template #cell-metadata="{ row: sub }">
                    <span v-for="(value, key) in (sub.metadata || {})" :key="key" class="block text-2xs">
                        {{ key }}: {{ value }}
                    </span>
                </template>

                <template #prepended-row-actions="{ row: sub }">
                    <template v-if="sub.status === 'active' && canCancel">
                        <ui-dropdown-item
                            v-if="sub.paused"
                            :text="__('Resume')"
                            @click="promptAction(sub.resume_url, 'Resume this subscription?', { method: 'PUT' })"
                            icon="padlock-unlocked"
                        />
                        <ui-dropdown-item
                            v-else
                            :text="__('Pause')"
                            @click="promptAction(sub.pause_url, 'Pause this subscription?', { method: 'PUT' })"
                            icon="padlock-locked"
                        />
                        <ui-dropdown-item
                            :text="__('Cancel')"
                            @click="promptAction(sub.cancel_url, 'Cancel this subscription? This cannot be undone.', { danger: true, method: 'DELETE' })"
                            icon="trash"
                            variant="destructive"
                        />
                    </template>
                </template>
            </ui-listing>
        </div>

        <div v-if="donations.length">
            <ui-heading size="lg" :text="__('Single Donations')" class="mb-3" />
            <ui-listing
                :items="donations"
                :columns="donationColumns"
                sort-column="date"
                sort-direction="desc"
                :allow-search="false"
                :allow-customizing-columns="false"
            >
                <template #cell-amount="{ row: donation }">
                    {{ donation.currency.toUpperCase() }} {{ Number(donation.amount).toFixed(2) }}
                </template>

                <template #cell-status="{ row: donation }">
                    <ui-badge :variant="statusVariant(donation)">{{ statusLabel(donation) }}</ui-badge>
                </template>

                <template #cell-metadata="{ row: donation }">
                    <span v-for="(value, key) in (donation.metadata || {})" :key="key" class="block text-2xs">
                        {{ key }}: {{ value }}
                    </span>
                </template>

                <template #prepended-row-actions="{ row: donation }">
                    <ui-dropdown-item
                        v-if="donation.status === 'succeeded' && canRefund"
                        :text="__('Refund')"
                        @click="promptAction(donation.refund_url, 'Refund this payment? This cannot be undone.', { danger: true })"
                        icon="undo"
                        variant="destructive"
                    />
                </template>
            </ui-listing>
        </div>

        <ui-confirmation-modal
            :open="showConfirm"
            :title="__('Confirm')"
            :body-text="confirmMessage"
            :button-text="__('Confirm')"
            :danger="confirmDanger"
            @confirm="executeAction"
            @cancel="showConfirm = false"
        />
    </div>
</template>
