<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@lang('policy::app.policyDetails') - {{ $policy->title }}</title>
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ $company->favicon_url }}">
    <meta name="theme-color" content="#ffffff">

    <style>
        .bg-grey {
            background-color: #F2F4F7;
        }

        .bg-white {
            background-color: #fff;
        }

        .border-radius-25 {
            border-radius: 0.25rem;
        }

        .p-25 {
            padding: 1.25rem;
        }

        .f-21 {
            font-size: 50px;
        }

        .title{
            margin-top: 10px;
            font-weight: bold;
        }
        .policy-title{
            font-size: 35px;
            margin-top: 10px;
        }
        .date{
            font-weight: bold;
            margin-top: 100px;

        }
        .page-break{
            page-break-before: always;
        }
        .center{
            text-align: center;
        }
        .cover-sheet {
            position: absolute;

            top: 0px;
            left: 0px;
            right: 0px;
            bottom: 0px;

            overflow: hidden;
            margin: 0;
            padding: 0;
            }
    </style>

</head>
<body class="content-wrapper">
    <div class="date">{{__('policy::app.effectiveDate').': '. $policy->date ? $policy->date->format(company()->date_format) : '--'}}</div>
    <hr class="mt-1 mb-1">
    <p>{{__('policy::app.policyTerms')}}</p>
    <br>
    @if ($policy->employeeAcknowledge->isNotEmpty())
        <div style="text-align: right;">
            @if (!is_null($policy->employeeAcknowledge[0]->signature_file))
                <img src="{{ $policy->employeeAcknowledge[0]->employee_signature }}" style="width: 200px;">
                <h4 class="name" style="margin-bottom: 20px;">@lang('policy::app.employeeSignature')</h4>
            @endif
            <p>{{__('policy::app.acknowledgedOn').':- '. $policy->employeeAcknowledge[0]->acknowledged_on->format(company()->date_format) }}
            <br><br>
            {{__('app.name').':- '. $policy->employeeAcknowledge[0]->users->name }}
            </p>
        </div>
    @endif

</body>
</html>
