import DonationsIndex from './pages/DonationsIndex.vue';
import DonorProfile from './pages/DonorProfile.vue';

Statamic.booting(() => {
    Statamic.$inertia.register('DonationCheckout/DonationsIndex', DonationsIndex);
    Statamic.$inertia.register('DonationCheckout/DonorProfile', DonorProfile);
});
