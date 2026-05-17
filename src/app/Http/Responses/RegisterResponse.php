<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Get the response for a successful registration.
     */
    public function toResponse($request)
    {
        return redirect('/email/verify');
    }
}
