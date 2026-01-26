<?php

namespace Modules\Recruit\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecruitCustomFieldGroup extends BaseModel
{
    use HasFactory, HasCompany;

    const ALL_FIELDS = [
        ['name' => 'Job', 'category' => 'Modules\Recruit\Entities\RecruitJob']
    ];

    public function customField(): HasMany
    {
        return $this->HasMany(RecruitCustomField::class, 'custom_field_group_id');
    }

    public static function customFieldsDataMerge($category)
    {
        $recruitCustomFields = RecruitCustomField::exportCustomFields($category);

        $recruitCustomFieldsDataMerge = [];

        foreach ($recruitCustomFields as $recruitCustomField) {
            $recruitCustomFieldsData = [
                $recruitCustomField->name => [
                    'data' => $recruitCustomField->name,
                    'name' => $recruitCustomField->name,
                    'title' => str($recruitCustomField->label)->__toString(),
                    'visible' => (!is_null($recruitCustomField['visible'])) ? $recruitCustomField['visible'] : false,
                    'exportable' => (!is_null($recruitCustomField['export'])) ? $recruitCustomField['export'] : false,
                    'orderable' => false,
                ]
            ];

            $recruitCustomFieldsDataMerge = array_merge($recruitCustomFieldsDataMerge, $recruitCustomFieldsData);
        }

        return $recruitCustomFieldsDataMerge;
    }

    /**
     * Get the custom field group's name.
     */
    protected function fields(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->customField->map(function ($item) {
                    if (in_array($item->type, ['select', 'radio'])) {
                        $item->values = json_decode($item->values);

                        return $item;
                    }

                    return $item;
                });
            },
        );
    }
}