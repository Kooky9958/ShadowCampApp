<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Mail\Mailables\Content;

class Generic extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $body_html, $body_text=null) {
        $this->subject = $subject;
        $this->body_html = $body_html;
        $this->body_text = $body_text;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        $mailable = $this->subject($this->subject);

        if(isset($this->body_html) && $this->body_html != null)
            $mailable->view('emails/generic-html', ['body_html' => $this->body_html]);

        if(isset($this->body_text) && $this->body_text != null)
            $mailable->text('emails/generic-text', ['body' => $this->body_text]);

        return $mailable;
    }
                    
}
