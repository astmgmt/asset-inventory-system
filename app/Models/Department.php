<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    
    use HasFactory;
    protected $fillable = ['name', 'description'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function borrowTransactions()
    {
        return $this->hasMany(AssetBorrowTransaction::class, 'user_department_id');
    }
}
