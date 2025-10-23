<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute; // <-- penting
use Illuminate\Support\Facades\DB;

class Job extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    /** LEVELS (canonical slug) */
    public const LEVELS = ['bod', 'manager', 'supervisor', 'spv', 'staff', 'non_staff'];

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
        'company_id','code','title','site_id','division','level',
        'employment_type','openings','status','description',
        'skills','keywords','created_by','updated_by',
    ];

    protected $casts = [
        'openings' => 'integer',
        'skills'   => 'array',
        // HAPUS cast keywords => array (biar kita kontrol via accessor+mutator)
    ];

    /* =====================
     | Normalizer: LEVEL
     |=====================*/
    public static function normalizeLevel(?string $raw): ?string
    {
        if (!$raw) return null;
        $s = strtolower(trim($raw));
        $s = str_replace([" ", "\xC2\xA0"], '_', $s);
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
        $s = str_replace([" ", "\xC2\xA0"], '_', $s);
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
     | Normalizer: SKILLS
     |=====================*/
    public function setSkillsAttribute($value): void
    {
        $arr = [];
        if (is_array($value)) {
            $arr = $value;
        } elseif (is_string($value)) {
            $trim = trim($value);
            if ($trim !== '') {
                $decoded = json_decode($trim, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $arr = $decoded;
                } else {
                    $arr = preg_split('/\s*[,;|]\s*/', $trim) ?: [];
                }
            }
        }

        $arr = collect($arr)
            ->map(fn($v) => is_string($v) ? trim($v) : (is_array($v) ? ($v['name'] ?? $v['label'] ?? '') : ''))
            ->filter()->unique()->values()->all();

        $this->attributes['skills'] = json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    /* =====================
     | KEYWORDS: accessor + mutator
     |=====================*/
    private static function normalizeKeywords(mixed $value): array
    {
        if (is_array($value)) {
            $arr = $value;
        } elseif (is_string($value)) {
            $s = trim($value);
            if ($s === '') return [];
            $decoded = json_decode($s, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $arr = $decoded;
            } else {
                $arr = preg_split('/\s*[,;|]\s*/', $s) ?: [];
            }
        } else {
            $arr = [];
        }

        return collect($arr)
            ->map(fn($v) => is_string($v) ? trim($v) : (is_array($v) ? ($v['name'] ?? $v['label'] ?? '') : ''))
            ->filter()->unique()->values()->all();
    }

    /** Selalu simpan sebagai JSON array; baca sebagai array */
    protected function keywords(): Attribute
    {
        return Attribute::make(
            get: fn($value) => self::normalizeKeywords($value),
            set: fn($value) => ['keywords' => json_encode(self::normalizeKeywords($value), JSON_UNESCAPED_UNICODE)],
        );
    }

    /** Helper buat Blade */
    public function getKeywordsTextAttribute(): string
    {
        return implode(', ', $this->keywords ?? []);
    }

    /* =====================
     | Relationships
     |=====================*/

    /** @return BelongsTo<Site, Job> */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class)
            ->select(['id', 'code', 'name', 'region', 'timezone', 'address']);
    }

    /** @return BelongsTo<Company, Job> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class)
            ->select(['id', 'code', 'name']);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id','name','email']);
    }
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id','name','email']);
    }

    /** @return HasMany<JobApplication> */
    public function applications(): HasMany
    {
        return $this->hasMany(\App\Models\JobApplication::class);
    }

    public function manpowerRequirements(): HasMany
    {
        return $this->hasMany(\App\Models\ManpowerRequirement::class);
    }

    public function manpowerRequirement(): HasOne
    {
        return $this->hasOne(\App\Models\ManpowerRequirement::class);
    }

    /* =====================
     | Scopes
     |=====================*/
    public function scopeOpen($q)
    {
        return $q->where('status', 'open');
    }

    public function scopeAtSite($q, string $siteId)
    {
        return $q->where('site_id', $siteId);
    }

    public function scopeAtSiteCode($q, string $code)
    {
        return $q->whereHas('site', fn($qq) => $qq->where('code', $code));
    }

    public function scopeAtCompany($q, ?string $companyId)
    {
        if ($companyId === null) {
            return $q->whereNull('company_id');
        }
        return $q->where('company_id', $companyId);
    }

    public function scopeAtCompanyCode($q, string $code)
    {
        return $q->whereHas('company', fn($qq) => $qq->where('code', $code));
    }

    public function scopeInDivision($q, ?string $division)
    {
        $norm = self::normalizeDivision($division);
        return $norm ? $q->where('division', $norm) : $q;
    }

    public function scopeSearch($q, ?string $term)
    {
        if (!$term) return $q;
        $like = "%{$term}%";

        return $q->where(function ($qq) use ($like, $term) {
            $qq->where('code', 'like', $like)
               ->orWhere('title', 'like', $like)
               ->orWhere('description', 'like', $like);

            // Keywords (JSON array)
            try {
                $qq->orWhereJsonContains('keywords', $term);
            } catch (\Throwable $e) {
                // fallback kalau engine tidak support json_contains
                $qq->orWhere('keywords', 'like', $like);
            }

            // Skills JSON (opsional)
            try {
                $qq->orWhereJsonContains('skills', $term);
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }

    /* =====================
     | Virtual attrs: site_code & company_code
     |=====================*/
    public function getSiteCodeAttribute(): ?string
    {
        return $this->site?->code;
    }

    public function setSiteCodeAttribute(?string $code): void
    {
        if (!$code) {
            $this->site_id = null;
            return;
        }
        $siteId = DB::table('sites')->where('code', $code)->value('id');
        $this->site_id = $siteId ?: $this->site_id;
    }

    public function getCompanyCodeAttribute(): ?string
    {
        return $this->company?->code;
    }

    public function setCompanyCodeAttribute(?string $code): void
    {
        if (!$code) {
            $this->company_id = null;
            return;
        }
        $companyId = DB::table('companies')->where('code', $code)->value('id');
        $this->company_id = $companyId ?: $this->company_id;
    }
}
