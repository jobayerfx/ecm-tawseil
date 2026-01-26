<style>
    .message-container {
        display: -webkit-box;
        -webkit-line-clamp: 2; /* Limit to 2 lines */
        -webkit-box-orient: vertical;
        overflow: hidden;
        max-height: 4em; /* Adjust based on font-size */
    }
    .custom-icon {
        font-size: 25px;
        color: #c8ced5;
        cursor: pointer;
    }
</style>

<div class="card rounded-0 border-top-0 border-left-0 border-right-0 user_list_box position-relative" id="user-no-{{ $message->id }}">
    <a @class([
        'tablinks',
        'show-user-messages',
        'unread-message' => $unreadMessageCount > 0,
    ]) href="javascript:;" data-name="{{ $message->name }}" data-user-id="{{ $message->id }}"
        data-group-id="{{ $message->id }}" data-channel-id="{{ $message->id }}" data-is-member="{{ $message->is_member }}" data-type="channel" data-unread-message-count="{{ $unreadMessageCount }}"
        style="display: block; width: 100%; text-decoration: none; color: inherit;">
        <div class="card-horizontal user-message d-flex align-items-center">
            <div class="mx-2 p-2 my-2 border rounded-circle bg-accent-color">
                <i class="fa fa-hashtag custom-icon" aria-hidden="true"></i>
            </div>
            <div class="card-body border-0 pl-0 w-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="pt-3">
                        <h4 class="card-title f-12 f-w-500 text-dark-grey mb-0">{{ $message->name }}</h4>
                    </div>
                </div>
                <div @class([
                    'card-text',
                    'f-11',
                    'text-lightest',
                    'd-flex',
                    'justify-content-between',
                    'message-mention',
                    'text-dark' => $unreadMessageCount > 0,
                    'font-weight-bold' => $unreadMessageCount > 0,
                ])>
                    <div class="message-container">{{ strlen(strip_tags($message->description)) > 40 ? substr(strip_tags($message->description), 0, 40) . '...' : strip_tags($message->description) }}</div>

                    @if ($unreadMessageCount > 0)
                        <div>
                            <span class="badge badge-primary ml-1 unread-count">{{ $unreadMessageCount }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </a>

    <!-- Actions Menu (Placed outside the <a> tag) -->
    <div class="dropdown position-absolute" style="top: 10px; right: 10px; padding-top: 25px;">
        <a href="javascript:;" class="text-dark-grey" id="dropdownMenuButton-channel-{{ $message->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="event.preventDefault(); event.stopPropagation();">
            <i class="fas fa-ellipsis-v"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0" aria-labelledby="dropdownMenuButton-channel-{{ $message->id }}" tabindex="0">
            <a class="dropdown-item view-channel" href="javascript:;" data-channel-id="{{ $message->id }}" onclick="event.stopPropagation();">@lang('app.view')</a>
            @if (user()->id == $message->owner_id)
                <a class="dropdown-item edit-channel" href="javascript:;" data-channel-id="{{ $message->id }}" onclick="event.stopPropagation();">@lang('app.edit')</a>
                <a class="dropdown-item delete-channel" href="javascript:;" data-channel-id="{{ $message->id }}" onclick="event.stopPropagation();">@lang('app.delete')</a>
            @endif
        </div>
    </div>
</div>
<!-- card end -->
