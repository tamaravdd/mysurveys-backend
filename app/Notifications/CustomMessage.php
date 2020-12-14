<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class CustomMessage extends Notification
{

    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        //
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return $this->getMessage($notifiable, $this->data);
    }

    private function replace_wildcards($body)
    {

        // $replacements[] = array('*link*', $row['link']);
        // $replacements[] = array('*responsibleperson*', stripslashes($row['responsible_person']) );
        // $replacements[] = array('*projecttitle*', stripslashes($row['project_title']));
        // $replacements[] = array('*projectinfo*', stripslashes($row['description']));
        // $replacements[] = array('*projectenddate*', datetimeReassemblePrettyEmail($row['defaultend'], $lang));
        // $replacements[] = array('*maxpayout*', $row['max_payout']);
        // $replacements[] = array('*expectedpayout*', $row['exp_payout']);
        // $replacements[] = array('*loginlink*', selfURLbase() . 'loginuser.php?loginas=' . $emailaddress);

        // $replacements[] = array('*adminaddress*', getEmailAdressFromNickname('admin'));
        // $replacements[] = array('*contactaddress*', $_SESSION['contactaddress']);

        $row = $this->data['project']->project_title;

        $patterns = array();
        $patterns[0] = "/\*\*\w+\*\*/";



        $body = str_replace("**project_title**",  $this->data['project']->project_title, $body);
        $replacements[0] = "";
        $body =  preg_replace($patterns, $replacements, $body);
        var_dump($body);
        return $body;

        //     $replacements = array();
        // $replacements[] = array('*link*', $row['link']);
        // $replacements[] = array('*responsibleperson*', stripslashes($row['responsible_person']));
        // $replacements[] = array('*projecttitle*', stripslashes($row['project_title']));
        // $replacements[] = array('*projectinfo*', stripslashes($row['description']));
        // $replacements[] = array('*projectenddate*', datetimeReassemblePrettyEmail($row['defaultend'], $lang));
        // $replacements[] = array('*maxpayout*', $row['max_payout']);
        // $replacements[] = array('*expectedpayout*', $row['exp_payout']);
        // $replacements[] = array('*loginlink*', selfURLbase() . 'loginuser.php?loginas=' . $emailaddress);

        // $replacements[] = array('*adminaddress*', getEmailAdressFromNickname('admin'));
        // $replacements[] = array('*contactaddress*', $_SESSION['contactaddress']);
    }

    private function getMessage($notifiable, $extra = [])
    {



        $body = $this->replace_wildcards($this->data['body']);
        // var_dump($body);

        $mailMessage = new MailMessage();
        $mailMessage->subject(Lang::get($this->data['subject']))
            ->greeting("Greetings " . $notifiable->participant['first_name'] . "!");
        $mailMessage->line($body);
        if (isset($this->data['link']) && $this->data['link'] !== '') {
            $mailMessage->action(Lang::get($this->data['link']), $this->data['link']);
        }

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
