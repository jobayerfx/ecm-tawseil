@php
    $notificationUser = App\Models\User::where('id', $notification->data['user_id'])->first();
    $group = Modules\GroupMessage\Entities\Group::where('id', $notification->data['group_id'])->first();
    $url = route('group-messaging.index').'?view=group';
@endphp

<x-cards.notification :notification="$notification"
                    :link="$url"
                    :image="$notificationUser->image_url"
                    :title="__('groupmessage::email.GroupJoin.subject')"
                    :text="$group->name"
                    :time="$notification->created_at"/>
