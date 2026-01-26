<?php

namespace Modules\GroupMessage\Views\Components;

use Illuminate\View\Component;

class UserList extends Component
{

    public $message;
    public $totalUnreadMsgs;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($message)
    {
        $this->message = $message;

        if ($message->chat_type == 'client') {
            $this->totalUnreadMsgs = $message->unreadMessagesForClient()->count();
        }
        else {
            $this->totalUnreadMsgs = $message->unreadMessagesForUser()->count();
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('groupmessage::components.user-list');
    }

}
