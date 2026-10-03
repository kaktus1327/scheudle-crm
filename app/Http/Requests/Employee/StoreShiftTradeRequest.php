<?php

namespace App\Http\Requests\Employee;

use App\Enums\TradeType;
use App\Models\ShiftTrade;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShiftTradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ShiftTrade::class);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(TradeType::class)],
            'offered_shift_id' => ['required', 'integer', 'exists:shifts,id'],
            'target_user_id' => [
                Rule::requiredIf(fn () => in_array($this->input('type'), [TradeType::Swap->value, TradeType::Giveaway->value], true)),
                'nullable',
                'integer',
                Rule::exists('users', 'id')->whereNull('deleted_at')->where('role', 'employee'),
                Rule::notIn([$this->user()->id]),
            ],
            'requested_shift_id' => [
                Rule::requiredIf(fn () => $this->input('type') === TradeType::Swap->value),
                'nullable',
                'integer',
                'exists:shifts,id',
            ],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
