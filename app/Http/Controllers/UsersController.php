<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\User;
use PHPUnit\Util\Json;

class UsersController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     */
    public function show($id)
    {
        Log::info('requested user id');
        Log::info($id);

        return response()->json([
            'user' => User::findOrFail($id)->with('roles')->get()
        ], 200);
    }
}
