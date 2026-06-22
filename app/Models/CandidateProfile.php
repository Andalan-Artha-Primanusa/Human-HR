<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class CandidateProfile extends Model
{
    use HasUuids, HasFactory;

    /**
     * Mutator: always store gender as lowercased and trimmed string
     */
    public function setGenderAttribute($value)
    {
        $this->attributes['gender'] = is_null($value) ? null : mb_strtolower(trim($value));
    }

    protected $fillable = [
        'user_id',
        'full_name',
        'nickname',
        'gender',
        'birthplace',
        'birthdate',
        'age',
        'nik',
        'phone',
        'whatsapp',
        'email',
        'source_channel',

        'last_education',
        'education_major',
        'education_school',

        'ktp_address',
        'ktp_rt',
        'ktp_rw',
        'ktp_village',
        'ktp_district',
        'ktp_city',
        'ktp_province',
        'ktp_postal_code',
        'ktp_residence_status',

        'domicile_address',
        'domicile_rt',
        'domicile_rw',
        'domicile_village',
        'domicile_district',
        'domicile_city',
        'domicile_province',
        'domicile_postal_code',
        'domicile_residence_status',

        'motivation',
        'has_relatives',
        'relatives_detail',
        'worked_before',
        'worked_before_position',
        'worked_before_duration',
        'applied_before',
        'applied_before_position',
        'willing_out_of_town',
        'not_willing_reason',

        // ===== TAMBAHAN WAJIB (INI YANG BIKIN NULL) =====
        'poh_id',
        'current_salary',
        'expected_salary',
        'expected_facilities',
        'available_start_date',
        'work_motivation',
        'medical_history',
        'last_medical_checkup',

        'cv_path',
        'documents',
        'extras',
        'status_pernikahan',
    ];


    protected $casts = [
        'birthdate' => 'date',

        'current_salary' => 'decimal:2',
        'expected_salary' => 'decimal:2',
        'available_start_date' => 'date',

        'has_relatives' => 'boolean',
        'worked_before' => 'boolean',
        'applied_before' => 'boolean',
        'willing_out_of_town' => 'boolean',

        'documents' => 'array',
        'extras' => 'array',
    ];
    public function user()
    {
        // kolom FK: user_id (UUID atau bigint sesuai migrasi kamu)
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Repeater: trainings
     */
    public function trainings()
    {
        return $this->hasMany(CandidateTraining::class)->orderBy('order_no');
    }

    /**
     * Repeater: employments
     */
    public function employments()
    {
        return $this->hasMany(CandidateEmployment::class)->orderBy('order_no');
    }

    /**
     * Repeater: references
     */
    public function references()
    {
        return $this->hasMany(CandidateReference::class)->orderBy('order_no');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function isCompleteForApplication(): bool
    {
        return $this->missingRequiredForApplication() === [];
    }

    public function missingRequiredForApplication(): array
    {
        $requiredFields = [
            'poh_id' => 'POH / tempat penempatan',
            'full_name' => 'Nama lengkap',
            'gender' => 'Jenis kelamin',
            'age' => 'Usia',
            'birthplace' => 'Tempat lahir',
            'birthdate' => 'Tanggal lahir',
            'nik' => 'NIK KTP',
            'email' => 'Email',
            'phone' => 'Nomor HP',
            'last_education' => 'Pendidikan terakhir',
            'education_major' => 'Jurusan',
            'education_school' => 'Sekolah / kampus',
            'ktp_address' => 'Alamat KTP',
            'ktp_village' => 'Desa / kelurahan KTP',
            'ktp_district' => 'Kecamatan KTP',
            'ktp_city' => 'Kabupaten / kota KTP',
            'ktp_province' => 'Provinsi KTP',
            'ktp_postal_code' => 'Kode pos KTP',
            'domicile_address' => 'Alamat domisili',
            'domicile_village' => 'Desa / kelurahan domisili',
            'domicile_district' => 'Kecamatan domisili',
            'domicile_city' => 'Kabupaten / kota domisili',
            'domicile_province' => 'Provinsi domisili',
            'domicile_postal_code' => 'Kode pos domisili',
        ];

        $missing = [];

        foreach ($requiredFields as $field => $label) {
            $value = $this->getAttribute($field);

            if ($value === null || $value === '') {
                $missing[] = $label;
            }
        }

        if (! $this->hasCvForApplication()) {
            $missing[] = 'CV';
        }

        if ($this->relationCount('trainings') < 1) {
            $missing[] = 'Minimal 1 pelatihan';
        }

        if ($this->relationCount('employments') < 1) {
            $missing[] = 'Minimal 1 riwayat pekerjaan';
        }

        if ($this->relationCount('references') < 1) {
            $missing[] = 'Minimal 1 referensi';
        }

        return array_values(array_unique($missing));
    }

    private function relationCount(string $relation): int
    {
        $countAttribute = "{$relation}_count";

        if (array_key_exists($countAttribute, $this->attributes)) {
            return (int) $this->getAttribute($countAttribute);
        }

        if ($this->relationLoaded($relation)) {
            return $this->getRelation($relation)->count();
        }

        return $this->{$relation}()->count();
    }

    private function hasCvForApplication(): bool
    {
        if (filled($this->cv_path)) {
            return true;
        }

        foreach ((array) ($this->documents ?? []) as $document) {
            $needle = Str::lower((string) (($document['name'] ?? '') . ' ' . ($document['path'] ?? '')));

            if (str_contains($needle, 'cv') || str_contains($needle, 'resume') || str_contains($needle, 'curriculum')) {
                return true;
            }
        }

        if ($this->relationLoaded('attachments')) {
            return $this->attachments->contains(function ($attachment) {
                $needle = Str::lower((string) ($attachment->label . ' ' . $attachment->path));

                return str_contains($needle, 'cv')
                    || str_contains($needle, 'resume')
                    || str_contains($needle, 'curriculum');
            });
        }

        return $this->attachments()
            ->where(function ($query) {
                $query->where('label', 'like', '%cv%')
                    ->orWhere('label', 'like', '%resume%')
                    ->orWhere('label', 'like', '%curriculum%')
                    ->orWhere('path', 'like', '%cv%')
                    ->orWhere('path', 'like', '%resume%')
                    ->orWhere('path', 'like', '%curriculum%');
            })
            ->exists();
    }
}
