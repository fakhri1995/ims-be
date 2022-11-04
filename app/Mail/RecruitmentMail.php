<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecruitmentMail extends Mailable //implements ShouldQueue
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
        if($this->data->attachment === NULL){
            return $this->subject($this->data->subject)
                ->view('emails.recruitment')
                ->with(['image_url' => $image_url, 'message' => $this]);
        }

        // dd(base64_decode($this->data->attachment));
        // $attachment = base64_encode($this->data->attachment);
        // dd(file_get_contents(base64_decode($this->data->attachment)));
        // return $this->subject($this->data->subject)
        //     ->view('emails.recruitment')
        //     ->with(['image_url' => $image_url, 'message' => $this])
        //     ->attachData(file_get_contents(base64_decode($this->data->attachment)), "file.xlsx", [
        //         "mime" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        //     ]);

        return $this->subject($this->data->subject)
            ->view('emails.recruitment')
            ->with(['image_url' => $image_url, 'message' => $this])
            ->attach($this->data->attachment->getRealPath(),[
                'as' => $this->data->attachment->getClientOriginalName()
            ]);
    }
}
