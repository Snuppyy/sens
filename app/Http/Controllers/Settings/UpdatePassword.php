<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UpdatePassword extends Controller
{
    /**
     * Update the user's password.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|min:6' //|confirmed
        ]);

        $user = $request->user();
        $user->password = bcrypt($request->password);
        $user->save();
    }
}
