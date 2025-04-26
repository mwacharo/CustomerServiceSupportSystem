<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow authorized users to update contacts
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contactable_type' => 'sometimes|required|string',
            'contactable_id' => 'sometimes|required|integer',
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'nullable',
                'email',
                Rule::unique('contacts', 'email')->ignore($this->contact), // Ignore the current contact ID
            ],
            'phone' => 'sometimes|required|string|max:20',
            'alt_phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string',
            'city' => 'sometimes|nullable|string|max:100',
            'zip_code' => 'sometimes|nullable|string|max:20',
            'country_name' => 'sometimes|nullable|string|max:100',
            'state_name' => 'sometimes|nullable|string|max:100',
            'type' => 'sometimes|nullable|string|max:50',
            'company_name' => 'sometimes|nullable|string|max:255',
            'job_title' => 'sometimes|nullable|string|max:255',
            'whatsapp' => 'sometimes|nullable|string|max:20',
            'linkedin' => 'sometimes|nullable|url',
            'telegram' => 'sometimes|nullable|string|max:50',
            'facebook' => 'sometimes|nullable|url',
            'twitter' => 'sometimes|nullable|url',
            'instagram' => 'sometimes|nullable|url',
            'wechat' => 'sometimes|nullable|string|max:50',
            'snapchat' => 'sometimes|nullable|string|max:50',
            'tiktok' => 'sometimes|nullable|string|max:50',
            'youtube' => 'sometimes|nullable|url',
            'pinterest' => 'sometimes|nullable|url',
            'reddit' => 'sometimes|nullable|string|max:50',
            'consent_to_contact' => 'sometimes|nullable|boolean',
            'tags' => 'sometimes|nullable|array',
            'profile_picture' => 'sometimes|nullable|string',
            'notes' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|boolean',
        ];
    }
}
