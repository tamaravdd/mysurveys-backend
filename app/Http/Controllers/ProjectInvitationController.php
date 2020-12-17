<?php

namespace App\Http\Controllers;

use App\EmailTemplate;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Participant;
use App\ProjectParticipant;
use Validator;
use App\User;
use App\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Sendlist;
use Egulias\EmailValidator\Warning\EmailTooLong;

// $pp = ProjectParticipant::with(['user' => function ($query) use ($project_id) {
//     $query->where('projects_projectid', '=', $project_id);
// },])->get();
//
// $tournaments = Tournament::with(['championships' => function ($query) {
//     $query->where('something', 'like', '%this%');
// },
// 'championships.settings' => function ($query) {
//     $query->where('something', 'like', '%this%');
// },
// ...])->get();
class ProjectInvitationController extends BaseController
{

    /**
     * Send custom message to selected participants
     * @param Request $request
     * @return type
     */
    public function send_custom_message(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'template_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError(implode(",", $validator->messages()->all()), implode(",", $validator->messages()->all()));
        }
        $ids = $request['ids'];
        $data = $request->all();
        $data['project'] = Project::find($data['project_id']);

        $template = EmailTemplate::find($request['template_id']);
        $data['body'] = $template['body'];
        $data['subject'] = $template['subject'];

        if ($request['test']) {
            $user = Auth()->user();
            $user->sendEmailTemplateMessage($data);
            return $this->sendResponse('Test email sent to ' . $user->email, $user->email);
        }

        $users_actual = User::with('participant')->whereIn("id", $ids)->get();

