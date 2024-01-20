<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;
    protected $primaryKey='id';
    public $incrementing = false;
    protected $fillable=[
        'id',
        'portal_setting_id',
        'title',
        'description',
        'phone_number',
        'location',
        'start_time',
        'end_time'
    ];

    public function portal_setting()
    {
        return $this->belongsTo('App\Models\Portal_setting','portal_setting_id');
    }

    public function posts()
    {
        return $this->hasMany('App\Models\Post','page_id');
    }
}