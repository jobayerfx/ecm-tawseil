<?php

namespace Modules\Recruit\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruitCustomField extends BaseModel
{
    use HasFactory, HasCompany;

    protected $guarded = ['id'];

    protected $fillable = [];

    public static function boot()
    {
        parent::boot();
    }

    public static function generateUniqueSlug($label, $categoryId)
    {
        $slug = str_slug($label);
        $count = RecruitCustomField::where('name', $slug)->where('custom_field_group_id', $categoryId)->count();

        if ($count > 0) {
            $i = 1;

            while (RecruitCustomField::where('name', $slug . '-' . $i)->where('custom_field_group_id', $categoryId)->count() > 0) {
                $i++;
            }

            $slug .= '-' . $i;
        }

        return $slug;
    }

    public function fieldGroup(): BelongsTo
    {
        return $this->belongsTo(RecruitCustomFieldGroup::class, 'custom_field_group_id');
    }

    public function customFieldGroup(): HasOne
    {
        return $this->hasOne(RecruitCustomFieldGroup::class, 'custom_field_group_id');
    }

    public static function exportCustomFields($category)
    {
        $recruitCustomFieldsGroupsId = RecruitCustomFieldGroup::where('category', $category::CUSTOM_FIELD_MODEL)->select('id')->first();
        $recruitCustomFields = collect();

        if ($recruitCustomFieldsGroupsId) {
            $recruitCustomFields = RecruitCustomField::where('custom_field_group_id', $recruitCustomFieldsGroupsId->id)->where(function ($q) {
                return $q->where('export', 1)->orWhere('visible', 'true');
            })->get();
        }

        return $recruitCustomFields;
    }

    public static function customFieldData($datatables, $category, $relation = null)
    {
        $recruitCustomFields = RecruitCustomField::exportCustomFields($category);
        $recruitCustomFieldNames = [];
        $recruitCustomFieldsId = $recruitCustomFields->pluck('id');
        $fieldData = DB::table('recruit_custom_fields_data')->where('category', $category)->whereIn('custom_field_id', $recruitCustomFieldsId)->select('id', 'custom_field_id', 'category_id', 'value')->get();
        foreach ($recruitCustomFields as $recruitCustomField) {
            $datatables->addColumn($recruitCustomField->name, function ($row) use ($fieldData, $recruitCustomField, $relation) {

                $finalData = $fieldData->filter(function ($value) use ($recruitCustomField, $row, $relation) {
                    return ($value->custom_field_id == $recruitCustomField->id) && ($value->category_id == ($relation ? $row?->{$relation}?->id : $row->id));
                })->first();

                if ($recruitCustomField->type == 'select') {
                    $data = $recruitCustomField->values;
                    $data = json_decode($data); // string to array

                    return $finalData ? (($finalData->value >= 0 && $finalData->value != null) ? $data[$finalData->value] : '--') : '--';
                }

                if ($recruitCustomField->type == 'date') {
                    $dateValue = $finalData?->value;
                    if (!empty($dateValue)) {
                        try {
                            $formattedDate = \Carbon\Carbon::parse($dateValue)->translatedFormat(company()->date_format);
                            return $formattedDate;
                        } catch (\Exception $e) {
                            return '<span class="text-danger">' . __('Invalid Date') . '</span>';
                        }
                    }
                    return '--';
                }

                if ($recruitCustomField->type == 'file') {
                    return $finalData ? '<a href="' . asset_url_local_s3('custom_fields/' . $finalData->value) . '" target="__blank" class="text-dark-grey">' . __('app.storageSetting.viewFile') . '</a>' : '--';
                }

                return $finalData ? $finalData->value : '--';
            });

            // This will use for datatable raw column
            if ($recruitCustomField->type == 'file') {
                $recruitCustomFieldNames[] = $recruitCustomField->name;
            }

        }

        return $recruitCustomFieldNames;
    }

}
