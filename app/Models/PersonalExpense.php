<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalExpense extends Model
{
    protected $table = 'personal_expenses';

    protected $fillable = [
        'user_code',
        'section',
        'expense_date',
        'amount',
        'description',
        'file_path',
        'status',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'status' => 'integer',
    ];
}
