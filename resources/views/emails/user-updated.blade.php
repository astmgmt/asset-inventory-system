@component('mail::message')
{{-- Optional logo or banner --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" style="height: 50px;">
@endcomponent
@endslot

# 🔔 Account Information Updated

Hello **{{ $user->name }}**,

We wanted to let you know that your account information has been updated by an administrator. Here is a summary of the changes:

<div style="margin-top: 24px; margin-bottom: 24px;">
    <table class="table-auto w-full text-sm border-collapse" style="width: 100%; margin: 0 auto; font-size: 14px; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center; font-weight: 600;">Field</th>
                <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center; font-weight: 600;">Old Value</th>
                <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center; font-weight: 600;">New Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($changes as $field => $change)
            <tr>
                <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center; color: #1f2937; font-weight: 500;">
                    {{ ucfirst($field) }}
                </td>
                <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center; color: #4b5563;">
                    {{ $change['old'] }}
                </td>
                <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center; color: #111827;">
                    {{ $change['new'] }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<p class="text-sm text-gray-700" style="font-size: 14px; color: #374151; text-align: center;">
    If you did not request these changes, please contact our support team immediately.
    @if(!empty($supportUrl))
        <br>
        <a href="{{ $supportUrl }}" class="text-blue-600 underline" style="color: #2563eb; text-decoration: underline;">Contact Support</a>
    @endif
</p>

Thanks,  
**{{ config('app.name') }} Team**

@slot('footer')
@component('mail::footer')
&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
@endcomponent
@endslot
@endcomponent
