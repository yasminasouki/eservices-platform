<?php

namespace App\Services;

use App\Models\Document;
use App\Models\ServiceRequest;
use App\Notifications\OfficeAddedDocumentNotification;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ServiceRequestPdfAutomationService
{
    public const DESC_APPROVAL = '[AUTO_PDF:approval] Official approval notice';

    public const DESC_COMPLETION = '[AUTO_PDF:completion] Service completion certificate';

    public const DESC_RECEIPT = '[AUTO_PDF:receipt] Payment / service fee receipt';

    /**
     * Generate an approval notice when the office sets status to approved.
     */
    public function generateApprovalNotice(ServiceRequest $serviceRequest, ?int $generatedByUserId): void
    {
        $serviceRequest->loadMissing(['service', 'governmentOffice', 'citizen']);

        $this->removePreviousAutomated($serviceRequest, self::DESC_APPROVAL);

        $html = view('pdf.approval-notice', [
            'request' => $serviceRequest,
            'issuedAt' => now(),
            'title' => 'Approval notice',
        ])->render();

        $this->persistPdf(
            $serviceRequest,
            $html,
            'approval-notice-'.$serviceRequest->id.'-'.now()->format('YmdHis').'.pdf',
            'generated',
            self::DESC_APPROVAL,
            $generatedByUserId
        );
    }

    /**
     * Generate a completion certificate when the office completes the request.
     */
    public function generateCompletionCertificate(ServiceRequest $serviceRequest, ?int $generatedByUserId): void
    {
        $serviceRequest->loadMissing(['service', 'governmentOffice', 'citizen']);

        $this->removePreviousAutomated($serviceRequest, self::DESC_COMPLETION);

        $html = view('pdf.completion-certificate', [
            'request' => $serviceRequest,
            'issuedAt' => now(),
            'title' => 'Completion certificate',
        ])->render();

        $this->persistPdf(
            $serviceRequest,
            $html,
            'completion-certificate-'.$serviceRequest->id.'-'.now()->format('YmdHis').'.pdf',
            'certificate',
            self::DESC_COMPLETION,
            $generatedByUserId
        );
    }

    /**
     * Generate a receipt (uses completed payment if present; otherwise published service price as reference).
     */
    public function generateReceipt(ServiceRequest $serviceRequest, ?int $generatedByUserId): void
    {
        $serviceRequest->loadMissing(['service', 'governmentOffice', 'citizen', 'payment']);

        $this->removePreviousAutomated($serviceRequest, self::DESC_RECEIPT);

        $payment = $serviceRequest->payment;

        $html = view('pdf.payment-receipt', [
            'request' => $serviceRequest,
            'payment' => $payment,
            'issuedAt' => now(),
            'title' => 'Receipt',
        ])->render();

        $this->persistPdf(
            $serviceRequest,
            $html,
            'receipt-'.$serviceRequest->id.'-'.now()->format('YmdHis').'.pdf',
            'receipt',
            self::DESC_RECEIPT,
            $generatedByUserId
        );
    }

    private function removePreviousAutomated(ServiceRequest $serviceRequest, string $descriptionPrefix): void
    {
        Document::query()
            ->where('service_request_id', $serviceRequest->id)
            ->where('description', $descriptionPrefix)
            ->get()
            ->each(function (Document $document) {
                Storage::disk('public')->delete($document->file_path);
                $document->delete();
            });
    }

    private function persistPdf(
        ServiceRequest $serviceRequest,
        string $html,
        string $fileName,
        string $type,
        string $description,
        ?int $generatedByUserId,
    ): void {
        $pdfBinary = $this->renderPdf($html);

        $relativePath = 'service-requests/'.$serviceRequest->id.'/'.'auto-'.$fileName;
        Storage::disk('public')->put($relativePath, $pdfBinary);

        Document::create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $generatedByUserId,
            'file_path' => $relativePath,
            'file_name' => $fileName,
            'mime_type' => 'application/pdf',
            'file_size' => strlen($pdfBinary),
            'description' => $description,
            'type' => $type,
            'uploaded_by' => 'office',
        ]);
    }

    private function notifyCitizenOfNewDocuments(ServiceRequest $serviceRequest): void
    {
        $serviceRequest->loadMissing('citizen', 'governmentOffice');
        if ($serviceRequest->citizen && $serviceRequest->governmentOffice) {
            $serviceRequest->citizen->notify(new OfficeAddedDocumentNotification(
                $serviceRequest,
                $serviceRequest->governmentOffice,
            ));
        }
    }

    private function renderPdf(string $html): string
    {
        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Run all automation hooks for a status transition (never throws — logs failures).
     */
    public function onStatusChanged(
        ServiceRequest $serviceRequest,
        string $oldStatus,
        string $newStatus,
        ?int $officerId,
    ): void {
        if ($oldStatus === $newStatus) {
            return;
        }

        try {
            if ($newStatus === 'approved') {
                $this->generateApprovalNotice($serviceRequest, $officerId);
                $this->notifyCitizenOfNewDocuments($serviceRequest);
            }
            if ($newStatus === 'completed') {
                $this->generateCompletionCertificate($serviceRequest, $officerId);
                $this->generateReceipt($serviceRequest, $officerId);
                $this->notifyCitizenOfNewDocuments($serviceRequest);
            }
        } catch (Throwable $e) {
            Log::error('Automated PDF generation failed.', [
                'service_request_id' => $serviceRequest->id,
                'from' => $oldStatus,
                'to' => $newStatus,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
