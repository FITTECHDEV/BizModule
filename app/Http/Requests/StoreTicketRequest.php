<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
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
            'ticket_title' => 'required',
            'ticket_description' => 'required',
            'module' => 'required',
            'sub_module' => 'required',
            'solution' => 'required',
            'priority' => 'required',
            'attachments' => 'size:2048',
        ];
    }
    public function messages(){
        return [
            'attachments.size' => 'File size should be less than 2 MB',
        ];
    }
}
