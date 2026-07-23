<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'work_date' => ['required', 'date_format:Y-m-d'],
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i', 'after:clock_in'],
            // 休憩データのバリデーション（配列形式の場合）
            'breaks.*.break_in' => [
                'nullable',
                'distinct',
                'required_with:breaks.*.break_out',
                'date_format:H:i',
                'after:clock_in',
                'before:clock_out',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $allBreaks = $this->input('breaks');

                    $currentIn = $value;
                    $currentOut = $allBreaks[$index]['break_out'] ?? null;

                    if (!$currentIn || !$currentOut) return;

                    // 同一ペア内での前後関係チェック（break_out が break_in より後か）
                    if ($currentOut <= $currentIn) {
                        $fail("休憩時間が不適切な値です");
                    }

                    foreach ($allBreaks as $i => $break) {
                        if ($i == $index) continue;
                        if (empty($break['break_in']) || empty($break['break_out'])) continue;

                        // 重複チェック
                        if ($break['break_in'] < $currentOut && $break['break_out'] > $currentIn) {
                            $fail("休憩" . ($index + 1) . "が他の休憩時間と重複しています");
                        }
                    }
                },
            ],

            'breaks.*.break_out' => ['distinct', 'nullable', 'required_with:breaks.*.break_in', 'date_format:H:i', 'after:clock_in', 'before:clock_out'],

            'remarks' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を記入してください',
            'clock_out.required' => '退勤時間を記入してください',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'breaks.*.break_in.required_with' => '休憩開始時間と終了時間の両方を入力してください',
            'breaks.*.break_out.required_with' => '休憩開始時間と終了時間の両方を入力してください',
            'breaks.*.break_in.after' => '休憩時間が不適切な値です',
            'breaks.*.break_in.before' => '休憩時間が不適切な値です',
            'breaks.*.break_out.after' => '休憩時間もしくは出勤時間が不適切な値です',
            'breaks.*.break_out.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'breaks.*.break_in.distinct' => '同じ休憩開始時間が重複しています',
            'breaks.*.break_out.distinct' => '同じ休憩終了時間が重複しています',
            'remarks.required' => '備考を記入してください'
        ];
    }
}
