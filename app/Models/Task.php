<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'column_id', 'title', 'description', 'progress_percentage', 
        'priority', 'due_date', 'order'
    ];

    public function column()
    {
        return $this->belongsTo(Column::class);
    }
}
