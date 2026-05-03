<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChimeraPrintJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'delivery_method',
        'target_host',
        'target_port',
        'target_path',
        'template_path',
        'payload',
        'asset_count',
        'result_message',
    ];

    protected $casts = [
        'target_port' => 'integer',
        'asset_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'chimera_print_job_assets')
            ->withTimestamps();
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
