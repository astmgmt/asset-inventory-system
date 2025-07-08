<?php

namespace App\Livewire\Contact;

use Livewire\Component;
use App\Models\User;
use App\Services\SendEmail;
use Illuminate\Support\Facades\Log;

class ContactUs extends Component
{
    public $name = '';
    public $email = ''; 
    public $subject = '';
    public $message = '';

    public $success = false;
    public $successMessage = '';

    public $error = false;
    public $errorMessage = '';

    protected $rules = [
        'name' => 'required|min:3|max:255', 
        'email' => 'required|email',
        'subject' => 'required|min:5|max:255',
        'message' => 'required|min:10',
    ];

    protected $messages = [
        'name.required' => 'The name field is required.', 
        'name.min' => 'The name must be at least 3 characters.', 
        'email.required' => 'The email field is required.',
        'email.email' => 'Please enter a valid email address.', 
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

            $regularNotificationType = \App\Models\NotificationType::firstOrCreate([
                'type_name' => 'email_notification'
            ]);
            
            $superAdminNotificationType = \App\Models\NotificationType::firstOrCreate([
                'type_name' => 'super_admin_email_notification'
            ]);

            $regularNotification = \App\Models\Notification::create([
                'type_id' => $regularNotificationType->id,
                'message' => 'You have a new email message',
                'email_alert' => true
            ]);

            $superAdminNotification = \App\Models\Notification::create([
                'type_id' => $superAdminNotificationType->id,
                'message' => 'Important: New contact form submission (Super Admin attention required)',
                'email_alert' => true
            ]);

            $admins->each(function($user) use ($regularNotification) {
                $user->notifications()->attach($regularNotification->id, [
                    'is_read' => false,
                    'notified_at' => now()
                ]);
            });

            $superAdmins->each(function($user) use ($regularNotification, $superAdminNotification) {
                $user->notifications()->attach($regularNotification->id, [
                    'is_read' => false,
                    'notified_at' => now()
                ]);
                $user->notifications()->attach($superAdminNotification->id, [
                    'is_read' => false,
                    'notified_at' => now()
                ]);
            });

            $primaryRecipient = $superAdmins->first()->email;
            $ccList = $superAdmins->skip(1)->pluck('email')
                        ->merge($admins->pluck('email'))
                        ->unique()
                        ->toArray();

            $emailContent = [
                'emails.contact-admins',
                [
                    'senderName' => $this->name, 
                    'senderEmail' => $this->email, 
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

            session()->flash('successMessage', 'Your message has been sent successfully!');
            return redirect()->route('contact');

        } catch (\Throwable $e) {
            Log::error("Contact form submission failed: " . $e->getMessage());
            $this->error = true;
            $this->errorMessage = 'Failed to send your message. Please try again later.';
        }
    }


    private function resetMessages()
    {
        $this->success = false;
        $this->successMessage = '';
        $this->error = false;
        $this->errorMessage = '';
    }

    public function render()
    {
        return view('livewire.contact.contact-us')
            ->layout('components.layouts.guest');
    }
}
