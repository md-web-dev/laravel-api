<?php

namespace App\Http\Requests\Soundblock\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Client;

class AddPayMethod extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return (true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return ([
            "paymethod_account" => "required|string",
            "paymethod_type" => "required|string|in:paypal,venmo,zelle",
            "flag_primary" => "boolean",
        ]);
    }

    protected function getValidatorInstance() {
        $v = parent::getValidatorInstance();
        $v->sometimes("user", "required|uuid", function () {
            return (Client::app()->app_name == "office");
        });

        return ($v);
    }
}
