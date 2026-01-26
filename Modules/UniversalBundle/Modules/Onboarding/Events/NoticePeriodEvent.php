<?php

namespace Modules\Onboarding\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NoticePeriodEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $user;
    public $password;
    public $notice_period_start_date;

    public function __construct(User $user, $password, $notice_period_start_date)
    {
        $this->user = $user;
        $this->password = $password;
        $this->notice_period_start_date = $notice_period_start_date; // Set the notice period start date

    }
    
}
