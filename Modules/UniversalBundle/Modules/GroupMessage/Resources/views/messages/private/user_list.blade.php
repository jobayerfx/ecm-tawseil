<div class="col-md-12 p-0">
    @forelse($userLists as $user)
        <x-groupmessage::user-list :message="$user"/>
    @empty
        <x-cards.no-record icon="comment-alt" :message="__('messages.noConversation')" />
    @endforelse
</div>
