<div class="col-md-12 p-0">
    @forelse($groupLists as $user)
        <x-groupmessage::group-lists :message="$user"/>
    @empty
        <x-cards.no-record icon="comment-alt" :message="__('messages.noConversation')" />
    @endforelse
</div>
