<?php

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Employee\Database\Factories\EmployeeAvailabilityFactory;

class EmployeeAvailability extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [

        'employee_id',
        'employee_plan_id'
    ];

    protected static function newFactory(): EmployeeAvailabilityFactory
    {
        // return EmployeeAvailabilityFactory::new();
    }
}
