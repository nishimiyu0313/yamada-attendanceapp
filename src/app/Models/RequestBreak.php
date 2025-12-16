<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestBreak extends Model
{
    use HasFactory;

    protected $table = 'request_breaks';

    protected $fillable = [
        'request_id',
        'break_id',
        'requested_break_start',
        'requested_break_end',
    ];


    public function break()
    {
        return $this->belongsTo(BreakTime::class, 'break_id');
    }

    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id', 'id');
    }
}
