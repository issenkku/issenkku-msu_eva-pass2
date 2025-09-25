<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyEndDateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $endDateTh;
    public $daysLeftText;

    public function __construct($name, $endDateTh, $daysLeftText)
    {
        $this->name = $name;
        $this->endDateTh = $endDateTh;
        $this->daysLeftText = $daysLeftText;
    }

    public function build()
    {
        return $this->subject('แจ้งเตือนวันสิ้นสุดการประเมินใกล้ถึงกำหนด')
            ->view('emails.notify_enddate');
    }
}
