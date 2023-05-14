<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'lat',
        'long',
        'collection_id',
    ];

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }
}
