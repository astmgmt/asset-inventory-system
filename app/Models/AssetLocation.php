<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetLocation extends Model
{
    /** @use HasFactory<\Database\Factories\AssetLocationFactory> */
    use HasFactory;
    protected $fillable = ['location_name'];
}
