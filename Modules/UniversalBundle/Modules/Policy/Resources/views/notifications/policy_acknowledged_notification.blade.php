@php
    $notificationUser = App\Models\User::where('id', $notification->data['user_id'])->first();
    $policy = Modules\Policy\Entities\Policy::where('id', $notification->data['policy_id'])->first();
@endphp

<x-cards.notification :notification="$notification"
                    :link="route('policy.show', $policy->id)"
                    :image="$notificationUser->image_url"
                    :title="__('policy::email.PolicyAcknowlwdged.subject')"
                    :text="$policy->title"
                    :time="$notification->created_at"/>
