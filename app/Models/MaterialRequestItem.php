<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_request_id', 'item_name', 'specification',
        'unit', 'quantity', 'remarks',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function materialRequest()
    {
        return $this->belongsTo(MaterialRequest::class);
    }
}
