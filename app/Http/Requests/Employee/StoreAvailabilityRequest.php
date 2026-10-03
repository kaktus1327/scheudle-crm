<?php

namespace App\Http\Requests\Employee;

use App\Support\CinemaWeek;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isEmployee();
    }

    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:500'],
            'days' => ['required', 'array', 'size:7'],
            'days.*.date' => ['required', 'date_format:Y-m-d'],
            'days.*.unavailable' => ['required', 'boolean'],
            'days.*.start_time' => ['nullable', 'required_if:days.*.unavailable,false', 'date_format:H:i'],
            'days.*.end_time' => ['nullable', 'required_if:days.*.unavailable,false', 'date_format:H:i'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $allowed = CinemaWeek::next()->days()->map->toDateString()->all();
                $given = collect($this->input('days', []))->pluck('date')->sort()->values()->all();

                if ($given !== $allowed) {
                    $validator->errors()->add('days', 'Dyspozycyjność można wysłać tylko na najbliższy tydzień kinowy.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'days.*.start_time.required_if' => 'Podaj godzinę od albo zaznacz "nie mogę".',
            'days.*.end_time.required_if' => 'Podaj godzinę do albo zaznacz "nie mogę".',
        ];
    }
}
