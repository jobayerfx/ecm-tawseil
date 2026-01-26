<?php

namespace Modules\Onboarding\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Notifications\BaseNotification;
use Modules\Onboarding\Entities\OnboardingNotificationSetting;

class TaskApprovalNotification extends BaseNotification
{
    use Queueable;

    protected $task;
    protected $status;
    protected $rejectionReason;
    protected $approver;
    protected $emailSetting;
    /**
     * Create a new notification instance.
     */
    public function __construct($task, $status, $approver, $rejectionReason = null, $company)
    {
        $this->task = $task;
        $this->status = $status;
        $this->approver = $approver;
        $this->rejectionReason = $rejectionReason;
        $this->emailSetting = OnboardingNotificationSetting::where('company_id', $company->id)->where('slug', 'onboard-notification')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $via = ['database'];

        if ($this->emailSetting->send_email == 'yes') {
            array_push($via, 'mail');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $taskName = $this->task->onboardingTask->title;
        $approverName = $this->approver->name;

        if ($this->status === 'approved') {
            return (new MailMessage)
                ->subject('Task Approved: ' . $taskName)
                ->greeting('Hello ' . $notifiable->name . '!')
                ->line('Your task "' . $taskName . '" has been approved by ' . $approverName . '.')
                ->line('The task is now marked as completed.')
                ->action('View Task', url('/account/employees/' . $notifiable->id))
                ->line('Thank you for your contribution!');
        } else {
            return (new MailMessage)
                ->subject('Task Rejected: ' . $taskName)
                ->greeting('Hello ' . $notifiable->name . '!')
                ->line('Your task "' . $taskName . '" has been rejected by ' . $approverName . '.')
                ->line('Reason: ' . $this->rejectionReason)
                ->line('Please review and resubmit the task.')
                ->action('View Task', url('/account/employees/' . $notifiable->id))
                ->line('Please make the necessary corrections and submit again.');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_name' => $this->task->onboardingTask->title,
            'status' => $this->status,
            'approver_name' => $this->approver->name,
            'rejection_reason' => $this->rejectionReason,
            'type' => $this->task->type,
        ];
    }
}
