<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CandidateProfile extends Model
{
    use HasUuids;

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

        'current_salary'  => 'decimal:2',
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
}
