<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Get the response for a successful login.
     */
    public function toResponse($request)
    {
        if (Auth::user()->is_admin) {
            return redirect()->route('admin.attendance.list');
        }

        return redirect()->route('attendance.index');
    }
}
