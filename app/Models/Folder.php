<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
     public $incrementing = false;
    protected $fillable = ['id', 'name'];

    public function scenarios()
    {
        return $this->hasMany(Scenario::class);
    }
}
