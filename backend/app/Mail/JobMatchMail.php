<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JobMatchMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $jobSubject,
        public string $messageBody,
        public string $coverLetterText,
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->jobSubject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.job_match',
            with: [
                'body' => $this->messageBody,
                'coverLetter' => $this->coverLetterText,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
