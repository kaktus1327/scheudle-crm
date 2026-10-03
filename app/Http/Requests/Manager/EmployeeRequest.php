<?php

namespace App\Http\Requests\Manager;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $employee = $this->route('user');

        return $employee instanceof User
            ? $this->user()->can('update', $employee)
            : $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        $employee = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($employee?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::enum(Role::class)],
            'hourly_rate' => ['required', 'numeric', 'min:0', 'max:1000'],
            'position_ids' => ['array'],
            'position_ids.*' => ['integer', 'exists:positions,id'],
        ];
    }
}
