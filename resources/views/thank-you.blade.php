<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $heading }}</title>
</head>
<body>
    <div style="max-width: 600px; margin: 4rem auto; padding: 2rem; text-align: center; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
        <h1>{{ $heading }}</h1>
        <p style="font-size: 1.25rem; color: #374151;">
            {{ strtoupper($currency) }} {{ number_format($amount, 2) }}
            @if($is_recurring)
                per month
            @endif
        </p>
        <p style="color: #6b7280; margin: 1.5rem 0;">{{ $message }}</p>
        @if($cta_url && $cta_text)
            <a href="{{ $cta_url }}" style="display: inline-block; padding: 0.75rem 2rem; background: #4f46e5; color: #fff; text-decoration: none; border-radius: 0.5rem; font-weight: 600;">
                {{ $cta_text }}
            </a>
        @endif
    </div>
</body>
</html>
