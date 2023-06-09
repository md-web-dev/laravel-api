<?php

namespace App\Http\Requests\Soundblock\Video;



use Illuminate\Foundation\Http\FormRequest;


class StoreRoomParticipant extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "participating_sid" => "required|string",
        ];
    }
}
