<?php

namespace Modules\Purchase\DataTables;

use Carbon\Carbon;
use App\Models\InventorySummary;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Modules\Purchase\Entities\PurchaseItem;
use Modules\Purchase\Entities\PurchaseOrder;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Modules\Purchase\Entities\PurchaseStockAdjustment;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class InventorySummaryDataTable extends BaseDataTable
{

    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('item_name', fn($row) => $row->product->name)
            ->addColumn('sku', fn($row) => $row->product->sku)
            ->addColumn('quantity_ordered', function ($row) {
                $value = $row->product->count_purchase_order ?? null;
                return ($value === null || $value === '') ? '0' : (string)(int)$value;
            })
            ->addColumn('quantity_in', function ($row) {
                $value = $row->product->opening_stock ?? null;
                return ($value === null || $value === '') ? '0' : (string)(int)$value;
            })
            ->addColumn('quantity_out', function ($row) {
                $value = $row->product->count_quantity_out ?? null;
                return ($value === null || $value === '') ? '0' : (string)(int)$value;
            })
            ->addColumn('stock_on_hand', fn($row) => $row->net_quantity)
            ->addColumn('committed_stock', function ($row) {
                if (!$row->product->invoiceItem) {
                    return '0';
                }
                $value = $row->product->invoiceItem->sum('quantity');
                return ($value === null || $value === '') ? '0' : (string)(int)$value;
            })
            ->addColumn('available_for_sale', fn($row) => $row->net_quantity - $row->product->invoiceItem->sum('quantity'));

    }

    /**
     * Get the query source of dataTable.
     */
    public function query(PurchaseStockAdjustment $model)
    {
        $request = $this->request();
        $model = $model->with(['product.invoiceItem' => function ($item) {
            $item->whereHas('invoice', function ($q) {
                $q->where('status', 'unpaid');
            });
        }]);

        // Check if product exists before proceeding
        $model = $model->whereHas('product', function ($query) {
            $query->whereNotNull('id'); // Ensure product exists
        });

        $model = $model->with([
            'product' => function ($q) {
                $q->withCount(['orderItem as count_purchase_order', 'orderItem as count_quantity_out' => function ($purchaseItemQuery) {
                    $purchaseItemQuery->whereHas('purchaseOrder', function ($purchaseOrderQuery) {
                        $purchaseOrderQuery->where('delivery_status', 'delivered');
                    });
                }]);
            },'product.tax']);


        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = Carbon::createFromFormat($this->company->date_format, $request->startDate)->toDateString();

            if (!is_null($startDate)) {
                $model = $model->where(DB::raw('DATE(purchase_stock_adjustments.`created_at`)'), '>=', $startDate);
            }
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = Carbon::createFromFormat($this->company->date_format, $request->endDate)->toDateString();

            if (!is_null($endDate)) {
                $model = $model->where(function ($query) use ($endDate) {
                    $query->where(DB::raw('DATE(purchase_stock_adjustments.`created_at`)'), '<=', $endDate);
                });
            }
        }

        return $model;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->setBuilder('inventorysummary-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["inventorysummary-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("#inventorysummary-table .select-picker").selectpicker();
                }',
            ]);

        if (canDataTableExport()) {
            $dataTable->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
        }

        return $dataTable;
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            __('purchase::modules.reports.itemName') => ['data' => 'item_name', 'name' => 'item_name', 'exportable' => true],
            __('purchase::modules.reports.sku') => ['data' => 'sku', 'name' => 'sku', 'exportable' => true],
            __('purchase::modules.reports.quantityOrdered') => ['data' => 'quantity_ordered', 'name' => 'quantity_ordered', 'exportable' => true],
            __('purchase::modules.reports.quantityIn') => ['data' => 'quantity_in', 'name' => 'quantity_in', 'exportable' => true],
            __('purchase::modules.reports.quantityOut') => ['data' => 'quantity_out', 'name' => 'quantity_out', 'exportable' => true],
            __('purchase::modules.reports.stockOnHand') => ['data' => 'stock_on_hand', 'name' => 'stock_on_hand', 'exportable' => true],
            __('purchase::modules.reports.committed_Stock') => ['data' => 'committed_stock', 'name' => 'committed_stock', 'exportable' => true],
            __('purchase::modules.reports.availableForSale') => ['data' => 'available_for_sale', 'name' => 'available_for_sale', 'exportable' => true],

        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'InventorySummary_' . date('YmdHis');
    }

}
