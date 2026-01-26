<?php

namespace Modules\Policy\Notifications;

use App\Models\EmailNotificationSetting;
use Illuminate\Bus\Queueable;
use App\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;

class PolicyPublishedNotification extends BaseNotification
{
    use Queueable;

    private $policy;
    private $emailSetting;

    /**
     * Create a new notification instance.
     */
    public function __construct($policy)
    {
        $this->policy = $policy;
        $this->company = $this->policy->company;

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
        $build = parent::build();
        $url = route('policy.show', $this->policy->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('policy::email.policyPublished.text') . '<br><br>' . __('policy::email.policyPublished.title') . ': <b>' . $this->policy->title . '</b>.';

        return $build
            ->subject(__('policy::email.policyPublished.subject') . ' - ' . config('app.name') . __('!'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('policy::email.policyPublished.action'),
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
            'policy_id' => $this->policy->id,
        ];
    }

    public function toSlack($notifiable)
    {
        try {
            $url = route('policy.show', $this->policy->id);
            $url = getDomainSpecificUrl($url, $this->company);

            $subject = __('policy::email.policyPublished.subject');
            $notifiableName = __('email.hello') . ' ' . $notifiable->name;

            $content = __('policy::email.policyPublished.text') . "\n\n" .
                __('policy::email.policyPublished.title') . ': *' . $this->policy->title . '*.';

            return $this->slackBuild($notifiable)
                ->content($subject."\n\n". $notifiableName ."\n\n". $content  . "\n\n" . $url);
        } catch (\Exception $e) {
            return $this->slackRedirectMessage('policy::email.policyPublished.subject', $notifiable);
        }
    }

}
