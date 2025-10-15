<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasUuidPrimaryKey
{
    /**
     * Set default PK behavior saat model diinisialisasi.
     * Tidak mendefinisikan property, hanya mengubah nilainya.
     */
    protected function initializeHasUuidPrimaryKey(): void
    {
        // Model already has these properties; we just set their values.
        $this->incrementing = false;
        $this->keyType = 'string';
    }

    /**
     * Auto-isi UUID pada kolom PK jika kosong saat creating.
     */
    protected static function bootHasUuidPrimaryKey(): void
    {
        static::creating(function ($model) {
            $key = $model->getKeyName();
            if (empty($model->{$key})) {
                $model->{$key} = (string) Str::uuid();
            }
        });
    }
}
