<?php

namespace App\Http\Controllers;

use App\Participant;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\User;
use Validator;
use App\Http\Resources\Participants as ParticipantsResource;
use Illuminate\Support\Facades\Auth;

class ParticipantController extends BaseController
{

    /**
     * Display a listing of the Participants.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $participants = Participant::where("id", ">", "3")->get();
        return new ProjectParticipants(ProjectParticipant::where("projects_projectid", $request['project_id'])->orderBy($request['sort'], $request['order'])->paginate());

        return $this->sendResponse(new ParticipantsResource($participants), 'Participants retrieved successfully.');
    }

    /**
     * Advanced sel. project participants - rows of conditions
     *
     * @return \Illuminate\Http\Response
     */
    public function get_advanced_selection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Missing project ID');
        }

        $forms = $validator->valid()['categoryForm'];
        $select_paypal_status_ok = $request['paypal_status_ok'] == true;
        $eligible_seed = $request['eligible_seed'] == true;
        $eligible_peers = $request['eligible_peers'] == true;
        $participants = Participant::whereHas('user', function ($query) use ($forms, $select_paypal_status_ok, $eligible_seed, $eligible_peers) {
            foreach ($forms as $key => $f) {
                $query->where($f['name'], $f['operator'], $f['value']);
            }
            if ($select_paypal_status_ok) {
                $query->where("paypal_id_status", "=", "Ok");
            }
            if ($eligible_seed) {
                $query->whereHas("friends");
                $query->where("seed_id", NULL);
            }
            if ($eligible_peers) {
                $query->whereHas("friends");
                $query->where("seed_id", "!=", NULL);
            }
        })->get();
        return new ParticipantsResource($participants);
    }

    /**
     * Store a newly created Participant in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        //handled elsewhere
    }

    /**
     * Display the specified Participant.
     *
     * @param  \App\Participant  $participant
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = $this->retrieve_profile($id);
        return $this->sendResponse($user, 'User retrieved');
    }

    /**
     * Allow a user to request their own profile information
     */
    public function show_profile()
    {
        $user = Auth::user();
        $profile = $this->retrieve_profile($user->id);
        return $this->sendResponse($profile, 'Profile retrieved');
    }

    /**
     * Retrieve profile from DB
     * @param type $id
     */
    private function retrieve_profile($id)
    {
        $p = \App\Participant::where("user_id", $id)->first();
        $p->load('user');
        $u = User::with('participant')->where('id', $p->user_id)->first();
        $u->participant->load('friends');
        $u->participant->load('seed');
        if ($u->participant->seed) {
            $u->participant->seed->load('user');
        }
        $u->participant->friends->load('user');
        $ua = array_merge($u->toArray(), $u->participant ? $u->participant->toArray() : []);
        //        rearrange data structure
        unset($ua['participant']);
        $ua['subrole'] = $u->getsubrole();
        $ua['role'] = $u->getRoleNames()[0];
        return $ua;
    }

    /**
     * Update the specified Participant in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Participant  $participant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $p = Participant::find($id);
        $input = $request->all();
        $validator = Validator::make($input, Participant::validator());
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $p->update($validator->valid());
        $this->logger('info', Auth::user()->email . ' updated ' . $p->email, $validator->valid());
        return $this->sendResponse($p, 'Profile updated successfully.');
    }
}