        foreach ($users_actual as $message_target) {
            //            TODO factor into env
            sleep(1);
            $message_target->sendEmailTemplateMessage($data);
            $this->logger('info', "Custom message sent to participant", ["user" => $message_target]);
        }
        return $this->sendResponse($ids, 'ids');
    }

    /**
     * Send project invitations
     */
    public function send_project_invitations(Request $request)
    {

        //default as DEV for no actual sending
        $DEV = 'DEVELOPMENT';

        //code only setting - bypass for checks
        //        TODO handle bypass /disable
        $BYPASS = True;
        $validator = Validator::make($request->all(), [
            'ids' => 'required',
            'project_id' => 'required',
            'TEST_MODE' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Missing required ids/dev');
        }
        $project_id = $request['project_id'];
        $ids = $request['ids'];

        if ($validator->valid()['TEST_MODE'] == 'PRODUCTION') {
            $DEV = 'PRODUCTION';
        }

        $user_ids = ProjectParticipant::where("projects_projectid", $project_id)
            ->whereIn("participants_userid", $ids)->pluck('participants_userid');

        $users_actual = User::with('participant')->whereIn("id", $user_ids)->get();
        $invitation_errors = $this->check_if_invitation_is_ok($project_id, $user_ids);
        $this->updateStartState($project_id, $invitation_errors);

        if ($DEV === 'DEVELOPMENT') {
            return $this->sendResponse(["ERRORS" => $invitation_errors, "PREVIEW" => $users_actual], 200);
        }
        if (!empty($invitation_errors) && (!$BYPASS)) {
            return $this->sendError($invitation_errors);
        }

        $pp_actual = ProjectParticipant::whereIn("participants_userid", $ids)
            ->where("projects_projectid", $project_id)
            ->get();

        $d = new \DateTime();
        $now = $d->format("Y-m-d H:i:s");

        $project_actual = Project::find($project_id);
        $this->logger('info', "Initiating invitations group", ["ids" => $ids, "project_id" => $project_id]);
        $count = 0;

        /**
         * Initiate sending sequence
         */
        $project_actual->state = "Started";
        $project_actual->save();
        $data = $project_actual->toArray();
        $data = array_merge($data, ["remind" => isset($request->all()['reminder'])]);

        foreach ($pp_actual as $invitee_participant) {
            sleep(1);
            DB::transaction(function () use ($data, $invitee_participant, $now,  $project_id, &$count) {
                $data['participant'] = $invitee_participant;
                $invitee_participant->invited = $now;
                $invitee_participant->save();

                $user_actual = User::find($invitee_participant->participants_userid);
                $user_actual->sendInvitationNotification($data);
                $count = $count + 1;
                $this->logger('info', "Invitation sent to participant", ["user" => $user_actual, "project_id" => $project_id]);

                $slData = [
                    "projects_projectid" => $project_id,
                    "replyto" => 'support',
                    "mailto" => $user_actual->email,
                    "subject" => "Invitation to study",
                    "body" => "InvitationContent",
                    "lastattempt" => $now
                ];
                $sendlist = new Sendlist();
                $sendlist->create($slData);
            });
        }

        //        $users_actual->load('participants');
        return $this->sendResponse($count . " Invitations sent", 200);
    }


    /**
     * Make sure the selected participants are valid etc
     * test banned, email verification, paypal OK
     * @return Bool
     * TODO - more elegant way?
     */
    public function checkIfParticipantsAreOk($project_id, $ids)
    {

        $participant_ids = $ids;
        $errors = [];

        $email_verification_test = ProjectParticipant::with('user')->whereHas('user', function ($q) {
            $q->where('email_verified_at', NULL);
        })
            ->whereIn("participants_userid", $participant_ids)
            ->where("projects_projectid", $project_id)
            ->get();

        if (!$email_verification_test->isEmpty()) {
            $errors[] = 'Error - 1 or more users has not validated email';
        }

        $banned_test = ProjectParticipant::with('user')->whereHas('user', function ($q) {
            $q->where('banned', "=", 1);
        })
            ->whereIn("participants_userid", $participant_ids)
            ->where("projects_projectid", $project_id)
            ->get();

        if (!$banned_test->isEmpty()) {
            $errors[] = 'Error - 1 or more users is banned';
        }

        $paypal_verification_test = ProjectParticipant::with('participant')->whereHas('participant', function ($q) {
            $q->whereNull('paypal_id_status');
        })
            ->whereIn("participants_userid", $participant_ids)
            ->where("projects_projectid", $project_id)
            ->get();
        if (!$paypal_verification_test->isEmpty()) {
            $errors[] = 'Error - 1 or more users has not verified PayPal';
        }
        return $errors;
    }

    /**
     * Run multiple checks on invitation
     */
    private function check_if_invitation_is_ok($project_id, $ids)
    {

        $errors = $this->checkIfParticipantsAreOk($project_id, $ids);

        $user_ids = ProjectParticipant::where("projects_projectid", $project_id)
            ->whereIn("participants_userid", $ids)->pluck('participants_userid');

        $project = Project::find($project_id);

        if ($project->default_end_date_elapsed) {
            $errors[] = $project->default_end_date_elapsed;
        }

        if ($project->expected_payout_exceeds_quota($user_ids)) {
            $errors[] = $project->expected_payout_exceeds_quota($user_ids);
        }

        if ($project->state !== 'Started') {
            $errors[] = 'Project state is ' . $project->state;
        }


        if (!$project->exp_payout) {
            $errors[] = 'The project has no expected payout';
        }


        $participants_actual = ProjectParticipant::whereIn("participants_userid", $user_ids)
            ->where("projects_projectid", $project_id)
            ->get();

        $proposed_invitations_cost = $project->proposed_invitations_cost($participants_actual);

        $used_quota = $project->used_quota;

        $proposed_total = $used_quota + $proposed_invitations_cost;

        if ($proposed_total > $project->quota) {
            $errors[] = 'The proposed total of ' . $proposed_total . " exceeds the project quota of " . $project->quota;
        }

        $number_finished = $project->number_finished;
        if ($number_finished > $project->desired_sample_size) {
            $errors[] = "Sample size reached!";
        }

        $invitation_threshold = $project->proposed_invitations_exceed_desired_number($participants_actual);
        if ($invitation_threshold) {
            $errors[] = "The proposed number of invitations plus the number already invited exceeds the desired number for the project";
        }

        return $errors;
        // TODO check below, still necessary?  
        // if num finished is less than desired sample size , can set project state to open
        // should wrap these checks in an updateStartState func
        // {{{then wrap all of the preceeding in a checkIfInvitationIsOk function}}}
    }

    /**
     * Change start state of project to open if no errors
     * @param type $project_id
     * @param type $errors
     */
    private function updateStartState($project_id, $errors)
    {
        $start_state = "Closed";
        if (empty($errors)) {
            $start_state = "Open";
        }
        $p = Project::find($project_id);
        $p->start_state = $start_state;
        $p->save();
    }

    /**
     * Test whether the project is over - end date elapsed
     */
    private function check_project_time_elapsed($project_id)
    {
        $project = Project::find($project_id);
        $start_dt = new \DateTime($project->defaultend);
        $now = new \DateTime();
        if ($now > $start_dt) {
            return false;
        }
        return true;
    }

    //possibly obsolete
    private function buildApiProjectInviteEmail()
    {
    }

    private function selectInvitedParticipants()
    {
    }

    private function updateInvitedParticipants()
    {
    }

    private function updateProjectstartState()
    {
        // Check if date has passed 'defaultend' and that the quota is ok and
        // that the desired sample size has not been reached yet
        // then update project if necessary
        // call the project started if OK
    }
}
