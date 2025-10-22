<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request_break extends Model
{
    use HasFactory;

    protected $fillable = [
        'break_id',
        'reason',
        'requested_start',
        'requested_end',
        'status',
    ];
}
