<?php

namespace Modules\Onboarding\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\BaseNotification;
use App\Models\User;
use Carbon\Carbon;
use Modules\Onboarding\Entities\OnboardingNotificationSetting;

class TaskSubmissionNotification extends BaseNotification
{
    use Queueable;

    protected $task;
    protected $employee;
    protected $company;
    protected $emailSetting;
    /**
     * Create a new notification instance.
     */
    public function __construct($task, $employee, $company)
    {
        $this->task = $task;
        $this->employee = $employee;
        $this->company = $company;
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
        $employeeName = $this->employee->name;
        $taskType = ucfirst($this->task->type);

        // Ensure submitted_on is properly formatted
        $submittedOn = $this->task->submitted_on;
        if (is_string($submittedOn)) {
            $submittedOn = Carbon::parse($submittedOn);
        }

        return (new MailMessage)
            ->subject('New Task Submission: ' . $taskName)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new ' . $taskType . ' task has been submitted for your review.')
            ->line('Task: "' . $taskName . '"')
            ->line('Submitted by: ' . $employeeName)
            ->line('Submitted on: ' . $submittedOn->format($this->company->date_format))
            ->action('Review Task', url('/account/employees/' . $this->employee->id))
            ->line('Please review and approve or reject this task.');
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
            'employee_name' => $this->employee->name,
            'type' => $this->task->type,
            'submitted_on' => $this->task->submitted_on,
        ];
    }
}
