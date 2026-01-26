<?php

namespace Modules\GroupMessage\Http\Controllers;

use App\Models\User;
use App\Helper\Reply;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Modules\GroupMessage\Entities\Group;
use Modules\GroupMessage\Entities\UserChat;
use Modules\GroupMessage\Events\NewGroupJoin;
use Modules\GroupMessage\Entities\GroupMember;
use App\Http\Controllers\AccountBaseController;
use Modules\GroupMessage\Http\Requests\StoreGroupChatRequest;
use Modules\GroupMessage\Http\Requests\StoreNewGroupRequest;

class GroupController extends AccountBaseController
{

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->messageSetting = message_setting();
        $this->employees = User::allEmployees(null, true, null);

        return view('groupmessage::messages.groups.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNewGroupRequest $request)
    {
        DB::beginTransaction();

        $group = new Group();
        $group->company_id = company()->id;
        $group->name = $request->name;
        $group->owner_id = user()->id;
        $group->description = $request->description;
        $group->save();

        if ($request->members) {
            $memberIds = [];

            foreach ($request->members as $member) {
                $groupMember = new GroupMember();
                $groupMember->group_id = $group->id;
                $groupMember->user_id = $member;
                $groupMember->save();

                $memberIds[] = $member;
            }

            $users = User::whereIn('id', $memberIds)->get();
            event(new NewGroupJoin($group, $users));
        }

        DB::commit();

        $userLists = UserChat::groupUserListLatest(user()->id, null);
        $messageIds = collect($userLists)->pluck('id');
        $allClientIds = User::allClients()->pluck('id');

        $this->userLists = UserChat::with(['fromUser', 'toUser', 'group'])->whereIn('id', $messageIds)->where('chat_type', 'group')->whereNotNull('group_id')->whereNotIn('from', $allClientIds)->whereNotIn('to', $allClientIds)
        ->groupBy('group_id')->orderByDesc('id')->get();

        $currentUserGroup = GroupMember::where('user_id', user()->id)->groupBy('group_id')->pluck('group_id');
        $this->groupLists = Group::whereIn('id', $currentUserGroup)->orderBy('created_at', 'DESC')->get();

        $userList = view('groupmessage::messages.groups.group_lists', $this->data)->render();

        $this->chatDetails = UserChat::groupChatDetail($group->id);
        $messageList = view('groupmessage::messages.groups.message_list', $this->data)->render();

        return Reply::dataOnly(['user_list' => $userList, 'message_list' => $messageList, 'receiver_id' => $group->id, 'userName' => $group->name]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $this->messageSetting = message_setting();
        $this->employees = User::allEmployees(null, true, null, company()->id);

        $this->group = Group::with('members')->findOrFail($id);

        $attendeeArray = [];

        foreach ($this->group->members as $item) {
            $attendeeArray[] = $item->id;
        }

        $this->attendeeArray = $attendeeArray;

        return view('groupmessage::messages.groups.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->messageSetting = message_setting();
        $this->employees = User::allEmployees(null, true, null, company()->id);

        $this->group = Group::with('members')->findOrFail($id);

        $attendeeArray = [];

        foreach ($this->group->members as $item) {
            $attendeeArray[] = $item->id;
        }

        $this->attendeeArray = $attendeeArray;

        return view('groupmessage::messages.groups.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreNewGroupRequest $request, $id)
    {
        $receiverID = user()->id;

        DB::beginTransaction();
        $group = Group::with('members')->findOrFail($id);

        $existingMembers = $group->members->pluck('id')->toArray();
        $newMembers = array_diff($request->members, $existingMembers);

        $group->company_id = company()->id;
        $group->name = $request->name;
        $group->description = $request->description;
        $group->save();

        $group->groupMembers()->delete();

        if (isset($request->members)) {
            foreach ($request->members as $member) {
                $groupMember = new GroupMember();
                $groupMember->group_id = $group->id;
                $groupMember->user_id = $member;
                $groupMember->save();
            }

            if (!empty($newMembers)) {
                $users = User::whereIn('id', $newMembers)->get();
                event(new NewGroupJoin($group, $users));
            }
        }

        DB::commit();

        $userLists = UserChat::groupUserListLatest(user()->id, null);
        $messageIds = collect($userLists)->pluck('id');
        $allClientIds = User::allClients()->pluck('id');

        $this->userLists = UserChat::with(['fromUser', 'toUser', 'group'])->whereIn('id', $messageIds)->where('chat_type', 'group')->whereNotNull('group_id')->whereNotIn('from', $allClientIds)->whereNotIn('to', $allClientIds)->groupBy('group_id')->orderByDesc('id')->get();

        $currentUserGroup = GroupMember::where('user_id', user()->id)->groupBy('group_id')->pluck('group_id');
        $this->groupLists = Group::whereIn('id', $currentUserGroup)->orderBy('created_at', 'DESC')->get();

        $userList = view('groupmessage::messages.groups.group_lists', $this->data)->render();

        $this->chatDetails = UserChat::groupChatDetail($group->id);
        $messageList = view('groupmessage::messages.groups.message_list', $this->data)->render();

        return Reply::dataOnly(['user_list' => $userList, 'group_name' => $group->name, 'message_list' => $messageList, 'receiver_id' => $receiverID]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $notifyData = 'Modules\GroupMessage\Notifications\NotifyGroupJoinee';

        Notification::where('type', $notifyData)
            ->whereNull('read_at')
            ->where('data', 'like', '%"group_id":' . $id . '%')
            ->delete();

        UserChat::where('group_id', $id)->delete();
        Group::destroy($id);

        $currentUserGroup = GroupMember::where('user_id', user()->id)->groupBy('group_id')->pluck('group_id');
        $this->groupLists = Group::whereIn('id', $currentUserGroup)->orderBy('created_at', 'DESC')->get();
        $userList = view('groupmessage::messages.groups.group_lists', $this->data)->render();

        return Reply::successWithData(__('messages.deleteSuccess'), ['chat_details' => $userList]);
    }

    public function removeMember(Request $request, $id)
    {
        $memberId = $request->input('member_id');
        $group = Group::find($id);

        if ($group) {
            $group->members()->detach($memberId);

            return Reply::success(__('groupmessage::messages.memberRemoveSuccess'));
        }

        return Reply::error(__('groupmessage::messages.memberRemoveFailed'));
    }

    public function storeGroupMessage(StoreGroupChatRequest $request)
    {
        $groupID = $request->group_id;
        $receiverID = user()->id;
        $message = $request->message;

        if($request->types == 'chat')
        {
            $validateModule = $this->validateModule($message);

            if($validateModule['status'] == false)
            {
                return Reply::error($validateModule ['message'] );
            }
        }

        $message = new UserChat();
        $message->company_id        = company()->id;
        $message->message           = $request->message;
        $message->user_one          = user()->id;
        $message->user_id           = $receiverID;
        $message->from              = user()->id;
        $message->to                = $receiverID;
        $message->chat_type         = 'group';
        $message->group_id          = $groupID;
        $message->notification_sent = 0;
        $message->save();

        $currentUserGroup = GroupMember::where('user_id', user()->id)->groupBy('group_id')->pluck('group_id');
        $this->groupLists = Group::whereIn('id', $currentUserGroup)->orderBy('created_at', 'DESC')->get();
        $userList = view('groupmessage::messages.groups.group_lists', $this->data)->render();

        $this->chatDetails = UserChat::groupChatDetail($groupID);
        $messageList = view('groupmessage::messages.groups.message_list', $this->data)->render();

        return Reply::dataOnly(['user_list' => $userList, 'message_list' => $messageList, 'message_id' => $message->id, 'receiver_id' => $groupID, 'userName' => $message->group->name]);
    }

    public function showGroupChat($id)
    {
        $this->chatDetails = UserChat::groupChatDetail($id);
        // Mark messages read
        $updateData = ['message_seen' => 'yes'];
        UserChat::groupMessageSeenUpdate($id, $updateData);
        $this->unreadMessage = (request()->unreadMessageCount > 0) ? 0 : 1;
        $this->userId = $id;

        $group = Group::with('members')->find($id);

        $members = '<div class="position-relative">';

        foreach ($group->members as $key => $member) {
            if ($key < 4) {
                $img = '<img data-toggle="tooltip" height="25" width="25" data-original-title="' . $member->name . '" src="' . $member->image_url . '">';
                $position = $key > 0 ? 'position-absolute ml-1' : '';
                $members .= '<div class="taskEmployeeImg rounded-circle ' . $position . '" style="left: ' . ($key * 13) . 'px"><a data-group-id="'.$group->id.'" href="javascript:;" class="text-dark f-10 view-group">' . $img . '</a></div>';
            }
        }

        if (count($group->members) > 4) {
            $members .= '<div class="taskEmployeeImg more-user-count text-center rounded-circle bg-amt-grey position-absolute" style="left:  52px"><a data-group-id="'.$group->id.'" href="javascript:;" class="text-dark f-10 view-group">+' . (count($group->members) - 4) . '</a></div>';
        }

        $members .= '</div>';

        $view = view('groupmessage::messages.groups.message_list', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'html' => $view, 'members' => $members, 'unreadMessages' => $this->unreadMessage, 'id' => $this->userId]);
    }

    public function fetchGroupListView()
    {
        $currentUserGroup = GroupMember::where('user_id', user()->id)->groupBy('group_id')->pluck('group_id');
        $this->groupLists = Group::whereIn('id', $currentUserGroup)->orderBy('created_at', 'DESC')->get();

        $userList = view('groupmessage::messages.groups.group_lists', $this->data)->render();
        Session::flash('message_user_id', request()->user);

        return Reply::dataOnly(['user_list' => $userList]);
    }

    public function fetchGroupMessages($groupId)
    {
        $this->chatDetails = UserChat::groupChatDetail($groupId);
        $messageList = view('groupmessage::messages.groups.message_list', $this->data)->render();

        return Reply::dataOnly(['message_list' => $messageList]);
    }

    public function validateModule($message)
    {
        if($message == '')
        {
            return [
                'status' => false,
                'message' => __('messages.fileMessage'),
            ];
        }
        else{
            return [
                'status' => true,
            ];
        }
    }

    public function fetchGroupmembers($id)
    {
        $group = Group::with('members')->findOrFail($id);

        $members = '<div class="position-relative">';

        foreach ($group->members as $key => $member) {
            if ($key < 4) {
                $img = '<img data-toggle="tooltip" height="25" width="25" data-original-title="' . $member->name . '" src="' . $member->image_url . '">';
                $position = $key > 0 ? 'position-absolute ml-1' : '';
                $members .= '<div class="taskEmployeeImg rounded-circle ' . $position . '" style="left: ' . ($key * 13) . 'px"><a data-group-id="'.$group->id.'" href="javascript:;" class="text-dark f-10 view-group">' . $img . '</a></div>';
            }
        }

        if (count($group->members) > 4) {
            $members .= '<div class="taskEmployeeImg more-user-count text-center rounded-circle bg-amt-grey position-absolute" style="left:  52px"><a data-group-id="'.$group->id.'" href="javascript:;" class="text-dark f-10 view-group">+' . (count($group->members) - 4) . '</a></div>';
        }

        $members .= '</div>';

        return Reply::dataOnly(['members' => $members]);
    }

}
