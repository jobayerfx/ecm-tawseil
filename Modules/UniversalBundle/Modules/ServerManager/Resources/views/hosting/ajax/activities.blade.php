<div class="card bg-white border-0 b-shadow-4 mt-3">
    <div class="card-header bg-white  border-bottom-grey  justify-content-between p-20">
        <h3 class="card-title">
            <i class="fas fa-history"></i> Recent Activity
        </h3>
        <div class="card-tools">
            <a href="#" class="btn btn-sm btn-outline-primary" onclick="showLogs()">
                View All
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="timeline">
            @forelse($hosting->logs()->orderBy('created_at', 'desc')->limit(5)->get() as $log)
                <div class="time-label">
                    <span class="bg-blue">{{ $log->created_at->format('M d, Y') }}</span>
                </div>
                <div>
                    <i class="fas fa-info bg-blue"></i>
                    <div class="timeline-item">
                        <span class="time">
                            <i class="fas fa-clock"></i> {{ $log->created_at->format('H:i') }}
                        </span>
                        <h3 class="timeline-header">{{ $log->action }}</h3>
                        <div class="timeline-body">
                            {{ $log->description }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center p-3">
                    <i class="fas fa-info-circle text-muted fa-2x"></i>
                    <p class="text-muted mt-2">No activity logs found</p>
                </div>
            @endforelse
        </div>
    </div>
</div>