<?php

namespace App\Models;

use App\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CommunicationLog extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'loggable_type',
        'loggable_id',
        'channel',
        'direction',
        'subject',
        'body',
        'contact_person',
    ];

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }
}
