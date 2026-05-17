<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in_at'  => ['bail', 'required', 'date_format:H:i'],
            'clock_out_at' => ['bail', 'required', 'date_format:H:i', 'after:clock_in_at'],
            'note'         => ['required', 'string', 'max:255'],

            'breaks' => ['sometimes', 'array'],
            'breaks.*' => ['array'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end'   => ['nullable', 'date_format:H:i'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in_at.required'       => '出勤時間を入力してください',
            'clock_in_at.date_format'    => '出勤時間の形式が正しくありません',
            'clock_out_at.required'      => '退勤時間を入力してください',
            'clock_out_at.date_format'   => '退勤時間の形式が正しくありません',
            'clock_out_at.after'         => '出勤時間もしくは退勤時間が不適切な値です',
            'note.required'              => '備考を記入してください',
            'note.max'                   => '備考は255文字以内で入力してください',
            'breaks.*.start.date_format' => '休憩時間の形式が正しくありません',
            'breaks.*.end.date_format'   => '休憩時間の形式が正しくありません',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {
            $clockIn = $this->input('clock_in_at');
            $clockOut = $this->input('clock_out_at');
            $breaks = $this->input('breaks', []);

            $hasClockFormatError =
                collect($validator->errors()->get('clock_in_at'))->contains('出勤時間の形式が正しくありません') ||
                collect($validator->errors()->get('clock_out_at'))->contains('退勤時間の形式が正しくありません');

            foreach ($breaks as $index => $break) {
                $start = $break['start'] ?? null;
                $end = $break['end'] ?? null;

                $hasBreakFormatError =
                    $validator->errors()->has("breaks.$index.start") ||
                    $validator->errors()->has("breaks.$index.end");

                if ($hasBreakFormatError) {
                    continue;
                }

                if (blank($start) && blank($end)) {
                    continue;
                }

                $breakErrors = [];

                if (blank($start) || blank($end)) {
                    $breakErrors[] = '休憩時間が不適切な値です';
                }

                if ($start >= $end) {
                    $breakErrors[] = '休憩時間が不適切な値です';
                }

                if (!$hasClockFormatError && $clockIn && $start <= $clockIn) {
                    $breakErrors[] = '休憩時間が不適切な値です';
                }

                if (!$hasClockFormatError && $clockOut && $start >= $clockOut) {
                    $breakErrors[] = '休憩時間が不適切な値です';
                }

                if (!$hasClockFormatError && $clockOut && $end >= $clockOut) {
                    $breakErrors[] = '休憩時間もしくは退勤時間が不適切な値です';
                }

                $breakErrors = array_unique($breakErrors);

                foreach ($breakErrors as $message) {
                    $validator->errors()->add("breaks.$index.start", $message);
                }
            }
        });
    }
}
