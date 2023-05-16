<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'center_lat',
        'center_lng',
        'radius',
        'frequency',
        'period',
        'agent_id',
    ];

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $casts = [
        'has_started' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($collection) {
            if ($collection->isDirty('has_started') && $collection->has_started) {
                $collection->start_date = now();
                $collection->end_date = now()->addHours($collection->period);
            }
        });
    }
}
