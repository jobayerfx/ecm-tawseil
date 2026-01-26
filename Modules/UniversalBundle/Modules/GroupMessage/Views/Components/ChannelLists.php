<?php

namespace Modules\GroupMessage\Views\Components;

use App\Models\UserChat;
use Illuminate\View\Component;

class ChannelLists extends Component
{

    public $message;
    public $unreadMessageCount;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($message)
    {
        $this->message = $message;

        $this->unreadMessageCount = UserChat::where('channel_id', $message->id)
            ->where('message_seen', 'no')->whereNot('from', user()->id)->whereNot('to', user()->id)->where('chat_type', 'channel')->count();

    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('groupmessage::components.channel-lists');
    }

}
