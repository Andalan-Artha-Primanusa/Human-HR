<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('candidate_profiles', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
      $t->unique('user_id');

      // === Identitas & Kontak ===
      $t->string('full_name');                         // Nama Lengkap (KTP)
      $t->string('nickname')->nullable();              // Nama Panggilan
      $t->enum('gender', ['male', 'female'])->nullable(); // Jenis Kelamin
      $t->string('birthplace')->nullable();            // Tempat Lahir
      $t->date('birthdate')->nullable();               // Tanggal Lahir
      $t->unsignedTinyInteger('age')->nullable()->comment('Opsional; snapshot usia saat isi form (redundan dgn birthdate)');
      $t->char('nik', 16)->nullable()->unique();       // NIK KTP (16 digit)

      $t->string('phone', 32)->nullable();             // Nomor HP
      $t->string('whatsapp', 32)->nullable();          // Nomor WA
      $t->string('email')->nullable();                 // Email pribadi (bisa beda dgn users.email)

      // === Pendidikan Formal Terakhir ===
      $t->enum('last_education', [
        'SD',
        'SMP',
        'SMA_SMK',
        'D1',
        'D2',
        'D3',
        'D4',
        'S1',
        'S2',
        'S3',
        'LAINNYA'
      ])->nullable();
      $t->string('education_major')->nullable();       // Jurusan
      $t->string('education_school')->nullable();      // Nama Sekolah/Kampus

      // === Alamat KTP ===
      $t->text('ktp_address')->nullable();
      $t->string('ktp_rt', 4)->nullable();
      $t->string('ktp_rw', 4)->nullable();
      $t->string('ktp_village', 100)->nullable();      // Desa/Kelurahan
      $t->string('ktp_district', 100)->nullable();     // Kecamatan
      $t->string('ktp_city', 100)->nullable();         // Kab/Kota
      $t->string('ktp_province', 100)->nullable();     // Provinsi
      $t->string('ktp_postal_code', 10)->nullable();   // Kode Pos
      $t->enum('ktp_residence_status', ['OWN', 'RENT', 'DORM', 'FAMILY', 'COMPANY', 'OTHER'])
        ->nullable()->comment('Status Tempat Tinggal KTP: OWN=Milik, RENT=Sewa, DORM=Kost, FAMILY=Ortu/Klg, COMPANY=Dinas, OTHER=Lainnya');

      // === Alamat Domisili ===
      $t->text('domicile_address')->nullable();
      $t->string('domicile_rt', 4)->nullable();
      $t->string('domicile_rw', 4)->nullable();
      $t->string('domicile_village', 100)->nullable();
      $t->string('domicile_district', 100)->nullable();
      $t->string('domicile_city', 100)->nullable();
      $t->string('domicile_province', 100)->nullable();
      $t->string('domicile_postal_code', 10)->nullable();
      $t->enum('domicile_residence_status', ['OWN', 'RENT', 'DORM', 'FAMILY', 'COMPANY', 'OTHER'])
        ->nullable()->comment('Status Tempat Tinggal Domisili');

      // === Pernyataan Pribadi & Riwayat di Perusahaan ===
      $t->text('motivation')->nullable();               // Motivasi melamar di ABN
      $t->boolean('has_relatives')->nullable();         // Punya saudara/kenalan di ABN?
      $t->string('relatives_detail')->nullable();       // Nama & posisi saudara/kenalan

      $t->boolean('worked_before')->nullable();         // Pernah bekerja di ABN?
      $t->string('worked_before_position')->nullable(); // Posisi terakhir di ABN
      $t->string('worked_before_duration')->nullable(); // Lama bekerja (teks)

      $t->boolean('applied_before')->nullable();        // Pernah melamar ke ABN?
      $t->string('applied_before_position')->nullable(); // Posisi yang pernah dilamar

      $t->boolean('willing_out_of_town')->nullable();   // Bersedia di luar kota?
      $t->text('not_willing_reason')->nullable();       // Alasan jika tidak bersedia

      // === Berkas ===
      $t->string('cv_path')->nullable();                // CV
      $t->json('documents')->nullable()->comment('KTP, KK, NPWP, Ijazah, Surat pengalaman, dll (array path/metadata)');
      $t->string('status_pernikahan')->nullable();                // CV

      // === Lain-lain ===
      $t->json('extras')->nullable();                   // Fleksibel untuk field tambahan
      $t->timestamps();

      // Index opsional untuk pencarian
      $t->index(['last_education']);
      $t->index(['ktp_city', 'ktp_province']);
      $t->index(['domicile_city', 'domicile_province']);

      $t->decimal('current_salary', 15, 2)
        ->nullable()
        ->comment('Gaji saat ini');

      $t->decimal('expected_salary', 15, 2)
        ->nullable()
        ->comment('Gaji yang diharapkan');

      // === Harapan & Kesiapan Kerja ===
      $t->text('expected_facilities')
        ->nullable()
        ->comment('Fasilitas yang diharapkan');

      $t->date('available_start_date')
        ->nullable()
        ->comment('Tanggal siap mulai bekerja');

      $t->text('work_motivation')
        ->nullable()
        ->comment('Motivasi kerja di Andalan Group');

      // === Kesehatan ===
      $t->text('medical_history')
        ->nullable()
        ->comment('Riwayat penyakit, operasi, atau cacat fisik');

      $t->string('last_medical_checkup')
        ->nullable()
        ->comment('Pemeriksaan kesehatan terakhir (kapan & di mana)');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('candidate_profiles');
  }
};
