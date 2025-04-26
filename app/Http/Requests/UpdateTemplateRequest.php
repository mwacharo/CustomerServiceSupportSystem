<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow the update (you can adjust with policies later)
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
            'name' => 'sometimes|required|string|max:255',
            'channel' => 'sometimes|required|string|in:whatsapp,email,sms,telegram',
            'module' => 'nullable|string|max:255',
            'content' => 'sometimes|required|string',
            'placeholders' => 'nullable|array',
            'placeholders.*' => 'string', // each placeholder must be a string
            'owner_type' => 'sometimes|required|string',
            'owner_id' => 'sometimes|required|integer',
        ];
    }
}
