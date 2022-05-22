<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reason_id',
        'weight',
        'with_coupon',
        'active',
        'response_id',
        'department_id',
    ];
}
