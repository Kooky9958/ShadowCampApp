<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Invite extends Mailable
{
    use Queueable, SerializesModels;
    public $product;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($product, $subject)
    {
        $this->product = $product;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mailable = $this->subject($this->subject);

        $mailable->view("emails/invite-{$this->product}-html");

        return $mailable;
    }
}
