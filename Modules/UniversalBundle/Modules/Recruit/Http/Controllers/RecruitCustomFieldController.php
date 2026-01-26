<?php

namespace Modules\Recruit\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Modules\Recruit\Entities\RecruitCustomQuestion;
use Modules\Recruit\Entities\RecruitSetting;
use Modules\Recruit\Http\Requests\RecruitSetting\StoreRecruitCustomField;
use Modules\Recruit\Entities\RecruitCustomField;
use Modules\Recruit\Entities\RecruitCustomFieldGroup;
use Modules\Recruit\Http\Requests\RecruitSetting\UpdateRecruitCustomField;

class RecruitCustomFieldController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->activeSettingMenu = 'recruit_settings';
        $this->middleware(function ($request, $next) {
            abort_403(! in_array(RecruitSetting::MODULE_NAME, $this->user->modules));

            return $next($request);
        });
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function create()
    {
        $this->types = ['text', 'number', 'password', 'textarea', 'select', 'radio', 'date', 'checkbox', 'file'];
        $this->customFieldGroups = RecruitCustomFieldGroup::all();
        return view('recruit::recruit-setting.custom-field.create-field-modal', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Renderable
     */
    public function store(StoreRecruitCustomField $request)
    {
        $name = RecruitCustomField::generateUniqueSlug($request->get('label'), $request->category_id);
        $group = [
            'fields' => [
                [
                    'category_id' => $request->category_id,
                    'name' => $name,
                    'label' => $request->get('label'),
                    'type' => $request->get('type'),
                    'required' => $request->get('required'),
                    'values' => $request->get('value'),
                    'export' => $request->get('export'),
                    'visible' => $request->get('visible'),
                ]
            ],

        ];

        $this->addCustomField($group);

        return Reply::success('messages.recordSaved');
    }

    private function addCustomField($group)
    {
        foreach ($group['fields'] as $field) {
            $insertData = [
                'custom_field_group_id' => $field['category_id'],
                'label' => $field['label'],
                'name' => $field['name'],
                'type' => $field['type'],
                'export' => $field['export'],
                'visible' => $field['visible'],
                'company_id' => company()->id,
            ];

            if (isset($field['required']) && (in_array($field['required'], ['yes', 'on', 1]))) {
                $insertData['required'] = 'yes';
            } else {
                $insertData['required'] = 'no';
            }

            // Single value should be stored as text (multi value JSON encoded)
            if (isset($field['values'])) {
                if (is_array($field['values'])) {
                    $insertData['values'] = \GuzzleHttp\json_encode($field['values']);
                } else {
                    $insertData['values'] = $field['values'];
                }
            }

            // Use insert instead of Eloquent create to avoid timestamps
            \DB::table('recruit_custom_fields')->insert($insertData);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function edit($id)
    {
        $this->field = RecruitCustomField::with('fieldGroup')->findOrfail($id);
        $this->types = ['text', 'number', 'password', 'textarea', 'select', 'radio', 'date', 'checkbox', 'file'];
        $this->field->values = json_decode($this->field->values);

        return view('recruit::recruit-setting.custom-field.edit-field-modal', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Renderable
     */
    public function update(UpdateRecruitCustomField $request, $id)
    {
        $field = RecruitCustomField::findOrFail($id);

        $name = RecruitCustomField::generateUniqueSlug($request->label, $field->custom_field_group_id);

        $updateData = [
            'label' => $request->label,
            'name' => $name,
            'values' => json_encode($request->value),
            'required' => $request->required,
            'export' => $request->export,
            'visible' => $request->visible,
        ];

        // Use query builder to avoid Eloquent's timestamps
        \DB::table('recruit_custom_fields')
            ->where('id', $id)
            ->update($updateData);

        return Reply::success('messages.updateSuccess');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function destroy($id)
    {
        RecruitCustomField::destroy($id);
        return Reply::success(__('recruit::messages.CustomDeleted'));
    }

    public function changeQuestionStatus(Request $request)
    {
        $question = RecruitCustomQuestion::findOrFail($request->questionId);
        $question->status = lcfirst($request->status);
        $question->update();

        return Reply::success(__('messages.updateSuccess'));
    }
}