<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CareerApplyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $image_url = public_path('img/MIG-logo.png');
        return $this->subject($this->data->subject)->view('careers.career_apply')->with(['image_url' => $image_url, 'message' => $this]);
        
        // return $this->subject($this->data['subject'])->view('emails.forgot_password')->with(['image_url' => $image_url]);
        // return $this->subject($this->data['subject'])->markdown('emails.forgot_password')->with(['image_url' => $image_url, 'message' => $this]);
    }
}
