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

    public $actionUrl;

    public $actionText;

    public function __construct($name, $endDateTh, $daysLeftText, $actionUrl = null, $actionText = 'เข้าสู่รายการประเมิน')
    {
        $this->name = $name;
        $this->endDateTh = $endDateTh;
        $this->daysLeftText = $daysLeftText;
        $this->actionUrl = $actionUrl;
        $this->actionText = $actionText;
    }

    public function build()
    {
        return $this->subject('แจ้งเตือนวันสิ้นสุดการประเมินใกล้ถึงกำหนด')
            ->view('emails.notify_enddate');
    }
}
