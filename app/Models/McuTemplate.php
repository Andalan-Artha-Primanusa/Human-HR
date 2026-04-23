<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class McuTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_name',
        'city',
        'project_name',
        'vendor_name',
        'vendor_address',
        'subject',
        'for_text',
        'bu_name',
        'owner_name',
        'matrix_owner',
        'notes',
        'result_emails',
        'signer_name',
        'signer_title',
        'footer_company_name',
        'footer_address',
        'footer_email',
        'footer_website',
        'is_active',
    ];
}
