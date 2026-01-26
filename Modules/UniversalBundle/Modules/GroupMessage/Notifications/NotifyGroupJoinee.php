<?php

namespace Modules\GroupMessage\Notifications;

use Illuminate\Bus\Queueable;
use App\Notifications\BaseNotification;
use App\Models\EmailNotificationSetting;
use Illuminate\Notifications\Messages\MailMessage;

class NotifyGroupJoinee extends BaseNotification
{

    use Queueable;

    private $group;
    private $emailSetting;

    /**
     * Create a new notification instance.
     */
    public function __construct($group)
    {
        $this->group = $group;
        $this->company = $this->group->company;

        // When there is company of user.
        if ($this->company) {
            $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'user-registrationadded-by-admin')->first();
        }
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $via = ['database'];

        if (is_null($this->company)) {
            array_push($via, 'mail');

            return $via;
        }

        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
            $this->slackUserNameCheck($notifiable) ? array_push($via, 'slack') : null;
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $build = parent::build($notifiable);
        $url = route('group-messaging.index').'?view=group';

        $content = __('groupmessage::email.GroupJoin.text').' <b>'.$this->group->name .'</b> '.__('app.by').' '.$this->group->addedBy->name.'.';

        return $build
            ->subject(__('groupmessage::email.GroupJoin.subject'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('groupmessage::app.viewGroup'),
                'notifiableName' => $notifiable->name
            ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'user_id' => $notifiable->id,
            'group_id' => $this->group->id,
        ];
    }

    public function toSlack($notifiable)
    {
        return $this->slackBuild($notifiable)->content(__('email.hello') . ' ' . $notifiable->name . ' ' . __('groupmessage::email.GroupJoin.subject'));
    }

}
