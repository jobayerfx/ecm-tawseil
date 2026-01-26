<div class="row">
    <div class="col-12 mt-3">
        <x-cards.data :title="__('servermanager::app.dashboard.recentActivities')" :value="__('servermanager::app.dashboard.recentActivitiesDesc')">
                                @if($recentLogs->count() > 0)
                <div class="d-flex flex-wrap p-20">
                    @foreach($recentLogs as $log)
                        <div class="card file-card w-100 rounded-0 border-0 comment p-2">
                            <div class="card-horizontal">
                                <div class="card-img my-1 ml-0">
                                    <img src="{{ $log->performedBy->image_url ?? asset('img/avatar.png') }}" alt="{{ $log->performedBy->name ?? 'System' }}">
                                </div>
                                <div class="card-body border-0 pl-0 py-1 mb-2">
                                    <div class="d-flex flex-grow-1">
                                        <h4 class="card-title f-12 font-weight-normal text-dark mr-3 mb-1">
                                            {{-- <span class="badge {{ $log->getActionBadgeClass() }}">
                                                {{ ucfirst($log->action) }}
                                            </span> --}}
                                            {{ $log->description . ' ' . __('app.by')}}
                                            @if($log->performedBy)
                                                <a href="{{ route('employees.show', $log->performedBy->id) }}" class="text-darkest-grey">{{ $log->performedBy->name }}</a>
                                            @else
                                                <span class="text-darkest-grey">System</span>
                                            @endif
                                        </h4>
                                    </div>
                                    <div class="card-text f-11 text-lightest text-justify">
                                        @if($log->entity_type === 'hosting' && isset($log->entity) && isset($log->entity->status))
                                            <span class="badge {{ $log->entity->status === 'active' ? 'badge-success' : ($log->entity->status === 'suspended' ? 'badge-danger' : 'badge-warning') }}">
                                                {{ ucfirst($log->entity->status) }}
                                            </span>
                                        @elseif($log->entity_type === 'domain' && isset($log->entity) && isset($log->entity->status))
                                            <span class="badge {{ $log->entity->status === 'active' ? 'badge-success' : ($log->entity->status === 'expired' ? 'badge-danger' : 'badge-warning') }}">
                                                {{ ucfirst($log->entity->status) }}
                                            </span>
                                        @endif
                                        <span class="f-11 text-lightest">
                                            {{ $log->created_at->timezone(company()->timezone)->translatedFormat(company()->date_format .' '. company()->time_format) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fa fa-history fa-3x text-muted mb-3"></i>
                    <h5>@lang('servermanager::app.dashboard.noRecentActivities')</h5>
                </div>
            @endif
        </x-cards.data>
    </div>
</div>