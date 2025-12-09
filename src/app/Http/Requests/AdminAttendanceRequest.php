<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AdminAttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'reason' => ['required'],

            // 休憩は必ず入れる（修正画面では元データ分だけ存在する前提）
            'breaks' => ['required', 'array'],
            'breaks.*.start' => ['required', 'date_format:H:i'],
            'breaks.*.end' => ['required', 'date_format:H:i'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_in.date_format' => '出勤時間の形式が正しくありません',
            'clock_out.required' => '退勤時間を入力してください',
            'clock_out.date_format' => '退勤時間の形式が正しくありません',
            'reason.required' => '備考を入力してください',
            'breaks.required' => '休憩情報が不足しています',
            'breaks.*.start.required' => '休憩開始時間を入力してください',
            'breaks.*.start.date_format' => '休憩開始時間の形式が正しくありません',
            'breaks.*.end.required' => '休憩終了時間を入力してください',
            'breaks.*.end.date_format' => '休憩終了時間の形式が正しくありません',
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

                if ($bs && $be && $be->lt($bs)) {
                    $validator->errors()->add("breaks.$index.end", '休憩時間が不適切な値です');
                }
                if ($bs && $co && $bs->lt($co)) {
                    $validator->errors()->add("breaks.$index.start", '休憩時間が不適切な値です');
                }
                if ($be && $co && $be->gt($co)) {
                    $validator->errors()->add("breaks.$index.end", '休憩時間もしくは退勤時間が不適切な値です');
                }
            }
        });
    }
}
