<?php

namespace Point\Framework\Traits;

trait RequestApprovalTrait
{

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function isFormulirNull($request)
    {
        if (count($request->get('formulir_id')) == 0) {
            gritter_success('please select at least one form to request an approval');
            return true;
        }

        return false;
    }

    public function getUserForTimeline($request, $user_id)
    {
        if ($request->method() == 'POST') {
            return auth()->user()->id;
        }

        return $user_id;
    }

    public function getRedirectLink($request, $formulir)
    {
        if ($request->method() == 'POST') {
            return redirect()->back();
        }

        return '' . view('framework::app.approval-status')->with('formulir', $formulir);
    }
}
