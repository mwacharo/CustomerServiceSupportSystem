<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow authorized users to create contacts
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contactable_type' => 'required|string',
            'contactable_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:contacts,email',
            'phone' => 'required|string|max:20',
            'alt_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'country_name' => 'nullable|string|max:100',
            'state_name' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'linkedin' => 'nullable|url',
            'telegram' => 'nullable|string|max:50',
            'facebook' => 'nullable|url',
            'twitter' => 'nullable|url',
            'instagram' => 'nullable|url',
            'wechat' => 'nullable|string|max:50',
            'snapchat' => 'nullable|string|max:50',
            'tiktok' => 'nullable|string|max:50',
            'youtube' => 'nullable|url',
            'pinterest' => 'nullable|url',
            'reddit' => 'nullable|string|max:50',
            'consent_to_contact' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'profile_picture' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|boolean',
        ];
    }
}
