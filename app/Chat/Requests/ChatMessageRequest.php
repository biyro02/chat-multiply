<?php

namespace App\Chat\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatMessageRequest extends FormRequest
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
    public function rules()
    {
        return [
            'message' => 'string|nullable',
            'files.*' => 'nullable|mimes:'. config('chat.validation.extensions') .'|max:'. config('chat.validation.size')
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'files.*.required' => 'Please upload an image',
            'files.*.mimes' => 'Only jpeg,png and bmp images are allowed',
            'files.*.max' => 'Sorry! Maximum allowed size for an image is 15MB',
        ];
    }
}
