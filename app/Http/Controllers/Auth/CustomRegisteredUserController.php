<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Role;
use App\Models\User;
use App\Services\SendEmail;
use Carbon\Carbon;

class CustomRegisteredUserController extends Controller
{
    protected $creator;
    protected $sendEmail;

    public function __construct(CreateNewUser $creator, SendEmail $sendEmail)
    {
        $this->creator = $creator;
        $this->sendEmail = $sendEmail;
    }

    public function store(Request $request)
    {
        $user = $this->creator->create($request->all());
        event(new Registered($user));

        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole) {
            $superAdmins = User::where('role_id', $superAdminRole->id)
                                ->orderBy('id')
                                ->get();

            if ($superAdmins->isNotEmpty()) {
                $firstSuperAdmin = $superAdmins->shift(); 
                $ccEmails = $superAdmins->pluck('email')->toArray();

                $subject = "New User Registration: {$user->name}";
                $viewData = [
                    'user' => $user,
                    'time' => Carbon::now()->toDateTimeString(),
                ];

                $this->sendEmail->send(
                    $firstSuperAdmin->email,
                    $subject,
                    ['emails.new-registration', $viewData],
                    $ccEmails,
                    null,
                    null,
                    false
                );
            }
        }

        return Redirect::route('login')->with('info', 'Waiting for Approval but account has been successfully created.');
    }
}