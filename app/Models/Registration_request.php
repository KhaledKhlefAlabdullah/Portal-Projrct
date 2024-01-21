<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Registration_request extends Model
{
    use HasFactory, HasUuid;
    protected $keyType='string';

    protected $primaryKey='id';

    public $incrementing = false;




    protected $fillable=[
        'user_id',
        'name',
        'representative_name',
        'email',
        'password',
        'location',
        'phone_number',
        'job_title',
        'request_state',
        'failed_message'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}