@extends('pdf.layouts.document')

@section('content')
    <h2 style="font-size: 14px; margin: 0 0 12px 0; color: #1e3a8a;">Certificate of service completion</h2>
    <p style="margin-bottom: 14px;">
        This certifies that the service identified below has been processed and marked as <strong>completed</strong> by the issuing government office.
    </p>

    <div class="section-title">Record</div>
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
            <td class="label">Office</td>
            <td>{{ $request->governmentOffice?->name ?? '—' }}</td>
        </tr>
        @if($request->governmentOffice?->address)
            <tr>
                <td class="label">Address</td>
                <td>{{ $request->governmentOffice->address }}</td>
            </tr>
        @endif
        <tr>
            <td class="label">Applicant</td>
            <td>{{ $request->citizen?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Completed at</td>
            <td>{{ $request->completed_at?->format('Y-m-d H:i') ?? $issuedAt->format('Y-m-d H:i') }}</td>
        </tr>
    </table>

    <div class="seal">
        This certificate is issued for administrative and citizen records. Present your reference code for inquiries.<br>
        Generated automatically when the request status was set to completed.
    </div>
@endsection
