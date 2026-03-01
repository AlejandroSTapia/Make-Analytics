<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExecutionLog extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id', 'scenario_id', 'operations', 'cost', 
        'duration_ms', 'status', 'executed_at'
    ];

    protected $casts = [
        'executed_at' => 'datetime',
    ];

    public function scenario()
    {
        return $this->belongsTo(Scenario::class);
    }
}
