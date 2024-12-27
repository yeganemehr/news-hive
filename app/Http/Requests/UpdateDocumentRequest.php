<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends FormRequest
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
        $id = $this->route()?->parameter('document');

        return [
            'slug' => ['sometimes', 'required', 'string', Rule::unique(Document::class)->ignore($id)],
            'title' => ['sometimes', 'required', 'string'],
            'image' => ['sometimes', 'required', 'url'],
            'content' => ['sometimes', 'required', 'string'],
            'published_at' => ['sometimes', 'required', 'date'],
        ];
    }
}
