<?php

namespace Modules\GroupMessage\Http\Controllers;

use App\Models\User;
use App\Helper\Reply;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Modules\GroupMessage\Entities\Channel;
use Modules\GroupMessage\Entities\UserChat;
use App\Http\Controllers\AccountBaseController;
use Modules\GroupMessage\Http\Requests\StoreChannelChatRequest;
use Modules\GroupMessage\Http\Requests\StoreNewChannelRequest;

class ChannelController extends AccountBaseController
{

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('groupmessage::messages.channels.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNewChannelRequest $request)
    {
        DB::beginTransaction();

        $channel = new Channel();
        $channel->company_id = company()->id;
        $channel->name = $request->name;
        $channel->owner_id = user()->id;
        $channel->description = $request->description;
        $channel->save();

        DB::commit();

        $userLists = UserChat::channelUserListLatest(user()->id, null);
        $messageIds = collect($userLists)->pluck('id');
        $allClientIds = User::allClients()->pluck('id');

        $this->userLists = UserChat::with(['fromUser', 'toUser', 'channel'])->whereIn('id', $messageIds)->where('chat_type', 'channel')->whereNotNull('channel_id')->whereNotIn('from', $allClientIds)->whereNotIn('to', $allClientIds)->groupBy('channel_id')->orderByDesc('id')->get();

        $this->channelLists = Channel::orderBy('created_at', 'DESC')->get();
        $userList = view('groupmessage::messages.channels.channel_lists', $this->data)->render();

        $this->chatDetails = UserChat::channelChatDetail($channel->id);
        $messageList = view('groupmessage::messages.channels.message_list', $this->data)->render();

        return Reply::dataOnly(['user_list' => $userList, 'message_list' => $messageList, 'receiver_id' => $channel->id, 'userName' => $channel->name]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $this->channel = Channel::findOrFail($id);

        return view('groupmessage::messages.channels.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->channel = Channel::findOrFail($id);

        return view('groupmessage::messages.channels.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreNewChannelRequest $request, $id)
    {
        $receiverID = user()->id;

        $channel = Channel::findOrFail($id);
        $channel->company_id = company()->id;
        $channel->name = $request->name;
        $channel->description = $request->description;
        $channel->save();

        $userLists = UserChat::groupUserListLatest(user()->id, null);
        $messageIds = collect($userLists)->pluck('id');
        $allClientIds = User::allClients()->pluck('id');

        $this->userLists = UserChat::with(['fromUser', 'toUser', 'channel'])->whereIn('id', $messageIds)->where('chat_type', 'channel')->whereNotNull('channel_id')->whereNotIn('from', $allClientIds)->whereNotIn('to', $allClientIds)->groupBy('channel_id')->orderByDesc('id')->get();

        $this->channelLists = Channel::orderBy('created_at', 'DESC')->get();
        $userList = view('groupmessage::messages.channels.channel_lists', $this->data)->render();

        $this->chatDetails = UserChat::channelChatDetail($channel->id);
        $messageList = view('groupmessage::messages.channels.message_list', $this->data)->render();

        return Reply::dataOnly(['user_list' => $userList, 'message_list' => $messageList, 'receiver_id' => $receiverID]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        UserChat::where('channel_id', $id)->delete();
        Channel::destroy($id);

        $this->channelLists = Channel::orderBy('created_at', 'DESC')->get();
        $userList = view('groupmessage::messages.channels.channel_lists', $this->data)->render();

        return Reply::successWithData(__('messages.deleteSuccess'), ['chat_details' => $userList]);
    }

    public function validateModule($message)
    {
        if ($message == '' || $message == '<p><br></p>') {
            return [
                'status' => false,
                'message' => __('messages.fileMessage'),
            ];
        } else {
            return [
                'status' => true,
            ];
        }
    }

    public function storeChannelMessage(StoreChannelChatRequest $request)
    {
        $channelID = $request->channel_id;
        $channel = Channel::findOrFail($channelID);
        $receiverID = user()->id;
        $message = $request->message;

        if ($request->types == 'chat') {
            $validateModule = $this->validateModule($message);

            if ($validateModule['status'] == false) {
                return Reply::error($validateModule['message']);
            }
        }

        $message = new UserChat();
        $message->company_id        = company()->id;
        $message->message           = $request->message;
        $message->user_one          = user()->id;
        $message->user_id           = $receiverID;
        $message->from              = user()->id;
        $message->to                = $receiverID;
        $message->chat_type         = 'channel';
        $message->channel_id        = $channelID;
        $message->notification_sent = 0;
        $message->save();

        $this->channelLists = Channel::orderBy('created_at', 'DESC')->get();
        $userList = view('groupmessage::messages.channels.channel_lists', $this->data)->render();

        $this->chatDetails = UserChat::channelChatDetail($channelID);
        $messageList = view('groupmessage::messages.channels.message_list', $this->data)->render();

        return Reply::dataOnly(['user_list' => $userList, 'message_list' => $messageList, 'message_id' => $message->id, 'receiver_id' => $channelID, 'userName' => $channel->name]);
    }

    public function fetchChannelListView()
    {
        $this->channelLists = Channel::orderBy('created_at', 'DESC')->get();

        $userList = view('groupmessage::messages.channels.channel_lists', $this->data)->render();
        Session::flash('message_user_id', request()->user);

        return Reply::dataOnly(['user_list' => $userList]);
    }

    public function showChannelChat($id)
    {
        $this->chatDetails = UserChat::channelChatDetail($id);
        // Mark messages read
        $updateData = ['message_seen' => 'yes'];
        UserChat::channelMessageSeenUpdate($id, $updateData);
        $this->unreadMessage = (request()->unreadMessageCount > 0) ? 0 : 1;
        $this->userId = $id;

        $view = view('groupmessage::messages.channels.message_list', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'html' => $view, 'unreadMessages' => $this->unreadMessage, 'id' => $this->userId]);
    }

    public function fetchChannelMessages($channelId)
    {
        $this->chatDetails = UserChat::channelChatDetail($channelId);
        $messageList = view('groupmessage::messages.channels.message_list', $this->data)->render();

        return Reply::dataOnly(['message_list' => $messageList]);
    }
}
