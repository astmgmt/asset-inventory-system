<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class CustomRegisteredUserController extends Controller
{
    protected $creator;

    public function __construct(CreateNewUser $creator)
    {
        $this->creator = $creator;
    }

    public function store(Request $request)
    {
        $user = $this->creator->create($request->all());

        event(new Registered($user));

        // REDIRECT USER TO LOGIN PAGE
        return Redirect::route('login')->with('info', 'Waiting for Approval but account has been successfully created.');
    }
}
