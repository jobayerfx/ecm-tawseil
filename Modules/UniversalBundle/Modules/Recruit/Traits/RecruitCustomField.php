<?php

namespace Modules\Recruit\Traits;

use Carbon\Carbon;
use App\Helper\Files;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use Modules\Recruit\Entities\RecruitCustomField as EntitiesRecruitCustomField;
use Modules\Recruit\Entities\RecruitCustomFieldGroup;

trait RecruitCustomField
{

    public $category;
    private $extraData;
    public $custom_fields;
    public $custom_fields_data;

    /** Get company ID for current object
     * @return int Returns current object's company id
     */

    private function getCategoryName()
    {
        $category = new ReflectionClass($this);
        $this->category = $category;

        return $this->category->getName();
    }

    public function updateCustomField($group)
    {

        // Add Custom Fields for this group
        foreach ($group['fields'] as $field) {
            $insertData = [
                'custom_field_group_id' => 1,
                'label' => $field['label'],
                'name' => $field['name'],
                'type' => $field['type']
            ];

            if (isset($field['required']) && (in_array(strtolower($field['required']), ['yes', 'on', 1]))) {
                $insertData['required'] = 'yes';
            }
            else {
                $insertData['required'] = 'no';
            }

            // Single value should be stored as text (multi value JSON encoded)
            if (isset($field['value'])) {
                if (is_array($field['value'])) {
                    $insertData['values'] = json_encode($field['value']);

                }
                else {
                    $insertData['values'] = $field['value'];
                }
            }

            DB::table('custom_fields')->insert($insertData);
        }
    }

    public function getCustomFieldGroups($fields = false)
    {
        $recruitCustomFieldGroup = RecruitCustomFieldGroup::where('category', $this->getCategoryName());

        $recruitCustomFieldGroup = $recruitCustomFieldGroup->when(method_exists($this, 'company'), function ($query) {
            return $query->where('company_id', $this->company_id ?: company()->id);
        })->first();

        if ($fields && $recruitCustomFieldGroup) {
            $recruitCustomFieldGroup->load(['customField'])->append(['fields']);
        }

        return $recruitCustomFieldGroup;
    }

    public function getCustomFieldGroupsWithFields()
    {
        return $this->getCustomFieldGroups(true);
    }

    public function getCustomFieldsData()
    {

        $categoryId = $this->id;

        // Get custom fields for this modal
        /** @var \Illuminate\Database\Eloquent\Collection $data */
        $data = DB::table('recruit_custom_fields_data')
            ->rightJoin('recruit_custom_fields', function ($query) use ($categoryId) {
                $query->on('recruit_custom_fields_data.custom_field_id', '=', 'recruit_custom_fields.id');
                $query->on('category_id', '=', DB::raw($categoryId));
            })
            ->rightJoin('recruit_custom_field_groups', 'recruit_custom_fields.custom_field_group_id', '=', 'recruit_custom_field_groups.id')
            ->select('recruit_custom_fields.id', DB::raw('CONCAT("field_", recruit_custom_fields.id) as field_id'), 'recruit_custom_fields.type', 'recruit_custom_fields_data.value')
            ->where('recruit_custom_field_groups.category', $this->getCategoryName())
            ->get();

        $data = collect($data);

        // Convert collection to an associative array
        // of format ['field_{id}' => $value]
        $result = $data->pluck('value', 'field_id');

        return $result;
    }

    public function updateCustomFieldData($fields, $company_id = null)
    {
        foreach ($fields as $key => $value) {

            $idarray = explode('_', $key);
            $id = end($idarray);

            $fieldType = EntitiesRecruitCustomField::findOrFail($id);
            $company = $company_id ? Company::findOrFail($company_id) : company();

            $value = ($fieldType == 'date') ? Carbon::createFromFormat($company->date_format, $value)->format('Y-m-d') : $value;
            $value = ($fieldType == 'file' && !is_string($value) && !is_null($value)) ? Files::uploadLocalOrS3($value, 'custom_fields') : $value;

            // Find is entry exists
            $entry = DB::table('recruit_custom_fields_data')
                ->where('category', $this->getCategoryName())
                ->where('category_id', $this->id)
                ->where('custom_field_id', $id)
                ->first();

            if ($entry) {
                if ($fieldType == 'file' && (!is_null($entry->value) && $entry->value != $value)) {
                    Files::deleteFile($entry->value, 'custom_fields');
                }

                // Update entry
                DB::table('recruit_custom_fields_data')
                    ->where('category', $this->getCategoryName())
                    ->where('category_id', $this->id)
                    ->where('custom_field_id', $id)
                    ->update(['value' => $value]);
            }
            else {
                DB::table('recruit_custom_fields_data')
                    ->insert([
                        'category' => $this->getCategoryName(),
                        'category_id' => $this->id,
                        'custom_field_id' => $id,
                        'value' => (!is_null($value)) ? $value : ''
                    ]);
            }
        }
    }

    public function getExtrasAttribute()
    {
        if ($this->extraData == null) {
            $this->extraData = $this->getCustomFieldGroupsWithFields();
        }

        return $this->extraData;
    }

    public function withCustomFields()
    {
        $this->custom_fields = $this->getCustomFieldGroupsWithFields();
        $this->custom_fields_data = $this->getCustomFieldsData();

        return $this;
    }

}
