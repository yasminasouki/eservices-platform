@extends('pdf.layouts.document')

@section('content')
    <h2 style="font-size: 14px; margin: 0 0 12px 0; color: #7c2d12;">Receipt / service fee reference</h2>
    <p style="margin-bottom: 14px;">
        This receipt summarizes the applicable fee reference for the completed service request. If an online payment was recorded, details appear below; otherwise the published service tariff is shown.
    </p>

    <div class="section-title">Request</div>
    <table class="facts">
        <tr>
            <td class="label">Request ID</td>
            <td>#{{ $request->id }}</td>
        </tr>
        <tr>
            <td class="label">Reference</td>
            <td>{{ $request->qr_code }}</td>
        </tr>
        <tr>
            <td class="label">Service</td>
            <td>{{ $request->service?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Office</td>
            <td>{{ $request->governmentOffice?->name ?? '—' }}</td>
        </tr>
    </table>

    <div class="section-title">Payment details</div>
    <table class="facts">
        @if($payment && $payment->status === 'completed')
            <tr>
                <td class="label">Amount paid</td>
                <td>{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</td>
            </tr>
            <tr>
                <td class="label">Method</td>
                <td>{{ str($payment->method)->replace('_', ' ')->title() }}</td>
            </tr>
            @if($payment->transaction_id)
                <tr>
                    <td class="label">Transaction ID</td>
                    <td>{{ $payment->transaction_id }}</td>
                </tr>
            @endif
            <tr>
                <td class="label">Paid at</td>
                <td>{{ $payment->paid_at?->format('Y-m-d H:i') ?? '—' }}</td>
            </tr>
        @elseif($payment)
            <tr>
                <td class="label">Payment status</td>
                <td>{{ str($payment->status)->title() }} — amount {{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</td>
            </tr>
        @else
            <tr>
                <td class="label">Published fee (reference)</td>
                <td>
                    @if($request->service && $request->service->price !== null)
                        {{ number_format((float) $request->service->price, 2) }}
                        (service tariff; no payment transaction on file)
                    @else
                        Not specified — contact the office for fee confirmation.
                    @endif
                </td>
            </tr>
        @endif
    </table>

    <div class="seal">
        This document is for your records only and does not replace an official fiscal invoice where required by law.
    </div>
@endsection
