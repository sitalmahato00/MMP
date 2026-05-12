<?php

namespace App\Core\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * BaseModel
 *
 * All ERP domain models should extend this class.
 * Provides a central place to add shared model behaviour:
 * global scopes, audit hooks, common casts, etc.
 *
 * Usage:
 *   class Student extends BaseModel { ... }
 */
abstract class BaseModel extends Model
{
    /**
     * Disable mass-assignment protection at the base level.
     * Each concrete model still defines its own $fillable.
     */
    protected $guarded = [];
}
