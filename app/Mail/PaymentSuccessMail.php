<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Invoice $invoice)
    {
    }

    public function build()
    {
        $amount = number_format((float) ($this->invoice->grand_total ?? $this->invoice->total ?? 0), 2);
        $invoiceNumber = e((string) $this->invoice->invoice_number);

        return $this->subject("Payment Successful - Invoice {$this->invoice->invoice_number}")
            ->html(
                "<div style=\"font-family:Arial,sans-serif;background:#f8fafc;padding:24px;\">".
                "<div style=\"max-width:560px;margin:0 auto;background:#ffffff;border-radius:12px;padding:24px;border:1px solid #e2e8f0;\">".
                "<h2 style=\"margin:0 0 12px;color:#0f172a;\">Payment Successful</h2>".
                "<p style=\"margin:0 0 10px;color:#334155;\"><strong>Invoice Number:</strong> {$invoiceNumber}</p>".
                "<p style=\"margin:0 0 10px;color:#334155;\"><strong>Amount:</strong> INR {$amount}</p>".
                "<p style=\"margin:0;color:#334155;\"><strong>Status:</strong> Paid</p>".
                "</div></div>"
            );
    }
}
