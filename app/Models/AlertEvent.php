<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One entry in an alert's trail: what happened, who did it (null = the system) and the status
 * change. Append-only - written by AlertService::recordEvent(), never edited.
 */
class AlertEvent extends Model
{
    public const UPDATED_AT = null;

    /** Human wording for each action, used by the timeline and the broadcast payload. */
    public const ACTION_LABELS = [
        'raised' => 'Raised',
        'escalated' => 'Escalated to critical',
        'acknowledged' => 'Acknowledged',
        'resolved' => 'Resolved',
        'auto_resolved' => 'Auto-resolved (reading back in range)',
        'resolved_by_work_order' => 'Resolved by completing a work order',
        'device_back_online' => 'Resolved (device back online)',
    ];

    protected $fillable = [
        'alert_id',
        'user_id',
        'action',
        'from_status',
        'to_status',
        'severity',
        'value',
        'note',
    ];

    protected $casts = [
        'value' => 'decimal:3',
        'created_at' => 'datetime',
    ];

    public function alert(): BelongsTo
    {
        return $this->belongsTo(Alert::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return self::ACTION_LABELS[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }

    /**
     * "Joshua", or "System" when nobody was signed in (a device reading, the scheduler).
     */
    public function actorName(): string
    {
        return $this->user?->name ?? 'System';
    }
}
