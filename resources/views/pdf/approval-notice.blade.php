@extends('pdf.layouts.document')

@section('content')
    <h2 style="font-size: 14px; margin: 0 0 12px 0; color: #14532d;">Official approval notice</h2>
    <p style="margin-bottom: 14px;">
        The undersigned office confirms that the following service request has been <strong>approved</strong> and may proceed according to published procedures.
    </p>

    <div class="section-title">Request summary</div>
    <table class="facts">
        <tr>
            <td class="label">Request ID</td>
            <td>#{{ $request->id }}</td>
        </tr>
        <tr>
            <td class="label">Public reference (QR)</td>
            <td>{{ $request->qr_code }}</td>
        </tr>
        <tr>
            <td class="label">Service</td>
            <td>{{ $request->service?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Issuing office</td>
            <td>{{ $request->governmentOffice?->name ?? '—' }}</td>
        </tr>
        @if($request->governmentOffice?->address)
            <tr>
                <td class="label">Office address</td>
                <td>{{ $request->governmentOffice->address }}</td>
            </tr>
        @endif
        <tr>
            <td class="label">Applicant (internal reference)</td>
            <td>{{ $request->citizen?->name ?? 'Citizen on file' }}</td>
        </tr>
        <tr>
            <td class="label">Submitted</td>
            <td>{{ $request->submitted_at?->format('Y-m-d H:i') ?? $request->created_at?->format('Y-m-d H:i') }}</td>
        </tr>
    </table>

    <div class="seal">
        Status at issuance: <strong>Approved</strong><br>
        Generated automatically by {{ config('app.name') }} when status was set to approved.
    </div>
@endsection
