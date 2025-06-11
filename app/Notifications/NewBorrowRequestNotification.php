<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\AssetBorrowTransaction;

class NewBorrowRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $transaction;

    public function __construct(AssetBorrowTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Borrow Request: ' . $this->transaction->borrow_code)
            ->line('A new borrow request has been submitted by ' . $this->transaction->user->name)
            ->line('**Remarks:** ' . ($this->transaction->remarks ?: 'No remarks provided'))
            ->action('View Request', url('/admin/borrow-requests/' . $this->transaction->id))
            ->line('Thank you for using our application!');
    }
}