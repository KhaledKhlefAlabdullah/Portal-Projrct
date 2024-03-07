<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, HasUuid, SoftDeletes;
    protected $keyType='string';

    protected $primaryKey='id';

    public $incrementing = false;




    protected $fillable=[
        'stakeholder_id',
        'category_id',
        'infrastructures_state',
        'description',
        'start_date',
        'end_date',
    ];

    public function stakeholder()
    {
        return $this->belongsTo(Stakeholder::class,'stakeholder_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
}
