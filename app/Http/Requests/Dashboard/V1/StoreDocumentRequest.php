<?php

namespace Modules\Employee\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png',
                'max:40960', // 40 MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Document name is required.',
            'file.required' => 'Please select a file to upload.',
            'file.mimes'    => 'File must be PDF, Word, Excel, PowerPoint, JPG, or PNG.',
            'file.max'      => 'File must not exceed 40 MB.',
        ];
    }
}
