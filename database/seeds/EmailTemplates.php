<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Project;
use App\EmailTemplate;

class EmailTemplates extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        //email templates

        $eTemplates = [
            [
                "projects_projectid" => 1001,
                "languages_idlanguages" => "0",
                "ettype" => "Invitation",
                "subject" => "Invitation to a SciFriends study",
                "body" => "*title* *username*, *nl**nl* You are invited to join the following study: *nl**nl* *projecttitle*.*nl* *nl* *projectinfo* *nl* *nl*.<br><br>  The payment for participation is *maxpayout* USD. The study will be open until *projectenddate*, or until enough people participate. If you have any questions, please contact *responsibleperson*:*nl* *contactaddress**nl* *nl*. <br><br>Please click on the link below to log in to your account or copy and paste it into your browser. *nl*  *loginlink* ",
                "etdefault" => "1"
            ],
            [
                "projects_projectid" => 1002,
                "languages_idlanguages" => "0",
                "ettype" => "Reminder",
                "subject" => "SciFriends Reminder",
                "body" => "*title* *username*, *nl**nl* We would like to remind you that you are invited to join  the following study: *nl**nl* *projecttitle*. *nl* *nl* *projectinfo* *nl* *nl*. <br><br>The payment for participation is *maxpayout* USD. The study will be open until *projectenddate*, or until enough people participate. If you have any questions, please contact *responsibleperson*:*nl* *contactaddress**nl* *nl*. <br><br>Please click on the link below to log in to your account, or copy and paste it in your browser.  *nl*  *loginlink* ",
                "etdefault" => "1"
            ],
        ];
        foreach ($eTemplates as $eData) {
            EmailTemplate::insert($eData);
        }
    }

}
