<?php

namespace Ghijk\DonationCheckout\Widgets;

use Statamic\Facades\User;
use Statamic\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Ghijk\DonationCheckout\Actions\ListDonations;
use Ghijk\DonationCheckout\Actions\ListSubscriptions;

class DonationStatsWidget extends Widget
{
    public function html()
    {
        if (! auth()->user()->can('view donations')) {
            return '';
        }

        $cacheTtl = $this->config('cache_ttl', 300);

        $stats = Cache::remember('donation-checkout:widget-stats', $cacheTtl, function () {
            return $this->fetchStats();
        });

        return view('donation-checkout::widgets.donation-stats', $stats)->render();
    }

    private function fetchStats(): array
    {
        $listDonations = app(ListDonations::class);
        $listSubscriptions = app(ListSubscriptions::class);

        $totalRaised = 0;
        $activeSubscriptions = 0;
        $recentDonations = collect();

        $users = User::all()->filter(fn ($user) => $user->get('stripe_customer_id'));

        foreach ($users as $user) {
            $customerId = $user->get('stripe_customer_id');

            try {
                $donations = $listDonations($customerId, 5);

                foreach ($donations->data as $donation) {
                    if ($donation->status === 'succeeded') {
                        $totalRaised += $donation->amount;
                        $recentDonations->push([
                            'amount' => $donation->amount / 100,
                            'currency' => $donation->currency,
                            'email' => $user->email(),
                            'date' => date('Y-m-d H:i', $donation->created),
                        ]);
                    }
                }

                $subscriptions = $listSubscriptions($customerId, 100);

                foreach ($subscriptions->data as $subscription) {
                    if ($subscription->status === 'active') {
                        $activeSubscriptions++;
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return [
            'totalRaised' => $totalRaised / 100,
            'activeSubscriptions' => $activeSubscriptions,
            'recentDonations' => $recentDonations->sortByDesc('date')->take(5)->values(),
            'currency' => config('donation-checkout.stripe_currency', 'gbp'),
        ];
    }
}
