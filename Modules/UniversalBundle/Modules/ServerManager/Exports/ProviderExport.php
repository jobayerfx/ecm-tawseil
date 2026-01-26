<?php

namespace Modules\ServerManager\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\ServerManager\Entities\ServerProvider;
use Carbon\Carbon;

class ProviderExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $providers;
    protected $startDate;
    protected $endDate;
    protected $exportAll;

    public function __construct($startDate = null, $endDate = null, $exportAll = false)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->exportAll = $exportAll;

        $query = ServerProvider::where('company_id', company()->id)
            ->with(['createdBy', 'updatedBy']);

        if (!$this->exportAll && $this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate->startOfDay(), $this->endDate->endOfDay()]);
        }

        $this->providers = $query->get();
    }

    public function collection()
    {
        return $this->providers;
    }

    public function headings(): array
    {
        return [
            'ID',
            __('servermanager::app.provider.name'),
            __('servermanager::app.provider.url'),
            __('servermanager::app.provider.type'),
            __('servermanager::app.provider.description'),
            __('app.status'),
            __('app.createdBy'),
            __('app.createdAt'),
            __('app.updatedAt'),
        ];
    }

    public function map($provider): array
    {
        return [
            $provider->id,
            $provider->name,
            $provider->url,
            ucfirst($provider->type),
            $provider->description,
            ucfirst($provider->status),
            $provider->createdBy ? $provider->createdBy->name : '',
            $provider->created_at ? $provider->created_at->format(company()->date_format) : '',
            $provider->updated_at ? $provider->updated_at->format(company()->date_format) : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
