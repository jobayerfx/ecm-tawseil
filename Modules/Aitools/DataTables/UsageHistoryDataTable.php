<?php

namespace Modules\Aitools\DataTables;

use App\DataTables\BaseDataTable;
use Modules\Aitools\Entities\AiToolsUsageHistory;
use Yajra\DataTables\Html\Column;

class UsageHistoryDataTable extends BaseDataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $datatables = datatables()->eloquent($query);

        $datatables->editColumn('created_at', function ($row) {
            return $row->created_at->format('M d, Y H:i');
        });

        $datatables->editColumn('user_id', function ($row) {
            return $row->user->name ?? 'N/A';
        });

        $datatables->editColumn('model', function ($row) {
            return '<span class="badge badge-secondary">' . $row->model . '</span>';
        });

        $datatables->editColumn('prompt', function ($row) {
            return '<span class="text-truncate d-inline-block" style="max-width: 200px;" title="' . htmlspecialchars($row->prompt) . '">' 
                . \Str::limit($row->prompt, 50) 
                . '</span>';
        });

        $datatables->editColumn('prompt_tokens', function ($row) {
            return number_format($row->prompt_tokens);
        });

        $datatables->editColumn('completion_tokens', function ($row) {
            return number_format($row->completion_tokens);
        });

        $datatables->editColumn('total_tokens', function ($row) {
            return '<strong>' . number_format($row->total_tokens) . '</strong>';
        });

        $datatables->addIndexColumn();
        $datatables->rawColumns(['model', 'prompt', 'total_tokens']);
        $datatables->setRowId(fn($row) => 'row-' . $row->id);

        return $datatables;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \Modules\Aitools\Entities\AiToolsUsageHistory $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AiToolsUsageHistory $model)
    {
        $companyId = company()->id;
        
        return $model->newQuery()
            ->where('company_id', $companyId)
            ->with('user');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->setBuilder('usage-history-table', 0);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::make('created_at')
                ->title(__('aitools::app.dateTime'))
                ->width(150)
                ->orderable(true)
                ->searchable(false),
            Column::make('user_id')
                ->title(__('aitools::app.user'))
                ->width(150)
                ->orderable(true)
                ->searchable(true)
                ->data('user_id')
                ->name('user.name'),
            Column::make('model')
                ->title(__('aitools::app.model'))
                ->width(150)
                ->orderable(true)
                ->searchable(true),
            Column::make('prompt')
                ->title(__('aitools::app.prompt'))
                ->width(200)
                ->orderable(false)
                ->searchable(true),
            Column::make('prompt_tokens')
                ->title(__('aitools::app.promptTokens'))
                ->width(120)
                ->orderable(true)
                ->searchable(false),
            Column::make('completion_tokens')
                ->title(__('aitools::app.completionTokens'))
                ->width(150)
                ->orderable(true)
                ->searchable(false),
            Column::make('total_tokens')
                ->title(__('aitools::app.totalTokens'))
                ->width(120)
                ->orderable(true)
                ->searchable(false),
        ];
    }
}

