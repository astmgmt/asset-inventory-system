<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class SendEmail
{
    /**
     * Send an email with HTML content
     *
     * @param string $from Sender email address
     * @param string $subject Email subject
     * @param string $body HTML email content
     * @param string|array $to Recipient email(s)
     * @param string|array $cc CC email(s) (optional)
     * @return bool True on success, false on failure
     */
    public function send($from, $subject, $body, $to, $cc = [])
    {
        try {
            Mail::html($body, function ($message) use ($from, $subject, $to, $cc) {
                $message->from($from)
                        ->subject($subject)
                        ->to($to);
                
                if (!empty($cc)) {
                    $message->cc($cc);
                }
            });
            return true;
        } catch (\Exception $e) {
            \Log::error('Email sending failed: '.$e->getMessage());
            return false;
        }
    }
}