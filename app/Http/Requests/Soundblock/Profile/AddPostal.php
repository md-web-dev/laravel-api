<?php

namespace App\Http\Requests\Soundblock\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Client;

class AddPostal extends FormRequest {
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
            "postal_type"               => "required|postal_type",
            "postal_street"             => "required|string|max:255",
            "postal_street_additional"  => "sometimes|nullable|string|max:255",
            "postal_city"               => "sometimes|nullable|string|max:255",
            "postal_state"              => "sometimes|nullable|string|max:255",
            "postal_country"            => "sometimes|nullable|string|max:255",
            "postal_zipcode"            => "sometimes|nullable|postal_zipcode",
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
