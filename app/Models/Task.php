<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'column_id',
        'title',
        'description',
        'assigned_to',
        'created_by',
        'progress_percentage',
        'priority',
        'due_date',
        'order'
    ];
    protected $appends = ['deadline_status', 'days_until_due'];

    public function getDeadlineStatusAttribute()
    {
        if (!$this->due_date) {
            return 'no_due_date';
        }

        $dueDate = Carbon::parse($this->due_date);
        $today = Carbon::today();

        $daysDifference = $today->diffInDays($dueDate, false); 

        if ($daysDifference < 0) {
            return 'Atrasado'; // Fecha ya pasó
        } elseif ($daysDifference <= 3) {
            return 'Vence Pronto'; // Por vencer (0-3 días)
        } else {
            return 'A Tiempo'; // A tiempo (+3 días)
        }
    }
    public function getDaysUntilDueAttribute()
    {
        if (!$this->due_date) {
            return null;
        }

        $dueDate = Carbon::parse($this->due_date);
        $today = Carbon::today();

        return $today->diffInDays($dueDate, false); 
    }

    public function column()
    {
        return $this->belongsTo(Column::class);
    }
}
