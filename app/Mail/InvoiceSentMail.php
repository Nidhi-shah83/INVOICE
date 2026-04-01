<?php

namespace App\Mail;

use App\Models\Invoice;
use Barryvdh\DomPDF\PDF;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class InvoiceSentMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string $paymentLink,
        public PDF $pdf
    ) {
    }

    public function build()
    {
        return $this->subject("Invoice {$this->invoice->invoice_number}")
            ->view('emails.invoice_sent')
            ->with([
                'invoice' => $this->invoice,
                'paymentLink' => $this->paymentLink,
                'businessName' => Config::get('invoice.business_name'),
            ])
            ->attachData($this->pdf->output(), "{$this->invoice->invoice_number}.pdf");
    }
}
