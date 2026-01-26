<?php

namespace Modules\GroupMessage\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\GroupMessage\Entities\Group;

class NewGroupJoin
{

    use Dispatchable, SerializesModels;

    public $group;
    public $memberIds;

    /**
     * Create a new event instance.
     */
    public function __construct(Group $group, $memberIds)
    {
        $this->group = $group;
        $this->memberIds = $memberIds;
    }

}
