<div class="p-4">
    <div class="flex gap-6 mb-4">
        <div>
            <p class="text-gray-500 dark:text-gray-600 text-sm">Total Raised</p>
            <p class="text-2xl font-bold">{{ strtoupper($currency) }} {{ number_format($totalRaised, 2) }}</p>
        </div>
        <div>
            <p class="text-gray-500 dark:text-gray-600 text-sm">Active Subscriptions</p>
            <p class="text-2xl font-bold">{{ $activeSubscriptions }}</p>
        </div>
    </div>

    @if($recentDonations->isNotEmpty())
        <h3 class="font-bold text-sm mb-2">Recent Donations</h3>
        <table class="text-sm w-full">
            @foreach($recentDonations as $donation)
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <td class="py-1">{{ $donation['email'] }}</td>
                    <td class="py-1">{{ strtoupper($donation['currency']) }} {{ number_format($donation['amount'], 2) }}</td>
                    <td class="py-1 text-gray-500 dark:text-gray-600">{{ $donation['date'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <a href="{{ cp_route('donation-checkout.index') }}" class="text-blue-600 hover:text-blue-800 text-sm mt-4 block">View all donations &rarr;</a>
</div>
