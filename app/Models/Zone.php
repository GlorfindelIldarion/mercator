<?php

namespace App\Models;

use App\Contracts\HasIconContract;
use App\Contracts\HasPrefix;
use App\Factories\ZoneFactory;
use App\Traits\Auditable;
use App\Traits\HasIcon;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasCartographers;

/**
 * @property int $id
 * @property string $name
 * @property string|null $type
 * @property string|null $attributes
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Zone extends Model implements HasPrefix, HasIconContract
{
    use Auditable, HasIcon, HasUniqueIdentifier, HasFactory, SoftDeletes;
    use HasCartographers;

    public $table = 'zones';

    public static string $prefix = 'ZONE_SEC_';

    public static string $icon = '/images/zone.png';

    public static array $searchable = [
        'name',
        'description',
    ];

    protected array $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'ext_refs',
        'name',
        'type',
        'attributes',
        'description',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function newFactory(): Factory
    {
        return ZoneFactory::new();
    }

    /** @return BelongsToMany<Zone, $this> */
    public function childZones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'zone_zone', 'zone_id', 'related_zone_id')
            ->orderBy('name');
    }

    /** @return BelongsToMany<Zone, $this> */
    public function parentZones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'zone_zone', 'related_zone_id', 'zone_id')
            ->orderBy('name');
    }

    /** @return BelongsToMany<Building, $this> */
    public function buildings(): BelongsToMany
    {
        return $this->belongsToMany(Building::class)->orderBy('name');
    }

    /** @return BelongsToMany<AdminUser, $this> */
    public function adminUsers(): BelongsToMany
    {
        return $this->belongsToMany(AdminUser::class)->orderBy('user_id');
    }
}
