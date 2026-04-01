<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class PaymentConfirmedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Invoice $invoice, public Payment $payment)
    {
    }

    public function build()
    {
        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $this->invoice]);

        return $this->subject("Payment Received — {$this->invoice->invoice_number}")
            ->view('emails.payment_confirmed')
            ->with([
                'invoice' => $this->invoice,
                'payment' => $this->payment,
                'businessName' => Config::get('invoice.business_name'),
            ])
            ->attachData($pdf->output(), "{$this->invoice->invoice_number}-receipt.pdf");
    }
}
