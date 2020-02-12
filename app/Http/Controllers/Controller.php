<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    protected $request;
    protected $mid;
    protected $country;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->mid = Auth::guard('admin')->user()->id;
//        $country_id = Country::where('admin_user_id', $this->mid)->value('id');
        $this->country = Country::where('admin_user_id', $this->mid)->value('id');
    }

    /**
     * 求验证信息
     * @param $input
     * @return bool|MessageBag
     */
    public function validationMessages($input)
    {
        $failedValidators = [];
        foreach ($this->form()->builder()->fields() as $field) {
            if (!$validator = $field->getValidator($input)) {
                continue;
            }
            if (($validator instanceof Validator) && !$validator->passes()) {
                $failedValidators[] = $validator;
            }
        }

        $message = $this->mergeValidationMessages($failedValidators);
        return $message->any() ? $message : false;
    }

    protected function mergeValidationMessages($validators)
    {
        $messageBag = new MessageBag();
        foreach ($validators as $validator) {
            $messageBag = $messageBag->merge($validator->messages());
        }
        return $messageBag;
    }

}
