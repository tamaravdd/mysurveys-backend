<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Participant;
use Illuminate\Support\Facades\Auth;

class UserController extends BaseController
{
    /**
     * Display a listing of the User.   
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->role !== 'administrator') {
            $this->sendError("Only administrators can show all users");
        }
        $users = User::all();
        return response()->json(['users' => $users], 200);
    }

    /**
     * Display the specified User.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $user->load("participant");
        return $user;
    }

    /**
     * Update the specified User in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {

        if ($request->all()['banned'] == true) {
            $p = Participant::find($user->id);
            $p->paypal_id = null;
            $p->paypal_id_status = "Invalid";
            $p->save();
        }
        $user->update($request->all());
        $user->participant->paypal_id_status = $request['paypal_id_status'];
        $user->participant->save();
        $user->save();
        return $user;
    }

    /**
     * Remove the specified User from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
        // if (Auth::user()->role !== 'administrator') {
        // $this->sendError("Only administrators can delete");
        // }
    }
}
