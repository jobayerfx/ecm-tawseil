<?php

namespace Modules\ServerManager\Notifications;

use Illuminate\Bus\Queueable;
use App\Notifications\BaseNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\ServerManager\Entities\ServerDomain;

class DomainExpiringNotification extends BaseNotification
{
    use Queueable;

    private $domain;

    /**
     * Create a new notification instance.
     */
    public function __construct(ServerDomain $domain)
    {
        $this->domain = $domain;
        $this->company = $this->domain->company;
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
        $daysUntilExpiry = $this->domain->daysUntilExpiry();
        $notificationDays = $this->domain->notification_days_before;
        $timeUnit = $this->domain->notification_time_unit;

        $emailContent = parent::build()
        ->subject(__('servermanager::email.domain.subject'))
        ->greeting(__('servermanager::email.domain.greeting', ['name' => $notifiable->name]))
        ->line(__('servermanager::email.domain.message', [
            'domainName' => $this->domain->domain_name,
            'daysUntilExpiry' => $daysUntilExpiry,
            'expiryDate' => $this->domain->expiry_date->format($this->company->date_format),
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
            'id' => $this->domain->id,
            'title' => __('servermanager::email.domain.title', [
                'name' => $this->domain->domain_name
            ]),
            'message' => __('servermanager::email.domain.message', [
                'domainName' => $this->domain->domain_name,
                'daysUntilExpiry' => $this->domain->daysUntilExpiry(),
                'expiryDate' => $this->domain->expiry_date->format('y-m-d'),
                'notificationDays' => $this->domain->notification_days_before,
                'timeUnit' => $this->domain->notification_time_unit
            ]),
            'type' => 'domain_expiry',
            'domain_id' => $this->domain->id,
            'expiry_date' => $this->domain->expiry_date,
        ];
    }
}
