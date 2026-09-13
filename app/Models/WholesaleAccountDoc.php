<?php

namespace App\Models;

use App\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WholesaleAccountDoc extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'wholesale_account_id',
        'doc_type',
        'file_path',
        'file_name',
    ];

    public function wholesaleAccount(): BelongsTo
    {
        return $this->belongsTo(WholesaleAccount::class);
    }
}
