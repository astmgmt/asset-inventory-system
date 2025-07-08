<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use App\Models\User;
use App\Services\SendEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            
            foreach ($this->recipients as $recipientEmail) {
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