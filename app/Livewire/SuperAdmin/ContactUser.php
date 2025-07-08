<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use App\Models\User;
use App\Services\SendEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use App\Models\NotificationType;
use Illuminate\Support\Str;

class ContactUser extends Component
{
    public $search = '';
    public $recipients = [];
    public $subject = '';
    public $message = '';
    
    public $successMessage = '';
    public $errorMessage = '';
    
    public $searchResults = [];

    protected $rules = [
        'recipients' => 'required|array|min:1|max:5',
        'subject' => 'required|min:5|max:255',
        'message' => 'required|min:10',
    ];

    protected $messages = [
        'recipients.required' => 'Please select at least one recipient.',
        'recipients.min' => 'Please select at least one recipient.',
        'recipients.max' => 'You can only send email up to 5 addresses only!',
        'subject.required' => 'The subject field is required.',
        'subject.min' => 'The subject must be at least 5 characters.',
        'message.required' => 'The message field is required.',
        'message.min' => 'The message must be at least 10 characters.',
    ];

    public function updatedSearch($value)
    {
        if (strlen($value) < 2) {
            $this->searchResults = [];
            return;
        }
        
        $this->searchResults = User::query()
            ->where(function ($query) use ($value) {
                $query->where('email', 'like', '%' . $value . '%')
                    ->orWhere('name', 'like', '%' . $value . '%');
            })
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function selectRecipient($userId, $email)
    {
        if (count($this->recipients) >= 5) {
            $this->errorMessage = 'You can only send email up to 5 addresses only!';
            return;
        }
        if (!in_array($email, $this->recipients)) {
            $this->recipients[] = $email;
        }
        $this->search = '';
        $this->searchResults = [];
    }

    public function removeRecipient($email)
    {
        $this->recipients = array_filter($this->recipients, function ($e) use ($email) {
            return $e !== $email;
        });
    }

    public function submit()
    {
        $this->validate();
        $this->resetMessages();

        try {
            $sender = Auth::user();
            $emailContent = [
                'emails.contact-user',
                [
                    'senderName' => $sender->name,
                    'senderEmail' => $sender->email,
                    'subject' => $this->subject,
                    'messageContent' => $this->message
                ]
            ];

            $emailService = new SendEmail();
            $failedRecipients = [];
            
            $notificationTypes = [
                'admin' => NotificationType::firstWhere('type_name', 'email_notification'),
                'super_admin' => NotificationType::firstWhere('type_name', 'super_admin_email_notification'),
                'user' => NotificationType::firstWhere('type_name', 'user_email_notification'),
            ];
            
            foreach ($this->recipients as $recipientEmail) {
                $user = User::where('email', $recipientEmail)->first();
                $sent = $emailService->send(
                    $recipientEmail,
                    $this->subject,
                    $emailContent,
                    null, 
                    null, 
                    null, 
                    false 
                );
                
                if (!$sent) {
                    $failedRecipients[] = $recipientEmail;
                    Log::error("Failed to send email to: {$recipientEmail}");
                } elseif ($user) {
                    // Determine notification type based on recipient role
                    $type = $user->isSuperAdmin() 
                        ? $notificationTypes['super_admin'] 
                        : ($user->isAdmin() 
                            ? $notificationTypes['admin'] 
                            : $notificationTypes['user']);
                    
                    if ($type) {
                        $notification = Notification::create([
                            'type_id' => $type->id,
                            'message' => "New message: " . Str::limit($this->subject, 50),
                        ]);

                        $user->notifications()->attach($notification->id, [
                            'is_read' => false,
                            'notified_at' => now()
                        ]);
                    }
                }
            }

            if (count($failedRecipients)) {
                $this->errorMessage = 'Failed to send to: ' . implode(', ', $failedRecipients);
            } else {
                $this->successMessage = 'Your message has been sent successfully!';
                $this->reset(['recipients', 'subject', 'message']);
            }

        } catch (\Throwable $e) {
            Log::error("Contact form submission failed: " . $e->getMessage());
            $this->errorMessage = 'Failed to send your message. Please try again later.';
        }
    }

    public function resetMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->searchResults = [];
    }

    public function updatingSearch()
    {
        $this->searchResults = [];
    }

    public function render()
    {
        return view('livewire.super-admin.contact-user');
    }
}