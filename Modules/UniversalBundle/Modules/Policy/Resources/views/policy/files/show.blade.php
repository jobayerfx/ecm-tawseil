@forelse($policy->files as $file)
    <x-file-card :fileName="$file->filename" :dateAdded="$file->created_at->diffForHumans()">
        @if ($file->icon == 'images')
            <img src="{{ $file->file_url }}">
        @else
            <i class="fa {{ $file->icon }} text-lightest"></i>
        @endif
            <x-slot name="action">
                <div class="ml-auto dropdown file-action">
                    <button class="p-0 rounded btn btn-lg f-14 text-lightest text-capitalize dropdown-toggle"
                        type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-ellipsis-h"></i>
                    </button>

                    <div class="p-0 rounded dropdown-menu dropdown-menu-right border-grey b-shadow-4"
                        aria-labelledby="dropdownMenuLink" tabindex="0">
                            @if ($file->icon = 'images')
                                <a class="px-3 pt-3 cursor-pointer d-block text-dark-grey f-13 " target="_blank"
                                    href="{{ $file->file_url }}">@lang('app.view')</a>
                            @endif
                            <a class="px-3 py-3 cursor-pointer d-block text-dark-grey f-13 "
                                href="{{ route('event-files.download', md5($file->id)) }}">@lang('app.download')</a>

                            <a class="px-3 pb-3 cursor-pointer d-block text-dark-grey f-13 delete-file"
                                data-row-id="{{ $file->id }}" href="javascript:;">@lang('app.delete')</a>
                    </div>
                </div>
            </x-slot>

    </x-file-card>
@empty
<div class="col-md-12" id="no-files">
    <x-cards.no-record :message="__('messages.noFileUploaded')" icon="file" />
</div>
@endforelse
