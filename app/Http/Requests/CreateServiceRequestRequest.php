<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class CreateServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxUploadKilobytes = max(1024, min(20480, (int) Setting::get('max_upload_size', 10) * 1024));

        return [
            'requester_name' => 'nullable|string|max:255',
            'unit_head_name' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'phone_local' => 'nullable|string|max:20',
            'requester_office' => 'nullable|string|max:255',
            'office' => 'nullable|string|max:255',
            'date_request' => 'nullable|date',
            'time_request' => 'nullable',
            'services' => 'required|array|min:1',
            'services.*' => 'string',
            'network_specify_location' => 'nullable|string|max:255',
            'network_other' => 'nullable|string|max:255',
            'ict_other' => 'nullable|string|max:255',
            'mis_other' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'property_no' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:' . $maxUploadKilobytes,
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:' . $maxUploadKilobytes,
        ];
    }
}
