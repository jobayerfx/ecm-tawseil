<?php

namespace Modules\Policy\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Policy\Entities\Policy;

class PolicyPublishedEvent
{

    use Dispatchable, SerializesModels;

    public $policy;
    public $notifyUsers;

    /**
     * Create a new event instance.
     */
    public function __construct(Policy $policy, $notifyUsers)
    {
        $this->policy = $policy;
        $this->notifyUsers = $notifyUsers;
    }

}
