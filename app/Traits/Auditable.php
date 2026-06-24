<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Register model event listeners that create audit logs when the model is created, updated, or deleted.
     */
    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            self::audit('created', $model);
        });

        static::updated(function (Model $model): void {
            self::audit('updated', $model);
        });

        static::deleted(function (Model $model): void {
            self::audit('deleted', $model);
        });
    }

    /**
     * Create an audit log entry for the given model with the provided description.
     *
     * Records an AuditLog containing the description, the model's id and class as the subject,
     * the current authenticated user's id (if any), the model's serialized properties, and the
     * client's IP address (if available). The `properties` column is a TEXT column (64KB max),
     * so if the serialized model doesn't fit, properties is stored as null instead of being
     * truncated, which would otherwise corrupt the JSON.
     *
     * @param  string  $description  A short description of the action to record (e.g., "created", "updated", "deleted").
     * @param  Model  $model  The Eloquent model instance being audited.
     */
    protected static function audit(string $description, Model $model): void
    {
        $properties = (string) $model;

        AuditLog::query()->create([
            'description' => $description,
            'subject_id' => $model->id ?? null,
            'subject_type' => $model::class,
            'user_id' => Auth::id(),
            'properties' => strlen($properties) <= 65534 ? $properties : null,
            'host' => request()->ip() ?? null,
        ]);
    }
}
