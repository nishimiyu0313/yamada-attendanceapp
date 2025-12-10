<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UserAttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in' => ['nullable'],
            'clock_out' => ['nullable'],
            'reason' => ['required'],

            'breaks' => ['array'],
            'breaks.*.start' => ['nullable'],
            'breaks.*.end' => ['nullable'],
        ];
    }

    public function messages()
    {
        return [
            'reason.required' => '備考を入力してください',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('clock_in');
            $clockOut = $this->input('clock_out');

            // Carbon に渡す前に空文字チェック
            $ci = $clockIn ? Carbon::createFromFormat('H:i', $clockIn) : null;
            $co = $clockOut ? Carbon::createFromFormat('H:i', $clockOut) : null;

            if ($ci && $co && $co->lt($ci)) {
                $validator->errors()->add('clock_out', '出勤時間が不適切です');
            }

            // 複数休憩チェック
            $breaks = $this->input('breaks', []);


            foreach ($breaks as $index => $break) {
                $bs = !empty($break['start']) ? Carbon::createFromFormat('H:i', $break['start']) : null;
                $be = !empty($break['end']) ? Carbon::createFromFormat('H:i', $break['end']) : null;

                if ($bs && $ci && $bs->lt($ci)) {
                    $validator->errors()->add("breaks.$index.start", '休憩時間が不適切な値です');
                    continue;
                }

                // (2) 休憩終了が休憩開始より前 → NG
                if ($bs && $be && $be->lt($bs)) {
                    $validator->errors()->add("breaks.$index.end", '休憩時間が不適切な値です');
                    continue;
                }

                // (3) 休憩終了が退勤より後 → NG
                if ($be && $co && $be->gt($co)) {
                    $validator->errors()->add("breaks.$index.end", '休憩時間もしくは退勤時間が不適切な値です');
                    continue;
                }
            }
        });
    }
}
