<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scenario extends Model
{
     public $incrementing = false;
    protected $fillable = ['id', 'name', 'folder_id', 'is_active'];

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function logs()
    {
        return $this->hasMany(ExecutionLog::class);
    }
}
