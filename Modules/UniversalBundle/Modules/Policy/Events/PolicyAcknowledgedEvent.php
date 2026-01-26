<?php

namespace Modules\Policy\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Policy\Entities\Policy;

class PolicyAcknowledgedEvent
{
    use Dispatchable, SerializesModels;

    public $policy;
    public $acknowledgeBy;
    public $notifyUsers;

    /**
     * Create a new event instance.
     */
    public function __construct(Policy $policy, $acknowledgeBy, $notifyUsers)
    {
        $this->policy = $policy;
        $this->notifyUsers = $notifyUsers;
        $this->acknowledgeBy = $acknowledgeBy;
    }

}
