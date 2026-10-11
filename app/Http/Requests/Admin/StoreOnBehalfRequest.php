<?php

namespace App\Http\Requests\Admin;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOnBehalfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $allowedServices = [
            'new_network_computer',
            'new_network_printer',
            'new_cabling',
            'new_telephone_line',
            'new_cabling_telephone',
            'move_new_location',
            'others_network',
            'troubleshoot_pc',
            'troubleshoot_printer',
            'network_connection',
            'software_installation',
            'hardware_install',
            'others_ict',
            'account_creation',
            'password_reset',
            'system_access',
            'others_mis',
        ];
        $maxUploadKilobytes = max(1024, min(20480, (int) Setting::get('max_upload_size', 10) * 1024));

        return [
            'requester_name' => 'required|string|max:255',
            'requester_designation' => 'nullable|string|max:255',
            'requester_office' => 'nullable|string|max:255',
            'unit_head_name' => 'nullable|string|max:255',
            'office' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'date_of_request' => 'nullable|date',
            'time_of_request' => 'nullable|date_format:H:i',
            'services' => 'required|array|min:1',
            'services.*' => ['string', Rule::in($allowedServices)],
            'description' => 'nullable|string|max:5000',
            'property_no' => 'nullable|string|max:255',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:' . $maxUploadKilobytes,
        ];
    }
}
