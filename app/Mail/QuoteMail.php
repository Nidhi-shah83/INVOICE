<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuoteMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Quote $quote,
        private readonly ?string $pdfContent = null,
    ) {
    }

    public function build(): self
    {
        $approveUrl = url('/quote/approve/'.$this->quote->id.'/'.$this->quote->approval_token);

        $mail = $this->subject("Quote {$this->quote->quote_number}")
            ->view('emails.quote')
            ->with([
                'quote' => $this->quote,
                'approveUrl' => $approveUrl,
            ]);

        if ($this->pdfContent !== null) {
            $mail->attachData($this->pdfContent, "{$this->quote->quote_number}.pdf", [
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
