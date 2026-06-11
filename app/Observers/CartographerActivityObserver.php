<?php

namespace App\Observers;

use App\Events\CartographerModifiedObject;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CartographerActivityObserver
{
    public function updating(Model $model): void
    {
        if (! config('mercator.cartography.notifier_enabled', false)) {
            return;
        }

        $user = auth()->user();

        if (! $user instanceof User || $user->isAdmin()) {
            return;
        }

        $dirty = $model->getDirty();

        if (empty($dirty)) {
            return;
        }

        if ($user->isCartographerOf($model)) {
            CartographerModifiedObject::dispatch(
                $user,
                $model,
                $dirty,
                class_basename($model),
            );
        }
    }
}
