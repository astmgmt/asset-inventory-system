<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\User;
use App\Models\Notification;
use App\Models\NotificationType;
use App\Services\SendEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.app')]
class ContactAdmins extends Component
{
    public $subject = '';
    public $message = '';

    public $successMessage = '';
    public $errorMessage = '';

    protected $rules = [
        'subject' => 'required|min:5|max:255',
        'message' => 'required|min:10',
    ];

    protected $messages = [
        'subject.required' => 'The subject field is required.',
        'subject.min' => 'The subject must be at least 5 characters.',
        'message.required' => 'The message field is required.',
        'message.min' => 'The message must be at least 10 characters.',
    ];

    public function submit()
    {
        $this->validate();
        $this->resetMessages();

        try {
            $superAdmins = User::whereHas('role', fn($q) => $q->where('name', 'Super Admin'))->get();
            $admins = User::whereHas('role', fn($q) => $q->where('name', 'Admin'))->get();

            if ($superAdmins->isEmpty()) {
                throw new \Exception('No Super Admin found to receive the message');
            }

            $notificationType = NotificationType::firstOrCreate([
                'type_name' => 'email_notification'
            ]);

            $notification = Notification::create([
                'type_id' => $notificationType->id,
                'message' => 'New contact form: ' . $this->subject,
                'email_alert' => true
            ]);

            $allAdmins = $superAdmins->merge($admins);
            $allAdmins->each(function($user) use ($notification) {
                $user->notifications()->attach($notification->id, [
                    'is_read' => false,
                    'notified_at' => now()
                ]);
            });

            $primaryRecipient = $superAdmins->first()->email;
            $ccList = $superAdmins->skip(1)->pluck('email')
                        ->merge($admins->pluck('email'))
                        ->unique()
                        ->toArray();

            $user = Auth::user();

            $emailContent = [
                'emails.contact-admins',
                [
                    'senderName' => $user->name,  
                    'senderEmail' => $user->email, 
                    'subject' => $this->subject,
                    'messageContent' => $this->message
                ]
            ];

            $emailService = new SendEmail();
            $sent = $emailService->send(
                $primaryRecipient,
                "Contact Form: " . $this->subject,
                $emailContent,
                $ccList,
                null,
                null,
                false
            );

            if (!$sent) {
                throw new \Exception("Failed to send email to {$primaryRecipient}");
            }

            $this->successMessage = 'Your message has been sent successfully!';
            $this->reset(['subject', 'message']);

            $this->dispatch('refreshNotifications');

        } catch (\Throwable $e) {
            Log::error("Contact form submission failed: " . $e->getMessage());
            $this->errorMessage = 'Failed to send your message. Please try again later.';
        }
    }

    private function resetMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function render()
    {
        return view('livewire.user.contact-admins');            
    }
}