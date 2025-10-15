<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Job extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'code',
        'title',
        'site_id',
        'division',
        'level',
        'employment_type',
        'openings',
        'status',
        'description',
    ];

    protected $casts = [
        'openings' => 'integer',
    ];

    /** Eager load ringan; hapus jika tidak perlu */
    // protected $with = ['site'];

    /* =====================
     * Relationships
     * ===================== */

    /** @return BelongsTo<Site, Job> */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /** @return HasMany<JobApplication> */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /** @return HasOne<ManpowerRequirement> */
    public function manpowerRequirement(): HasOne
    {
        return $this->hasOne(ManpowerRequirement::class);
    }

    /* =====================
     * Scopes
     * ===================== */

    public function scopeOpen($q)
    {
        return $q->where('status', 'open');
    }

    /** Filter by Site UUID */
    public function scopeAtSite($q, string $siteId)
    {
        return $q->where('site_id', $siteId);
    }

    /** Filter by Site code (HO/DBK/POS/...) */
    public function scopeAtSiteCode($q, string $code)
    {
        return $q->whereHas('site', fn ($qq) => $qq->where('code', $code));
    }

    /** Filter by division quickly */
    public function scopeInDivision($q, ?string $division)
    {
        return $division ? $q->where('division', $division) : $q;
    }

    /** Simple search across title/code/description */
    public function scopeSearch($q, ?string $term)
    {
        if (!$term) return $q;
        $term = "%{$term}%";
        return $q->where(function ($qq) use ($term) {
            $qq->where('code', 'like', $term)
               ->orWhere('title', 'like', $term)
               ->orWhere('description', 'like', $term);
        });
    }

    /* =====================
     * Backward-compat (optional)
     * ===================== */

    /** Virtual getter: $job->site_code -> kode dari relasi Site */
    public function getSiteCodeAttribute(): ?string
    {
        return $this->site?->code;
    }

    /**
     * Virtual setter: $job->site_code = 'DBK';
     * Berguna kalau masih ada form lama kirim 'site_code' — kita konversi ke site_id.
     */
    public function setSiteCodeAttribute(?string $code): void
    {
        if (!$code) {
            $this->site_id = null;
            return;
        }
        $siteId = DB::table('sites')->where('code', $code)->value('id');
        $this->site_id = $siteId ?: $this->site_id; // jangan timpa kalau code tidak ketemu
    }
}
