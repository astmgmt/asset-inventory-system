<?php

namespace App\Traits;

use App\Models\UserActivity;
use App\Services\SendEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

trait TracksUserActivities
{
    protected function recordActivity($activityName, $description, Request $request, $isSensitive = false, $additionalEmails = [])
    {
        try {
            $activity = UserActivity::create([
                'user_id' => auth()->id(),
                'activity_name' => $activityName,
                'status' => 'active',
                'description' => $description,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);

            if ($isSensitive) {
                $this->sendSecurityAlerts($activity, $additionalEmails);
            }

            return $activity;
        } catch (\Exception $e) {
            Log::error("Failed to record activity: " . $e->getMessage());
            return null;
        }
    }

    protected function sendSecurityAlerts($activity, $additionalEmails = [])
    {
        try {
            $emailService = new SendEmail();
            $user = auth()->user();
            $emails = array_unique(array_merge([$user->email], $additionalEmails));
            
            $results = [];
            
            foreach ($emails as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    Log::error("Invalid email address: $email");
                    continue;
                }
                
                $content = [
                    'emails.security-alert',
                    [
                        'user' => $user,
                        'activity' => $activity,
                        'time' => now()->format('M d, Y h:i A')
                    ]
                ];

                $results[$email] = $emailService->send(
                    $email,
                    'Security Alert: ' . $activity->activity_name,
                    $content,
                    [], 
                    null, 
                    null, 
                    false 
                );
            }
            
            return $results;
        } catch (\Exception $e) {
            Log::error("Security alerts failed: " . $e->getMessage());
            return false;
        }
    }
}