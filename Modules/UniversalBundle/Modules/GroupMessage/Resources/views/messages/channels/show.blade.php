<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang("groupmessage::app.channelDetail")</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-sm-12">
            <div class="card-body">
                <x-cards.data-row :label="__('app.name')" :value="$channel->name"
                    html="true" />

                <x-cards.data-row :label="__('groupmessage::app.createdOn')" :value="$channel->created_at ? $channel->created_at->translatedFormat(company()->date_format) : '--'" />

                <x-cards.data-row :label="__('app.description')" :value="$channel->description"
                    html="true" />
            </div>
        </div>
    </div>
</div>
<div class="modal-footer mr-2">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
</div>
