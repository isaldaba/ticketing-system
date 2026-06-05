<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'assigned_to',
        'title',
        'requester_name',
        'requester_email',
        'concern',
        'resolution_note',
        'user_remarks',
        'admin_note',
        'priority',
        'due_date',
        'status',
        'submitted_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'submitted_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
