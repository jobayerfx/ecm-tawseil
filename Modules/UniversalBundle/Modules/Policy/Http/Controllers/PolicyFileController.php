<?php

namespace Modules\Policy\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Traits\IconTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Modules\Policy\Entities\Policy;
use Modules\Policy\Entities\PolicyFile;

class PolicyFileController extends Controller
{

    use IconTrait;

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $policyId = $request->policy_id;

        if ($request->hasFile('file')) {

            foreach ($request->file as $fileData) {
                $file = new PolicyFile();
                $file->policy_id = $policyId;

                $filename = Files::uploadLocalOrS3($fileData, PolicyFile::FILE_PATH.'/' . $policyId);

                $file->filename = $fileData->getClientOriginalName();
                $file->hashname = $filename;
                $file->size = $fileData->getSize();
                $file->added_by = user()->id;
                $file->save();
            }

            return Reply::success(__('messages.recordSaved'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $file = PolicyFile::findOrFail($id);
        $this->policy = Policy::findorFail($file->policy_id);
        Files::deleteFile($file->hashname, PolicyFile::FILE_PATH . '/' . $file->policy_id);

        PolicyFile::destroy($id);

        $this->files = PolicyFile::where('policy_id', $file->policy_id)->orderByDesc('id')->get();
        $view = view('policy::policy.files.show', $this->data)->render();

        return Reply::successWithData(__('messages.deleteSuccess'), ['view' => $view]);

    }

    public function download($id)
    {
        if (request()->type == 'only-file') {

            $file = Policy::withTrashed()->whereRaw('md5(id) = ?', $id)->firstOrFail();
            return download_local_s3($file, Policy::FILE_PATH . '/' . $file->filename);
        }

        $file = PolicyFile::whereRaw('md5(id) = ?', $id)->firstOrFail();
        return download_local_s3($file, 'policy-files/' . $file->policy_id . '/' . $file->hashname);

    }

}
