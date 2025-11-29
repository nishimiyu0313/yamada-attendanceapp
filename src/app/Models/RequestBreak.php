<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestBreak extends Model
{
    use HasFactory;

    protected $table = 'request_breaks';

    protected $fillable = [
        'break_id',
        'requested_start',
        'requested_end',
    ];

    /*public function request()
    {
        return $this->belongsTo(Request::class, 'break_id', 'id');
    }*/

    public function break()
    {
        return $this->belongsTo(BreakTime::class, 'break_id');
    }
}
