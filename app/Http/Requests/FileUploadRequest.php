<?php

namespace App\Http\Requests;

class FileUploadRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'attachment' => [
                'required',
                'file',
                'max:10240', // 10MB in kilobytes
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
                'scan_malware',  // Custom rule we'll create
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Custom validation messages
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attachment.required' => 'Please select a file to upload',
            'attachment.file' => 'The attachment must be a valid file',
            'attachment.max' => 'The file must not exceed 10MB',
            'attachment.mimes' => 'Only PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, and GIF files are allowed',
            'attachment.scan_malware' => 'The file appears to contain malicious content and cannot be uploaded',
            'description.max' => 'Description must not exceed 500 characters',
        ];
    }

    /**
     * Prepare data for validation
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'description' => trim($this->description ?? ''),
        ]);
    }
}
