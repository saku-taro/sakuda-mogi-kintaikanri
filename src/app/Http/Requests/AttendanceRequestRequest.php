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
            // 未来日を変更不可にする場合、clock_inとclock_outはnullableからrequiredにする
            'clock_in' => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i', 'after:clock_in'],
            // 休憩データのバリデーション（配列形式の場合）
            'breaks.*.break_in' => ['nullable', 'date_format:H:i', 'after:clock_in', 'before:clock_out'],
            'breaks.*.break_out' => ['nullable', 'date_format:H:i', 'after:breaks.*.break_in', 'after:clock_in', 'before:clock_out'],

            'remarks' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'breaks.*.break_in.after' => '休憩時間が不適切な値です',
            'breaks.*.break_in.before' => '休憩時間が不適切な値です',
            'breaks.*.break_out.after' => '休憩時間もしくは出勤時間が不適切な値です',
            'breaks.*.break_out.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'remarks.required' => '備考を記入してください'
        ];
    }
}
