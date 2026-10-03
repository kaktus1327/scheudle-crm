<?php

namespace App\Http\Requests\Manager;

use App\Models\Message;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Message::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:10000'],
            'requires_acknowledgement' => ['boolean'],
            'recipient_ids' => ['nullable', 'array'],
            'recipient_ids.*' => ['integer', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx'],
        ];
    }
}
