<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ApprovalEmailService
{
    /**
     * Send approval request emails to all configured approvers.
     * Tracks send status per approver in the emails array.
     *
     * @param array $quotation  Quote data (quoteNo, customer, revenue, cost, profit, margin)
     * @param array $approvers  Each: [userId, name, email]
     * @param string $pdfPath   Relative path under storage/app/attachments
     * @return array            Per-approver send results
     */
    public function sendApprovalEmails(array $quotation, array $approvers, string $pdfPath): array
    {
        $results = [];

        foreach ($approvers as $approver) {
            $results[] = $this->sendToApprover($quotation, $approver, $pdfPath);
        }

        return $results;
    }

    /**
     * Send approval email to a single approver.
     * Catches and logs failures without bubbling — ensures one failed
     * send does not block the remaining approvers.
     */
    private function sendToApprover(array $quotation, array $approver, string $pdfPath): array
    {
        $approveUrl = route('quotation.approve.page', [
            'id' => $quotation['quotationId'],
            'action' => 'approve',
            'approver' => $approver['userId'],
        ]);

        $rejectUrl = route('quotation.approve.page', [
            'id' => $quotation['quotationId'],
            'action' => 'reject',
            'approver' => $approver['userId'],
        ]);

        try {
            $attachmentFullPath = Storage::path('attachments/' . $pdfPath);

            Mail::send(
                'emails.approval-request',
                [
                    'approverName' => $approver['name'],
                    'quotation' => $quotation,
                    'approveUrl' => $approveUrl,
                    'rejectUrl' => $rejectUrl,
                ],
                function ($message) use ($approver, $quotation, $attachmentFullPath) {
                    $message
                        ->to($approver['email'], $approver['name'])
                        ->subject('Approval Required — Quotation ' . $quotation['quoteNo']);

                    // Attach PDF only if it exists — non-fatal if missing
                    if (file_exists($attachmentFullPath)) {
                        $message->attach($attachmentFullPath, [
                            'as' => 'Quotation_' . $quotation['quoteNo'] . '.pdf',
                            'mime' => 'application/pdf',
                        ]);
                    } else {
                        Log::warning('ApprovalEmailService: PDF not found', [
                            'path' => $attachmentFullPath,
                            'quoteNo' => $quotation['quoteNo'],
                            'approver' => $approver['email'],
                        ]);
                    }
                },
            );

            Log::info('ApprovalEmailService: email sent', [
                'quoteNo' => $quotation['quoteNo'],
                'approver' => $approver['email'],
            ]);

            return [
                'userId' => $approver['userId'],
                'email' => $approver['email'],
                'status' => 'Sent',
                'error' => null,
            ];
        } catch (\Exception $exception) {
            Log::error('ApprovalEmailService: email failed', [
                'quoteNo' => $quotation['quoteNo'],
                'approver' => $approver['email'],
                'error' => $exception->getMessage(),
            ]);

            return [
                'userId' => $approver['userId'],
                'email' => $approver['email'],
                'status' => 'Failed',
                'error' => $exception->getMessage(),
            ];
        }
    }
}
