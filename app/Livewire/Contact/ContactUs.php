<?php

namespace App\Livewire\Contact;

use Livewire\Component;
use App\Models\User;
use App\Services\SendEmail;
use Illuminate\Support\Facades\Log;

class ContactUs extends Component
{
    public $subject = '';
    public $message = '';

    public $success = false;
    public $successMessage = '';

    public $error = false;
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

            $primaryRecipient = $superAdmins->first()->email;
            $ccList = $superAdmins->skip(1)->pluck('email')
                        ->merge($admins->pluck('email'))
                        ->unique()
                        ->toArray();

            $emailContent = [
                'emails.contact-admins',
                [
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

            // Redirect with flash message
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
