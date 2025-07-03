<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class SendEmail
{
    public function send($to, $subject, $content, $cc = [], $attachmentContent = null, $attachmentName = null, $isHtml = true)
    {
        try {
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException("Invalid email address: {$to}");
            }
            
            if ($isHtml) {
                Mail::html($content, function ($message) use ($to, $subject, $cc, $attachmentContent, $attachmentName) {
                    $message->to($to)->subject($subject);
                    if (!empty($cc)) $message->cc($cc);
                    if ($attachmentContent) {
                        $message->attachData($attachmentContent, $attachmentName, [
                            'mime' => 'application/pdf',
                        ]);
                    }
                });
            } else {
                // Content is a Blade view with data: ['view.name', [...data]]
                if (!is_array($content) || count($content) !== 2) {
                    throw new InvalidArgumentException('View content must be an array: [view_name, data_array]');
                }

                [$view, $data] = $content;

                Mail::send($view, $data, function ($message) use ($to, $subject, $cc, $attachmentContent, $attachmentName) {
                    $message->to($to)->subject($subject);
                    if (!empty($cc)) $message->cc($cc);
                    if ($attachmentContent) {
                        $message->attachData($attachmentContent, $attachmentName, [
                            'mime' => 'application/pdf',
                        ]);
                    }
                });
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Email sending failed: {$e->getMessage()}", [
                'to' => $to,
                'subject' => $subject
            ]);            
            return false;
        }
    }
}