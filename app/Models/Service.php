<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_categories_id',
        'name',
        'description',
        'price',
    ];

    public function category(){
        return $this->belongsTo(ServiceCategory::class, 'service_categories_id');
    }

    public function partners()
    {
        return $this->belongsToMany(Partner::class);
    }

}
