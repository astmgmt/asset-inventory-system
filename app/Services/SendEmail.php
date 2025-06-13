<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class SendEmail
{
    /**
     * Reusable email sending service
     *
     * @param string $to              Recipient email address
     * @param string $subject         Email subject
     * @param string|array $content   Raw HTML string or ['view_name', data_array]
     * @param array $cc               CC email addresses
     * @param string|null $attachmentContent Optional PDF content
     * @param string|null $attachmentName    Optional filename
     * @param bool $isHtml            True if content is raw HTML, false if it's a Blade view
     * @return bool
     */
    public function send($to, $subject, $content, $cc = [], $attachmentContent = null, $attachmentName = null, $isHtml = true)
    {
        try {
            if ($isHtml) {
                // Content is raw HTML string
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
            Log::error('Email sending failed: ' . $e->getMessage());
            return false;
        }
    }
}


// namespace App\Services;

// use Illuminate\Support\Facades\Mail;

// class SendEmail
// {
//     public function send($to, $subject, $view, $data, $attachmentContent = null, $attachmentName = null)
//     {
//         try {
//             Mail::html($view, $data, function ($message) use ($to, $subject, $attachmentContent, $attachmentName) {
//                 $message->to($to)
//                     ->subject($subject);
                
//                 if ($attachmentContent) {
//                     $message->attachData($attachmentContent, $attachmentName, [
//                         'mime' => 'application/pdf',
//                     ]);
//                 }
//             });
            
//             return true;
//         } catch (\Exception $e) {
//             \Log::error('Email sending failed: '.$e->getMessage());
//             return false;
//         }
//     }
// }

// namespace App\Services;

// use Illuminate\Support\Facades\Mail;

// class SendEmail
// {
//     public function send($from, $subject, $body, $to, $cc = [])
//     {
//         try {
//             Mail::html($body, function ($message) use ($from, $subject, $to, $cc) {
//                 $message->from($from)
//                         ->subject($subject)
//                         ->to($to);
                
//                 if (!empty($cc)) {
//                     $message->cc($cc);
//                 }
//             });
//             return true;
//         } catch (\Exception $e) {
//             \Log::error('Email sending failed: '.$e->getMessage());
//             return false;
//         }
//     }
// }