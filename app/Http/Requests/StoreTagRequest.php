<?php

namespace App\Http\Requests;

use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        // No authrozation needed based on project description.
        // If we want authorize this request, I'll use Policies here.
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'slug' => ['sometimes', 'required', 'string', Rule::unique(Tag::class)],
            'title' => ['required', 'string'],
        ];
    }
}
