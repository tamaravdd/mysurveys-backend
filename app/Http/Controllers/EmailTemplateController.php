<?php

namespace App\Http\Controllers;

use App\EmailTemplate;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Project;
use Validator;



class EmailTemplateController extends BaseController
{


    /**
     * Display a listing of the Email Templates
     *
     * @return \Illuminate\Http\Response
     */
    public function email_templates_with_project(Request $request)
    {
        //
        // var_dump($request);
        $project_id =  $request->get('project_id');
        $templates = EmailTemplate::all();
        foreach ($templates as $template) {
            $template->transformed = $this->transformEmailTemplateBody($template, $project_id);
        }
        return $this->sendResponse($templates->toArray(), 'Email Templates Retrieved successfully.');
    }


    /**
     * Display a listing of the Email Templates
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $templates = EmailTemplate::all();
        foreach ($templates as $template) {
            $template->transformed = $this->transformEmailTemplateBody($template);
        }
        return $this->sendResponse($templates->toArray(), 'Email Templates Retrieved successfully.');
    }

    /**
     * Change the wildcards to their new values
     */
    public function transformEmailTemplateBody($template, $project_id = 1, $user = null)
    {

        $body = $template['body'];
        $subject = $template['subject'];
        $project_data = Project::find($project_id);
        if (!$project_data) {
            return False;
        }

        $replacements = array();
        $replacements[] = array('*projecttitle*', stripslashes($project_data->project_title));
        $replacements[] = array('*link*', $project_data->project_link);
        $replacements[] = array('*responsibleperson*', stripslashes($project_data->responsible_person));
        $replacements[] = array('*projectinfo*', stripslashes($project_data->project_description));
        $replacements[] = array('*projectenddate*', $project_data->defaultend);
        $replacements[] = array('*maxpayout*', $project_data->max_payout);
        $replacements[] = array('*expectedpayout*',  $project_data->expected_payout);
        if ($user) {
            $replacements[] = array('*username*', $user->email);
        } else {
            $replacements[] = array('*username*', '<no user selected>');
        }

        // TODO user login link 
        // $replacements[] = array('*loginlink*', selfURLbase() . 'loginuser.php?loginas=' . $emailaddress);
        // TODO another contact address 
        $replacements[] = array('*contactaddress*',  $project_data->expected_payout);

        foreach ($replacements as $rep) {
            $subject = str_replace($rep[0], $rep[1], $subject);
            $body = str_replace($rep[0], $rep[1], $body);
        }
        return array(
            "body" => $body,
            "subject" => $subject
        );
    }



    /**
     * Store a newly created Email Templates in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $input = $request->all();
        $validator = Validator::make($input, [
            'subject' => 'required|string',
            // 'body' => 'required|string'
        ]);
        if (!isset($input['body'])) {
            $input['body'] = "";
        }
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $template = EmailTemplate::create($input);
        return $this->sendResponse($template, 'Template created successfully.');
    }





    /**
     * Display the specified Email Templates.
     *
     * @param  \App\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(EmailTemplate $emailTemplate)
    {
        //
    }

    /**
     * Show the form for editing the specified Email Templates.
     *
     * @param  \App\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        //
    }

    /**
     * Update the specified Email Templates in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $input = $request->all();
        $template = $emailTemplate;
        $data = array(
            "subject" => $input['subject'],
            "body" => $input['body'],

        );
        $template->update($data);
        return $this->sendResponse($template, 'Template updated successfully.', 200);
    }

    /**
     * Remove the specified Email Templates from storage.
     *
     * @param  \App\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        //
    }
}
