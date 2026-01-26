<?php

namespace Modules\ServerManager\Notifications;

use Illuminate\Bus\Queueable;
use App\Notifications\BaseNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\ServerManager\Entities\ServerHosting;

class HostingExpiringNotification extends BaseNotification
{
    use Queueable;

    protected $hosting;

    /**
     * Create a new notification instance.
     */
    public function __construct(ServerHosting $hosting)
    {
        $this->hosting = $hosting;
        $this->company = $this->hosting->company;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $daysUntilExpiry = $this->hosting->daysUntilExpiry();
        $notificationDays = $this->hosting->notification_days_before;
        $timeUnit = $this->hosting->notification_time_unit;

        $emailContent = parent::build()
        ->subject(__('servermanager::email.hosting.subject'))
        ->greeting(__('servermanager::email.hosting.greeting', ['name' => $notifiable->name]))
        ->line(__('servermanager::email.hosting.message', [
            'hostingName' => $this->hosting->name,
            'daysUntilExpiry' => $daysUntilExpiry,
            'domainName' => $this->hosting->domain_name,
            'expiryDate' => $this->hosting->renewal_date->format($this->company->date_format),
            'notificationDays' => $notificationDays,
            'timeUnit' => $timeUnit
        ]));

        return $emailContent->line(__('servermanager::email.domain.footer'));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'id' => $this->hosting->id,
            'title' => __('servermanager::email.hosting.subject', [
                'name' => $this->hosting->name
            ]),
            'message' => __('servermanager::email.hosting.message', [
                'hostingName' => $this->hosting->name,
                'domainName' => $this->hosting->domain_name,
                'daysUntilExpiry' => $this->hosting->daysUntilExpiry(),
                'renewalDate' => $this->hosting->renewal_date->format('d M Y'),
                'notificationDays' => $this->hosting->notification_days_before,
                'timeUnit' => $this->hosting->notification_time_unit
            ]),
            'type' => 'hosting_expiry',
            'hosting_id' => $this->hosting->id,
            'renewal_date' => $this->hosting->renewal_date,
        ];
    }
}
