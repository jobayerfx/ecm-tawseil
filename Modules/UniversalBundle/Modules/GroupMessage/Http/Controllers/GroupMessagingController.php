<?php

namespace Modules\GroupMessage\Http\Controllers;

use App\Models\User;
use App\Helper\Reply;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Support\Facades\Session;
use Modules\GroupMessage\Entities\Group;
use Modules\GroupMessage\Entities\Channel;
use Modules\GroupMessage\Entities\UserChat;
use Modules\GroupMessage\Entities\GroupMember;
use App\Http\Controllers\AccountBaseController;
use Modules\GroupMessage\Http\Requests\StorePrivateChatRequest;

class GroupMessagingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'groupmessage::app.groupMessage';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('groupmessage', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->messageSetting = message_setting();
        abort_403($this->messageSetting->allow_client_admin == 'no' && $this->messageSetting->allow_client_employee == 'no' && in_array('client', user_roles()));

        session()->forget('message_setting');
        session()->forget('pusher_settings');

        $tab = request('view');

        if (request()->type == 'group') {
            $tab = 'group';
        }

        if (request()->type == 'channel') {
            $tab = 'channel';
        }

        if (request()->type == 'client' || in_array('client', user_roles())) {
            $tab = 'client';
        }

        switch ($tab) {
            case 'group':
                $this->groups();
                break;
            case 'client':
                abort_403((in_array('employee', user_roles()) && $this->messageSetting->allow_client_employee == 'no') || (in_array('admin', user_roles()) && $this->messageSetting->allow_client_admin == 'no'));

                $this->clients();
                break;
            case 'channel':
                $this->channels();
                break;
            default:
                $this->private();
                break;
        }

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        $this->activeTab = $tab ?: 'private';

        // To show particular user's chat using it's user_id
        Session::flash('message_user_id', request()->user);

        return view('groupmessage::messages.index', $this->data);
    }

    public function channels()
    {
        $term = (request('term') != '') ? request('term') : null;
        $userLists = UserChat::channelUserListLatest(user()->id, $term);

        $messageIds = collect($userLists)->pluck('id');
        $allClientIds = User::allClients()->pluck('id');

        $this->userLists = UserChat::with(['fromUser' => function ($q) {
            $q->withCount(['unreadMessages']);
        }, 'toUser' => function ($q) {
            $q->withCount(['unreadMessages']);
        }, 'channel'])
            ->whereIn('id', $messageIds)->orderByDesc('id')->whereNotNull('channel_id')->whereNotIn('from', $allClientIds)->whereNotIn('to', $allClientIds)->groupBy('channel_id')->get();

        $channelId = request()->channelId;

        // Fetch channel list...
        $channelLists = Channel::select('id', 'owner_id', 'name', 'description');

        if ($channelId) {
            $channelLists = $channelLists->where('id', $channelId);
        }

        if (request('term')) {
            $term = '%' . request('term') . '%';
            $channelLists = $channelLists->where(function ($query) use ($term) {
                $query->where('channels.id', 'like', $term);
                $query->orWhere('channels.name', 'like', $term);
                $query->orWhere('channels.description', 'like', $term);
            });
        }

        $this->channelLists = $channelLists->orderBy('created_at', 'DESC')->get();
        $this->userLists = $channelLists;

        $tab = request('tab');
        $this->activeTab = $tab ?: 'channel';

        $this->view = 'groupmessage::messages.channels.channel_lists';
    }

    public function groups()
    {
        $term = (request('term') != '') ? request('term') : null;
        $userLists = UserChat::groupUserListLatest(user()->id, $term);

        $messageIds = collect($userLists)->pluck('id');
        $allClientIds = User::allClients()->pluck('id');

        $this->userLists = UserChat::with(['fromUser' => function ($q) {
            $q->withCount(['unreadMessages']);
        }, 'toUser' => function ($q) {
            $q->withCount(['unreadMessages']);
        }, 'group'])
            ->whereIn('id', $messageIds)->orderByDesc('id')->whereNotNull('group_id')->whereNotIn('from', $allClientIds)->whereNotIn('to', $allClientIds)->groupBy('group_id')->get();

        $isAdmin = user()->hasRole('admin');
        $userId = request()->userId;
        $groupId = request()->groupId;

        $currentUserGroup = GroupMember::where('user_id', user()->id)->groupBy('group_id')->pluck('group_id');

        // Fetch group list...
        $groupLists = Group::select('id', 'owner_id', 'name', 'description');

        if ($groupId) {
            $groupLists = $groupLists->where('id', $groupId);
        }

        if (request('term')) {
            $term = '%' . request('term') . '%';
            $groupLists = $groupLists->where(function ($query) use ($term) {
                $query->where('id', 'like', $term);
                $query->orWhere('name', 'like', $term);
                $query->orWhere('description', 'like', $term);
            });
        }

        $this->groupLists = $groupLists->whereIn('id', $currentUserGroup)->orderBy('created_at', 'DESC')->get();

        $this->userData = $groupLists;

        $tab = request('tab');
        $this->activeTab = $tab ?: 'group';

        $this->view = 'groupmessage::messages.groups.group_lists';
    }

    public function clients()
    {
        if (request()->clientId) {
            $this->client = User::findOrFail(request()->clientId);
        }

        if (request()->ajax() && request()->has('term')) {
            $term = (request('term') != '') ? request('term') : null;

            if (in_array('client', user_roles())) {
                $userLists = UserChat::clientListLatest(user()->id, $term);
            } else {
                $userLists = UserChat::clientListLatest(user()->id, $term);
            }
        } else {
            $userLists = UserChat::clientListLatest(user()->id, null);
        }

        $messageIds = collect($userLists)->pluck('id');

        if (in_array('client', user_roles())) {

            if ($this->messageSetting->allow_client_admin == 'yes' && $this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'no') {
                $this->employees = User::withRole('employee')
                    ->join('employee_details', 'employee_details.user_id', '=', 'users.id')
                    ->leftJoin('designations', 'employee_details.designation_id', '=', 'designations.id')
                    ->select('users.id', 'users.company_id', 'users.name', 'users.email', 'users.created_at', 'users.image', 'designations.name as designation_name', 'users.email_notifications', 'users.mobile', 'users.country_id', 'users.status', 'users.id')
                    ->whereNot('users.id', user()->id)->where('users.company_id', company()->id)
                    ->where('status', 'active')->orderBy('users.name')->groupBy('users.id')->get();
            } else if ($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'no') {
                $this->employees = User::allEmployees(null, true, null, company()->id);
            } else if ($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'yes') {
                $this->project_id = Project::where('client_id', user()->id)->pluck('id');
                $this->user_id = ProjectMember::whereIn('project_id', $this->project_id)->pluck('user_id');
                $this->employees = User::whereIn('id', $this->user_id)->get();
            } else if ($this->messageSetting->allow_client_admin == 'yes') {
                $this->employees = User::allAdmins($this->messageSetting->company->id);
            } else {
                $this->employees = [];
            }

            $allClientIds = $this->employees->pluck('id')->toArray();
        } else {
            $allClientIds = User::allClients()->pluck('id');
        }

        $this->userLists = UserChat::with(['fromUser' => function ($q) {
            $q->withCount(['unreadMessages']);
        }, 'toUser' => function ($q) {
            $q->withCount(['unreadMessages']);
        }])
            ->where('chat_type', 'client')
            ->whereIn('id', $messageIds)
            ->where(function ($query) use ($allClientIds) {
                $query->whereIn('from', $allClientIds)
                    ->orWhereIn('to', $allClientIds);
            })
            ->orderByDesc('id')
            ->get();


        $tab = request('tab');
        $this->activeTab = $tab ?: 'private';

        $this->view = 'groupmessage::messages.private.user_list';
    }

    public function private()
    {
        if (request()->clientId) {
            $this->client = User::findOrFail(request()->clientId);
        }

        if (request()->ajax() && request()->has('term')) {
            $term = (request('term') != '') ? request('term') : null;
            $userLists = UserChat::userListLatest(user()->id, $term);
        } else {
            $userLists = UserChat::userListLatest(user()->id, null);
        }

        $messageIds = collect($userLists)->pluck('id');
        $allClientIds = User::allClients()->pluck('id');

        $this->userLists = UserChat::with(['fromUser', 'toUser'])
            ->whereIn('id', $messageIds)->whereNotIn('from', $allClientIds)->whereNotIn('to', $allClientIds)->where('chat_type', 'private')->orderByDesc('id')->get();

        if (in_array('client', user_roles())) {
            if ($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'no') {
                $this->employees = User::allEmployees(null, true, null, company()->id);
            } else if ($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'yes') {
                $this->project_id = Project::where('client_id', user()->id)->pluck('id');
                $this->user_id = ProjectMember::whereIn('project_id', $this->project_id)->pluck('user_id');
                $this->employees = User::whereIn('id', $this->user_id)->get();
            } else if ($this->messageSetting->allow_client_admin == 'yes') {
                $this->employees = User::allAdmins($this->messageSetting->company->id);
            } else {
                $this->employees = [];
            }
        } else {
            $this->employees = User::allEmployees(null, true, 'all');
        }

        $userData = [];

        $usersData = $this->employees;

        foreach ($usersData as $user) {
            $url = route('employees.show', [$user->id]);
            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];
        }

        $this->userData = $userData;

        $tab = request('tab');
        $this->activeTab = $tab ?: 'private';

        $this->view = 'groupmessage::messages.private.user_list';
    }

    /**
     * Show the form for creating a new* resource.
     */
    public function create()
    {
        $this->type = request()->type;
        $this->messageSetting = message_setting();
        $this->project_id = Project::where('client_id', user()->id)->pluck('id');
        $this->employee_project_id = ProjectMember::where('user_id', user()->id)->pluck('project_id');
        $this->employee_user_id = ProjectMember::whereIn('project_id', $this->employee_project_id)->pluck('user_id');
        $this->employee_client_id = Project::whereIn('id', $this->employee_project_id)->pluck('client_id');

        $this->user_id = ProjectMember::whereIn('project_id', $this->project_id)->pluck('user_id');

        if ($this->type == 'private') {
            $this->employees = User::allEmployees($this->user->id, true, 'all');
        }

        if ($this->type == 'client') {
            $this->employees = User::allClients(null, true);
        }

        // This will return true if message button from projects overview button is clicked
        if (request()->clientId) {
            $this->clientId = request()->clientId;
            $this->client = User::findOrFail(request()->clientId);
        }

        if (in_array('client', user_roles())) {
            if ($this->messageSetting->allow_client_admin == 'yes' && $this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'no') {
                $this->employees = User::withRole('employee')
                    ->join('employee_details', 'employee_details.user_id', '=', 'users.id')
                    ->leftJoin('designations', 'employee_details.designation_id', '=', 'designations.id')
                    ->select('users.id', 'users.company_id', 'users.name', 'users.email', 'users.created_at', 'users.image', 'designations.name as designation_name', 'users.email_notifications', 'users.mobile', 'users.country_id', 'users.status', 'users.id')->whereNot('users.id', user()->id)->where('users.company_id', company()->id)->where('status', 'active')->orderBy('users.name')->groupBy('users.id')->get();
            } else if ($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'no') {
                $this->employees = User::allEmployees(null, true, null, company()->id);
            } else if ($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'yes') {
                $this->project_id = Project::where('client_id', user()->id)->pluck('id');
                $this->user_id = ProjectMember::whereIn('project_id', $this->project_id)->pluck('user_id');
                $this->employees = User::whereIn('id', $this->user_id)->get();
            } else if ($this->messageSetting->allow_client_admin == 'yes') {
                $this->employees = User::allAdmins($this->messageSetting->company->id);
            } else {
                $this->employees = [];
            }
        }

        $userData = [];

        $usersData = $this->employees;

        foreach ($usersData as $user) {

            $url = route('employees.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];
        }

        $this->userData = $userData;

        return view('groupmessage::messages.private.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePrivateChatRequest $request)
    {
        if ($request->user_type == 'client') {
            $receiverID = $request->client_id;
        } else {
            $receiverID = $request->user_id;
        }

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
        $message->chat_type         = $request->active_tab == 'client' ? 'client' : 'private';
        $message->notification_sent = 0;
        $message->save();

        $userLists = UserChat::userListLatest(user()->id, null);
        $messageIds = collect($userLists)->pluck('id');
        $allClientIds = User::allClients()->pluck('id');

        if ($request->active_tab == 'client') {
            $this->userLists = UserChat::with(['fromUser' => function ($q) {
                $q->withCount(['unreadMessages']);
            }, 'toUser' => function ($q) {
                $q->withCount(['unreadMessages']);
            }])
                ->whereIn('from', $allClientIds)->orWhereIn('to', $allClientIds)->where('chat_type', 'client')->groupBy('from')->orderByDesc('id')->get();
        } else {
            $this->userLists = UserChat::with('fromUser', 'toUser')->whereIn('id', $messageIds)->where('chat_type', 'private')->whereNotIn('from', $allClientIds)->whereNotIn('to', $allClientIds)->orderByDesc('id')->get();
        }

        $userList = view('groupmessage::messages.private.user_list', $this->data)->render();

        $this->chatDetails = UserChat::chatDetail($receiverID, user()->id);
        $messageList = view('groupmessage::messages.private.message_list', $this->data)->render();

        return Reply::dataOnly(['user_list' => $userList, 'message_list' => $messageList, 'message_id' => $message->id, 'receiver_id' => $receiverID, 'userName' => $message->toUser->name]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $this->chatDetails = UserChat::chatDetail($id, user()->id);

        // Mark messages read
        $updateData = ['message_seen' => 'yes'];
        UserChat::messageSeenUpdate($this->user->id, $id, $updateData);
        $this->unreadMessage = (request()->unreadMessageCount > 0) ? 0 : 1;
        $this->userId = $id;

        $view = view('groupmessage::messages.private.message_list', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'html' => $view, 'unreadMessages' => $this->unreadMessage, 'id' => $this->userId]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $userChats = UserChat::findOrFail($id);

        // Delete chat
        UserChat::destroy($id);

        // To reset chat-box if deleted chat is last one between them
        if (request()->fetchType == 'channel') {
            $chatDetails = UserChat::channelChatDetail($userChats->channel_id);
        } else if (request()->fetchType == 'group') {
            $chatDetails = UserChat::groupChatDetail($userChats->group_id);
        } else {
            $chatDetails = UserChat::chatDetail($userChats->from, $userChats->to);
        }

        return Reply::successWithData(__('messages.deleteSuccess'), ['chat_details' => $chatDetails]);
    }

    public function fetchUserMessages($receiverID)
    {
        $this->chatDetails = UserChat::chatDetail($receiverID, user()->id);

        $messageList = view('groupmessage::messages.private.message_list', $this->data)->render();

        return Reply::dataOnly(['message_list' => $messageList]);
    }

    public function validateModule($message)
    {
        if ($message == '') {
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

    public function fetchUserListView()
    {
        $this->messageSetting = message_setting();
        $userLists = UserChat::userListLatest(user()->id, null);
        $messageIds = collect($userLists)->pluck('id');

        if (in_array('client', user_roles()) || request()->type == 'client') {

            if ($this->messageSetting->allow_client_admin == 'yes' && $this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'no') {
                $employees = User::allEmployees(null, true, null, company()->id);
                $admins = User::allAdmins(company()->id);

                $this->employees = $employees->merge($admins);
            } else if ($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'no') {
                $this->employees = User::allEmployees(null, true, null, company()->id);
            } else if ($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'yes') {
                $this->project_id = Project::where('client_id', user()->id)->pluck('id');
                $this->user_id = ProjectMember::whereIn('project_id', $this->project_id)->pluck('user_id');
                $this->employees = User::whereIn('id', $this->user_id)->get();
            } else if ($this->messageSetting->allow_client_admin == 'yes') {
                $this->employees = User::allAdmins($this->messageSetting->company->id);
            } else {
                $this->employees = [];
            }

            $allClientIds = $this->employees->pluck('id')->toArray();
        } else {
            $allClientIds = User::allClients()->pluck('id');
        }

        $userLists = UserChat::with(['fromUser' => function ($q) {
            $q->withCount(['unreadMessages']);
        }, 'toUser' => function ($q) {
            $q->withCount(['unreadMessages']);
        }])->whereIn('id', $messageIds);

        if (request()->type == 'client' || in_array('client', user_roles())) {
            $userLists->where('chat_type', 'client');
        } else {
            $userLists->where('chat_type', 'private');
        }

        $this->userLists = $userLists->where(function ($query) use ($allClientIds) {
            $query->whereNotIn('from', $allClientIds)
                ->orWhereNotIn('to', $allClientIds);
        })->orderByDesc('id')->get();

        // To show particular user's chat using it's user_id
        Session::flash('message_user_id', request()->user);
        $userList = view('groupmessage::messages.private.user_list', $this->data)->render();

        return Reply::dataOnly(['user_list' => $userList]);
    }
}
