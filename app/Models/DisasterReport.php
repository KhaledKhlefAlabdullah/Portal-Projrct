<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DisasterReport extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $keyType = 'string';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'natural_disaster_id',
        'entity_id',
        'shipment_id',
        'supplier_id',
        'employee_id',
        'waste_id',
        'is_safe',
        'impact_date',
        'start_date',
        'stop_date'
    ];

    public function natural_disaster()
    {
        return $this->belongsTo(NaturalDisaster::class, 'natural_disaster_id');
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function waste()
    {
        return $this->belongsTo(Waste::class, 'waste_id');
    }
}
