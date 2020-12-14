<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Project;
use App\EmailTemplate;

class EmailTemplates extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        //email templates

        $eTemplates = [
            [
                "subject" => "Invitation to a MySurveys study",
                "body" => "Hello *username*, You are invited to join the following study:  *projecttitle*.  *projectinfo*  .<br><br>  The payment for participation is *maxpayout* USD. The study will be open until *projectenddate*, or until enough people participate. If you have any questions, please contact *responsibleperson*: *contactaddress* . <br><br>Please click on the link below to log in to your account or copy and paste it into your browser.   *loginlink* ",
            ],
            [
                "subject" => "MySurveys Reminder",
                "body" => "Hello *username*,  We would like to remind you that you are invited to join  the following study:  *projecttitle*.   *projectinfo*  . <br><br>The payment for participation is *maxpayout* USD. The study will be open until *projectenddate*, or until enough people participate. If you have any questions, please contact *responsibleperson*: *contactaddress* . <br><br>Please click on the link below to log in to your account, or copy and paste it in your browser.    *loginlink* ",
            ],
        ];
        foreach ($eTemplates as $eData) {
            EmailTemplate::insert($eData);
        }
    }
}
