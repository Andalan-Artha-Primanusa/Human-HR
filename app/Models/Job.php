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

    /** LEVELS (canonical slug) */
    public const LEVELS = ['bod','manager','supervisor','spv','staff','non_staff'];

    /** Label Level untuk tampilan */
    public const LEVEL_LABELS = [
        'bod'        => 'BOD',
        'manager'    => 'Manager',
        'supervisor' => 'Supervisor',
        'spv'        => 'SPV',
        'staff'      => 'Staff',
        'non_staff'  => 'Non staff',
    ];

    /** DIVISIONS (canonical slug => label) */
    public const DIVISIONS = [
        'engineering' => 'Engineering',
        'hr'          => 'Human Resources',
        'it'          => 'Information Technology',
        'finance'     => 'Finance',
        'marketing'   => 'Marketing',
        'sales'       => 'Sales',
        'operations'  => 'Operations',
        'admin'       => 'Administration',
    ];

    protected $fillable = [
        'code','title','site_id','division','level','employment_type',
        'openings','status','description',
    ];

    protected $casts = [
        'openings' => 'integer',
    ];

    /* =====================
     | Normalizer: LEVEL
     |=====================*/
    public static function normalizeLevel(?string $raw): ?string
    {
        if (!$raw) return null;
        $s = strtolower(trim($raw));
        $s = str_replace([' ', ' '], '_', $s); // termasuk NBSP
        $aliases = [
            'board_of_directors' => 'bod',
            'non-staff' => 'non_staff',
            'nonstaff'  => 'non_staff',
        ];
        $s = $aliases[$s] ?? $s;
        return in_array($s, self::LEVELS, true) ? $s : null;
    }

    public function setLevelAttribute($value): void
    {
        $norm = self::normalizeLevel(is_string($value) ? $value : null);
        $this->attributes['level'] = $norm ?: null;
    }

    public function getLevelLabelAttribute(): ?string
    {
        $key = $this->attributes['level'] ?? null;
        return $key ? (self::LEVEL_LABELS[$key] ?? strtoupper($key)) : null;
    }

    /* =====================
     | Normalizer: DIVISION
     |=====================*/
    public static function normalizeDivision(?string $raw): ?string
    {
        if (!$raw) return null;
        $s = strtolower(trim($raw));
        $s = str_replace([' ', ' '], '_', $s); // termasuk NBSP
        $aliases = [
            'human_resources'        => 'hr',
            'people'                 => 'hr',
            'information_technology' => 'it',
            'ops'                    => 'operations',
        ];
        $s = $aliases[$s] ?? $s;
        return array_key_exists($s, self::DIVISIONS) ? $s : null;
    }

    public function setDivisionAttribute($value): void
    {
        $norm = self::normalizeDivision(is_string($value) ? $value : null);
        $this->attributes['division'] = $norm ?: null;
    }

    public function getDivisionLabelAttribute(): ?string
    {
        $key = $this->attributes['division'] ?? null;
        return $key ? (self::DIVISIONS[$key] ?? strtoupper($key)) : null;
    }

    /* =====================
     | Relationships
     |=====================*/

    /** @return BelongsTo<Site, Job> */
    public function site(): BelongsTo
    {
        // eksplisitkan keys biar aman
        return $this->belongsTo(\App\Models\Site::class, 'site_id', 'id');
    }

    /** @return HasMany<JobApplication> */
    public function applications(): HasMany
    {
        return $this->hasMany(\App\Models\JobApplication::class);
    }

    /** @return HasOne<ManpowerRequirement> */
    public function manpowerRequirement(): HasOne
    {
        return $this->hasOne(\App\Models\ManpowerRequirement::class);
    }

    /* =====================
     | Scopes
     |=====================*/
    public function scopeOpen($q) { return $q->where('status','open'); }

    public function scopeAtSite($q, string $siteId) { return $q->where('site_id',$siteId); }

    public function scopeAtSiteCode($q, string $code)
    {
        return $q->whereHas('site', fn($qq) => $qq->where('code',$code));
    }

    public function scopeInDivision($q, ?string $division)
    {
        $norm = self::normalizeDivision($division);
        return $norm ? $q->where('division', $norm) : $q;
    }

    public function scopeSearch($q, ?string $term)
    {
        if(!$term) return $q;
        $term = "%{$term}%";
        return $q->where(fn($qq) =>
            $qq->where('code','like',$term)
               ->orWhere('title','like',$term)
               ->orWhere('description','like',$term)
        );
    }

    /* =====================
     | Backward compat: site_code virtual
     |=====================*/
    public function getSiteCodeAttribute(): ?string { return $this->site?->code; }

    public function setSiteCodeAttribute(?string $code): void
    {
        if(!$code){ $this->site_id = null; return; }
        $siteId = DB::table('sites')->where('code',$code)->value('id');
        $this->site_id = $siteId ?: $this->site_id;
    }
}
