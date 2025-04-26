<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // You can adjust this if you have roles/permissions later
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'channel' => 'required|string|in:whatsapp,email,sms,telegram',
            'module' => 'nullable|string|max:255',
            'content' => 'required|string',
            'placeholders' => 'nullable|array',
            'placeholders.*' => 'string', // each placeholder must be a string
            'owner_type' => 'required|string', // e.g., App\Models\User, App\Models\Vendor
            'owner_id' => 'required|integer', // ID of the owner
        ];
    }
}
