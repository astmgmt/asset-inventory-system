<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserUpdatedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $changes;

    public function __construct($user, $changes)
    {
        $this->user = $user;
        $this->changes = $changes;
    }

    public function build()
    {
        return $this->subject('Your Account Has Been Updated')
                    ->markdown('emails.user-updated');
    }
}