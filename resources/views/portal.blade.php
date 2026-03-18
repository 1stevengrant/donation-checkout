<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Donor Portal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f3f4f6;
            color: #111827;
            line-height: 1.5;
        }
        .portal {
            max-width: 48rem;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        .portal-header {
            margin-bottom: 2rem;
        }
        .portal-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        .portal-header p {
            color: #6b7280;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .portal-section {
            margin-bottom: 2rem;
        }
        .portal-section h2 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        .portal-card {
            background: #fff;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }
        .portal-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .portal-row:last-child {
            border-bottom: none;
        }
        .portal-row-main {
            flex: 1;
        }
        .portal-amount {
            font-size: 1.125rem;
            font-weight: 600;
        }
        .portal-date {
            font-size: 0.8125rem;
            color: #6b7280;
        }
        .portal-meta {
            font-size: 0.8125rem;
            color: #6b7280;
        }
        .portal-badge {
            display: inline-block;
            padding: 0.125rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .portal-badge--active {
            background: #dcfce7;
            color: #166534;
        }
        .portal-badge--paused {
            background: #fef3c7;
            color: #92400e;
        }
        .portal-badge--canceled {
            background: #fee2e2;
            color: #991b1b;
        }
        .portal-badge--succeeded {
            background: #dcfce7;
            color: #166534;
        }
        .portal-badge--refunded {
            background: #fee2e2;
            color: #991b1b;
        }
        .portal-badge--default {
            background: #f3f4f6;
            color: #374151;
        }
        .portal-cancel-btn {
            padding: 0.375rem 0.875rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            background: #fff;
            color: #dc2626;
            font-size: 0.8125rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.15s, border-color 0.15s;
        }
        .portal-cancel-btn:hover {
            background: #fef2f2;
            border-color: #fecaca;
        }
        .portal-empty {
            text-align: center;
            padding: 2rem;
            color: #9ca3af;
            font-size: 0.875rem;
        }
        .portal-back {
            display: inline-block;
            margin-top: 1rem;
            font-size: 0.875rem;
            color: #4f46e5;
            text-decoration: none;
        }
        .portal-back:hover {
            color: #4338ca;
        }
    </style>
</head>
<body>
    <div class="portal">
        <div class="portal-header">
            <h1>Your Donations</h1>
            <p>Welcome back, {{ $name }}</p>
        </div>

        @if($subscriptions->isNotEmpty())
            <div class="portal-section">
                <h2>Recurring Donations</h2>
                <div class="portal-card">
                    @foreach($subscriptions as $sub)
                        <div class="portal-row">
                            <div class="portal-row-main">
                                <span class="portal-amount">{{ strtoupper($sub['currency']) }} {{ number_format($sub['amount'], 2) }}/mo</span>
                                @if($sub['paused'])
                                    <span class="portal-badge portal-badge--paused">Paused</span>
                                @elseif($sub['status'] === 'active')
                                    <span class="portal-badge portal-badge--active">Active</span>
                                @elseif($sub['status'] === 'canceled')
                                    <span class="portal-badge portal-badge--canceled">Cancelled</span>
                                @else
                                    <span class="portal-badge portal-badge--default">{{ ucfirst($sub['status']) }}</span>
                                @endif
                                <br>
                                <span class="portal-meta">
                                    Started {{ $sub['date'] }}
                                    @if($sub['status'] === 'active' && !$sub['paused'])
                                        &middot; Next payment {{ $sub['current_period_end'] }}
                                    @endif
                                </span>
                            </div>
                            @if($sub['can_cancel'] && $sub['status'] === 'active')
                                <form method="POST" action="{{ $sub['cancel_url'] }}" onsubmit="return confirm('Cancel this subscription? This cannot be undone.')">
                                    @csrf
                                    <button type="submit" class="portal-cancel-btn">Cancel</button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($donations->isNotEmpty())
            <div class="portal-section">
                <h2>Single Donations</h2>
                <div class="portal-card">
                    @foreach($donations as $donation)
                        <div class="portal-row">
                            <div class="portal-row-main">
                                <span class="portal-amount">{{ strtoupper($donation['currency']) }} {{ number_format($donation['amount'], 2) }}</span>
                                @if($donation['status'] === 'succeeded')
                                    <span class="portal-badge portal-badge--succeeded">Succeeded</span>
                                @elseif($donation['status'] === 'refunded')
                                    <span class="portal-badge portal-badge--refunded">Refunded</span>
                                @else
                                    <span class="portal-badge portal-badge--default">{{ ucfirst($donation['status']) }}</span>
                                @endif
                                <br>
                                <span class="portal-date">{{ $donation['date'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($donations->isEmpty() && $subscriptions->isEmpty())
            <div class="portal-card">
                <div class="portal-empty">No donations found.</div>
            </div>
        @endif

        <a href="{{ url('/') }}" class="portal-back">&larr; Back to site</a>
    </div>
</body>
</html>
