<?php

namespace Modules\Recruit\Http\Requests\Front;

use App\Http\Requests\CoreRequest;
use App\Models\Company;
use App\Scopes\CompanyScope;
use Illuminate\Support\Str;
use Modules\Recruit\Entities\RecruitCustomQuestion;
use Modules\Recruit\Entities\RecruitJob;
use Modules\Recruit\Entities\RecruitSetting;
use Modules\Recruit\Rules\CheckApplication;

class FrontJobApplication extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function rules()
    {
        $setting = Company::withoutGlobalScope(CompanyScope::class)->where('hash', request()->companyHash)->first();
        $jobId = RecruitJob::where('id', request()->job_id)->first();
        $recruitSetting = RecruitSetting::where('company_id', $setting->id)->select('google_recaptcha_status')->first();

        $data = [];

        $data = [
            'full_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
        ];

        if (request()->term_agreement == 'off') {
            $data['term_agreement'] = '';
        } else {
            $data['term_agreement'] = 'required';
        }

        if ($jobId->is_resume_require) {
            $data['resume'] = ['required', 'mimes:jpeg,pdf,png,jpg,doc,docx,xls,xlsx,ppt,pptx'];
        }

        if ($jobId->is_photo_require) {
            $data['photo'] = ['required', 'mimes:jpeg,bmp,png,jpg,webp'];
        }

        if ($jobId->is_dob_require) {

            $dateFormat = $setting->date_format;

            if (Str::contains($dateFormat, ' ')) {
                $dateFormat = '"' . $dateFormat . '"';
            }

            $data['date_of_birth'] = 'required|date_format:' . $dateFormat;
        }

        if ($jobId->is_gender_require) {
            $data['gender'] = 'required';
        }

        if (request()->get('answer')) {
            $fields = request()->get('answer');

            foreach ($fields as $key => $value) {

                $customField = RecruitCustomQuestion::findOrFail($key);

                if ($customField->required == 'yes' && (is_null($value) || $value == '')) {
                    $data['answer[' . $key . ']'] = 'required';
                }
            }
        }

        if ($recruitSetting->google_recaptcha_status == 'active') {
            if (global_setting()->google_recaptcha_status == 'active' && (global_setting()->google_recaptcha_v2_status == 'active')) {
                $rules['g-recaptcha-response'] = 'required';
            }
        }

        return $data;
    }

    public function authorize()
    {
        return true;
    }

    public function attributes()
    {
        $attributes = [];

        if (request()->get('answer')) {
            $fields = request()->get('answer');

            foreach ($fields as $key => $value) {

                $customField = RecruitCustomQuestion::findOrFail($key);

                if ($customField->required == 'yes') {
                    $attributes['answer[' . $key . ']'] = $customField->question;
                }
            }
        }

        return $attributes;
    }
}
