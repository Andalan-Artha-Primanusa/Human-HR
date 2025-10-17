<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStage extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    /**
     * Catatan:
     * - Tambah kolom 'acted_by' (UUID user yang terakhir mengubah stage)
     * - Tambah kolom 'user_id' (opsional: pembuat awal stage)
     * - Tambah kolom 'notes' (opsional: catatan perubahan)
     */
    protected $fillable = [
        'application_id',
        'stage_key',
        'status',
        'score',
        'payload',
        'acted_by',  // NEW
        'user_id',   // NEW (opsional jika tabel kamu punya kolom ini)
        'notes',     // NEW
    ];

    protected $casts = [
        'score'   => 'decimal:2',
        'payload' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** @return BelongsTo<JobApplication,ApplicationStage> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }

    /**
     * Aktor (User) yang TERAKHIR melakukan perubahan pada stage ini.
     * Kolom: acted_by (UUID) → users.id
     * @return BelongsTo<User,ApplicationStage>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }

    /**
     * (Opsional) Pembuat awal stage (jika kamu menyimpan user_id).
     * Kolom: user_id (UUID) → users.id
     * @return BelongsTo<User,ApplicationStage>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper singkat untuk ambil nama aktor (fallback ke user lalu "Sistem/Unknown").
     */
    public function getActorNameAttribute(): string
    {
        if ($this->relationLoaded('actor') && $this->actor) {
            return $this->actor->name ?? 'Sistem/Unknown';
        }
        if ($this->relationLoaded('user') && $this->user) {
            return $this->user->name ?? 'Sistem/Unknown';
        }
        // fallback query ringan bila belum eager-load (hindari N+1: tetap disarankan eager-load dari controller)
        if ($this->acted_by) {
            $u = User::query()->select('name')->find($this->acted_by);
            if ($u && $u->name) return $u->name;
        }
        if ($this->user_id) {
            $u = User::query()->select('name')->find($this->user_id);
            if ($u && $u->name) return $u->name;
        }
        return 'Sistem/Unknown';
    }
}
