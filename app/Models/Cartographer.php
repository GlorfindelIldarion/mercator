<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class Cartographer extends Model
{
    protected $fillable = [
        'cartographiable_type',
        'cartographiable_id',
        'user_id',
        'role_id',
    ];

    protected static function booted(): void
    {
        static::saved(fn ()   => Cache::put('cartographers_last_update', now()->timestamp));
        static::deleted(fn () => Cache::put('cartographers_last_update', now()->timestamp));
    }

    public static function canAccess(string $class): bool
    {
        $permission = Str::snake(class_basename($class)) . '_access';
        if (Gate::allows($permission)) {
            return true;
        }
        return array_key_exists($class, session('cartographer_permissions', []));
    }

    public static function canAccessAny(array $classes): bool
    {
        foreach ($classes as $class) {
            if (static::canAccess($class)) {
                return true;
            }
        }
        return false;
    }

    public static function canAccessAll(array $classes): bool
    {
        foreach ($classes as $class) {
            if (!static::canAccess($class)) {
                return false;
            }
        }
        return true;
    }

    public function cartographiable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Role, $this> */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /** @return array<class-string<\Illuminate\Database\Eloquent\Model>, string> */
    public static function cartographiableRoutesMap(): array
    {
        return [
            \App\Models\Activity::class               => 'admin.activities.show',
            \App\Models\Actor::class                  => 'admin.actors.show',
            \App\Models\Annuaire::class               => 'admin.annuaires.show',
            \App\Models\Application::class            => 'admin.applications.show',
            \App\Models\ApplicationBlock::class       => 'admin.application-blocks.show',
            \App\Models\ApplicationFlow::class        => 'admin.application-flows.show',
            \App\Models\ApplicationModule::class      => 'admin.application-modules.show',
            \App\Models\ApplicationService::class     => 'admin.application-services.show',
            \App\Models\Backup::class                 => 'admin.backups.show',
            \App\Models\Bay::class                    => 'admin.bays.show',
            \App\Models\Building::class               => 'admin.buildings.show',
            \App\Models\Certificate::class            => 'admin.certificates.show',
            \App\Models\Cluster::class                => 'admin.clusters.show',
            \App\Models\Container::class              => 'admin.containers.show',
            \App\Models\Database::class               => 'admin.databases.show',
            \App\Models\DhcpServer::class             => 'admin.dhcp-servers.show',
            \App\Models\Dnsserver::class              => 'admin.dnsservers.show',
            \App\Models\Domain::class                 => 'admin.domains.show',
            \App\Models\Entity::class                 => 'admin.entities.show',
            \App\Models\ExternalConnectedEntity::class => 'admin.external-connected-entities.show',
            \App\Models\ForestAd::class               => 'admin.forest-ads.show',
            \App\Models\Gateway::class                => 'admin.gateways.show',
            \App\Models\Information::class            => 'admin.information.show',
            \App\Models\Lan::class                    => 'admin.lans.show',
            \App\Models\LogicalFlow::class            => 'admin.logical-flows.show',
            \App\Models\LogicalServer::class          => 'admin.logical-servers.show',
            \App\Models\MacroProcessus::class         => 'admin.macro-processuses.show',
            \App\Models\Man::class                    => 'admin.mans.show',
            \App\Models\Network::class                => 'admin.networks.show',
            \App\Models\NetworkSwitch::class          => 'admin.network-switches.show',
            \App\Models\Operation::class              => 'admin.operations.show',
            \App\Models\Peripheral::class             => 'admin.peripherals.show',
            \App\Models\Phone::class                  => 'admin.phones.show',
            \App\Models\PhysicalLink::class           => 'admin.links.show',
            \App\Models\PhysicalRouter::class         => 'admin.physical-routers.show',
            \App\Models\PhysicalSecurityDevice::class => 'admin.physical-security-devices.show',
            \App\Models\PhysicalServer::class         => 'admin.physical-servers.show',
            \App\Models\PhysicalSwitch::class         => 'admin.physical-switches.show',
            \App\Models\Process::class                => 'admin.processes.show',
            \App\Models\Router::class                 => 'admin.routers.show',
            \App\Models\SecurityDevice::class         => 'admin.security-devices.show',
            \App\Models\Site::class                   => 'admin.sites.show',
            \App\Models\StorageDevice::class          => 'admin.storage-devices.show',
            \App\Models\Subnetwork::class             => 'admin.subnetworks.show',
            \App\Models\Task::class                   => 'admin.tasks.show',
            \App\Models\Vlan::class                   => 'admin.vlans.show',
            \App\Models\Wan::class                    => 'admin.wans.show',
            \App\Models\WifiTerminal::class           => 'admin.wifi-terminals.show',
            \App\Models\Workstation::class            => 'admin.workstations.show',
            \App\Models\Zone::class                   => 'admin.zones.show',
            \App\Models\ZoneAdmin::class              => 'admin.zone-admins.show',
        ];
    }

    /** @return array<class-string<\Illuminate\Database\Eloquent\Model>, string> */
    public static function cartographiableModelsList(): array
    {
        return [
            \App\Models\Activity::class               => trans('cruds.activity.title'),
            \App\Models\Actor::class                  => trans('cruds.actor.title'),
            \App\Models\Annuaire::class               => trans('cruds.annuaire.title'),
            \App\Models\Application::class            => trans('cruds.application.title'),
            \App\Models\ApplicationBlock::class       => trans('cruds.applicationBlock.title'),
            \App\Models\ApplicationFlow::class        => trans('cruds.flux.title'),
            \App\Models\ApplicationModule::class      => trans('cruds.applicationModule.title'),
            \App\Models\ApplicationService::class     => trans('cruds.applicationService.title'),
            \App\Models\Backup::class                 => trans('cruds.backup.title'),
            \App\Models\Bay::class                    => trans('cruds.bay.title'),
            \App\Models\Building::class               => trans('cruds.building.title'),
            \App\Models\Certificate::class            => trans('cruds.certificate.title'),
            \App\Models\Cluster::class                => trans('cruds.cluster.title'),
            \App\Models\Container::class              => trans('cruds.container.title'),
            \App\Models\Database::class               => trans('cruds.database.title'),
            \App\Models\DhcpServer::class             => trans('cruds.dhcpServer.title'),
            \App\Models\Dnsserver::class              => trans('cruds.dnsserver.title'),
            \App\Models\Domain::class                 => trans('cruds.domaine.title'),
            \App\Models\Entity::class                 => trans('cruds.entity.title'),
            \App\Models\ExternalConnectedEntity::class => trans('cruds.externalConnectedEntity.title'),
            \App\Models\ForestAd::class               => trans('cruds.forestAd.title'),
            \App\Models\Gateway::class                => trans('cruds.gateway.title'),
            \App\Models\Information::class            => trans('cruds.information.title'),
            \App\Models\Lan::class                    => trans('cruds.lan.title'),
            \App\Models\LogicalFlow::class            => trans('cruds.logicalFlow.title'),
            \App\Models\LogicalServer::class          => trans('cruds.logicalServer.title'),
            \App\Models\MacroProcessus::class         => trans('cruds.macroProcessus.title'),
            \App\Models\Man::class                    => trans('cruds.man.title'),
            \App\Models\Network::class                => trans('cruds.network.title'),
            \App\Models\NetworkSwitch::class          => trans('cruds.networkSwitch.title'),
            \App\Models\Operation::class              => trans('cruds.operation.title'),
            \App\Models\Peripheral::class             => trans('cruds.peripheral.title'),
            \App\Models\Phone::class                  => trans('cruds.phone.title'),
            \App\Models\PhysicalLink::class           => trans('cruds.physicalLink.title'),
            \App\Models\PhysicalRouter::class         => trans('cruds.physicalRouter.title'),
            \App\Models\PhysicalSecurityDevice::class => trans('cruds.physicalSecurityDevice.title'),
            \App\Models\PhysicalServer::class         => trans('cruds.physicalServer.title'),
            \App\Models\PhysicalSwitch::class         => trans('cruds.physicalSwitch.title'),
            \App\Models\Process::class                => trans('cruds.process.title'),
            \App\Models\Relation::class               => trans('cruds.relation.title'),
            \App\Models\Router::class                 => trans('cruds.router.title'),
            \App\Models\SecurityDevice::class         => trans('cruds.securityDevice.title'),
            \App\Models\Site::class                   => trans('cruds.site.title'),
            \App\Models\StorageDevice::class          => trans('cruds.storageDevice.title'),
            \App\Models\Subnetwork::class             => trans('cruds.subnetwork.title'),
            \App\Models\Task::class                   => trans('cruds.task.title'),
            \App\Models\Vlan::class                   => trans('cruds.vlan.title'),
            \App\Models\Wan::class                    => trans('cruds.wan.title'),
            \App\Models\WifiTerminal::class           => trans('cruds.wifiTerminal.title'),
            \App\Models\Workstation::class            => trans('cruds.workstation.title'),
            \App\Models\Zone::class                   => trans('cruds.zone.title'),
            \App\Models\ZoneAdmin::class              => trans('cruds.zoneAdmin.title'),
        ];
    }

    // ─── Détection contexte API (pas de session web initialisée) ─────────────

    /** @var array<int, list<int>> */
    private static array $roleIdsCache = [];

    /** @var array<int, array<string, list<int>>> */
    private static array $cartographerCache = [];

    private static function hasWebSession(): bool
    {
        return session()->has('cartographer_permissions_at');
    }

    // ─── Cache par requête sur le User (évite les N+1 en contexte API) ───────

    private static function getRoleIds(User $user): array
    {
        $key = (int) $user->getKey();
        if (! array_key_exists($key, self::$roleIdsCache)) {
            self::$roleIdsCache[$key] = $user->roles()->pluck('id')->toArray();
        }
        return self::$roleIdsCache[$key];
    }

    /**
     * Charge (et met en cache) toutes les entrées cartographe depuis la base,
     * groupées par type de modèle. Utilisé uniquement en contexte API (pas de session).
     */
    private static function loadCartographerCache(User $user): array
    {
        $key = (int) $user->getKey();
        if (! array_key_exists($key, self::$cartographerCache)) {
            $roleIds = self::getRoleIds($user);
            self::$cartographerCache[$key] = static::where(function ($q) use ($user, $roleIds) {
                    $q->where('user_id', $user->id);
                    if (! empty($roleIds)) {
                        $q->orWhereIn('role_id', $roleIds);
                    }
                })
                ->get(['cartographiable_type', 'cartographiable_id'])
                ->groupBy('cartographiable_type')
                ->map(fn ($rows) => $rows->pluck('cartographiable_id')->unique()->values()->toArray())
                ->toArray();
        }
        return self::$cartographerCache[$key];
    }

    // ─── API publique ──────────────────────────────────────────────────────────

    public static function isAllowed(User $user, \Illuminate\Database\Eloquent\Model $object): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (self::hasWebSession()) {
            $ids = session('cartographer_permissions.' . get_class($object), []);
            return in_array($object->getKey(), $ids);
        }

        // Contexte API : requête DB (cachée sur $user)
        $cache = self::loadCartographerCache($user);
        $ids   = $cache[get_class($object)] ?? [];
        return in_array($object->getKey(), $ids);
    }

    public static function allowedIdsFor(User $user, string $modelClass): array
    {
        if ($user->isAdmin()) {
            return [];
        }

        if (self::hasWebSession()) {
            return session('cartographer_permissions.' . $modelClass, []);
        }

        // Contexte API : requête DB (cachée sur $user)
        $cache = self::loadCartographerCache($user);
        return $cache[$modelClass] ?? [];
    }

    /**
     * Applique la restriction cartographe sur un builder déjà typé.
     *
     * @template T of \Illuminate\Database\Eloquent\Model
     * @param \Illuminate\Database\Eloquent\Builder<T> $query
     * @return \Illuminate\Database\Eloquent\Builder<T>
     */
    public static function scopedQuery(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        $class = get_class($query->getModel());
        $user  = auth()->user();

        if (! $user) {
            return $query->whereRaw('0 = 1');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        // No assignments: fall back to role-based permission (grants full access).
        $permission = Str::snake(class_basename($class)) . '_access';
        if (Gate::allows($permission)) {
            return $query;
        }

        // Cartographer assignments take precedence: if any exist, restrict to them.
        $ids = self::allowedIdsFor($user, $class);
        if (! empty($ids)) {
            $table = $query->getModel()->getTable();
            return $query->whereIn("{$table}.id", $ids);
        }

        return $query->whereRaw('0 = 1');
    }

    /**
     * Variante pour les appelants avec un nom de classe dynamique (chaîne).
     * Ne promet pas de type générique — utiliser scopedQuery(X::query()) si possible.
     */
    public static function scopedQueryByClass(string $class): \Illuminate\Database\Eloquent\Builder
    {
        return self::scopedQuery((new $class)->newQuery());
    }

    public static function hasAnyFor(User $user, string $modelClass): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (self::hasWebSession()) {
            return ! empty(session('cartographer_permissions.' . $modelClass, []));
        }

        // Contexte API
        $cache = self::loadCartographerCache($user);
        return ! empty($cache[$modelClass] ?? []);
    }

    public static function loadSessionFor(User $user): void
    {
        $roleIds = $user->roles()->pluck('id')->toArray();

        $hasAssignments = static::where(function ($q) use ($user, $roleIds) {
            $q->where('user_id', $user->id);
            if (! empty($roleIds)) {
                $q->orWhereIn('role_id', $roleIds);
            }
        })->exists();

        if ($user->isAdmin()) {
            session([
                'cartographer_permissions'    => [],
                'cartographer_permissions_at' => now()->timestamp,
                'is_cartographer'             => $hasAssignments,
            ]);
            return;
        }

        $permissions = static::where(function ($q) use ($user, $roleIds) {
                $q->where('user_id', $user->id);
                if (! empty($roleIds)) {
                    $q->orWhereIn('role_id', $roleIds);
                }
            })
            ->get(['cartographiable_type', 'cartographiable_id'])
            ->groupBy('cartographiable_type')
            ->map(fn ($rows) => $rows->pluck('cartographiable_id')->unique()->values()->toArray())
            ->toArray();

        session([
            'cartographer_permissions'    => $permissions,
            'cartographer_permissions_at' => now()->timestamp,
            'is_cartographer'             => $hasAssignments,
        ]);
    }
}
