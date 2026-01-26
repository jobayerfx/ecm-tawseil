<div class="col-md-12 p-0">
    @forelse($channelLists as $user)
        <x-groupmessage::channel-lists :message="$user" :type="'channel'"/>
    @empty
        <x-cards.no-record icon="comment-alt" :message="__('messages.noConversation')" />
    @endforelse
</div>
