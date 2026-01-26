<div class="card bg-white border-0 b-shadow-4 mt-3">
    <div class="card-header bg-white  border-bottom-grey  justify-content-between p-20">
        <h3 class="card-title">
            <i class="fas fa-chart-bar"></i> Statistics
        </h3>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-6">
                <div class="info-box bg-light">
                    <span class="info-box-icon bg-primary">
                        <i class="fas fa-calendar"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Days Active</span>
                        <span class="info-box-number">
                            {{ $hosting->purchase_date ? $hosting->purchase_date->diffInDays(now()) : 0 }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="info-box bg-light">
                    <span class="info-box-icon bg-success">
                        <i class="fas fa-dollar-sign"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Cost</span>
                        <span class="info-box-number">
                            ${{ $hosting->monthly_cost ? number_format($hosting->monthly_cost * ($hosting->purchase_date ? $hosting->purchase_date->diffInMonths(now()) : 0), 2) : '0.00' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>