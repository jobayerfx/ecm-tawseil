<?php

namespace Modules\Onboarding\Notifications;

use App\Notifications\BaseNotification;
use Modules\Onboarding\Entities\OnboardingNotificationSetting;

class OnboardingNotification extends BaseNotification
{

    private $user;
    private $emailSetting;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
        $this->company = $this->user->company;
        $this->emailSetting = OnboardingNotificationSetting::where('company_id', $this->company->id)->where('slug', 'onboard-notification')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = ['database'];

        if ($this->emailSetting->send_email == 'yes') {
            array_push($via, 'mail');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = route('employees.show', $this->user->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('onboarding::messages.onboardingStartMailText');

        return parent::build()
            ->subject(__('onboarding::messages.onboardingStartMailSubject'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('app.view') . ' ' . __('onboarding::clan.onboardingTasks'),
                'notifiableName' => $notifiable->name
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'user_id' => $notifiable->id,
            'heading' => __('onboarding::modules.onboardingStartMailSubject'),
        ];
    }

}
