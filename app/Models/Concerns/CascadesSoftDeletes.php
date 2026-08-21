<?php

namespace App\Models\Concerns;

/**
 * Soft-deleting a parent no longer triggers the database's ON DELETE CASCADE
 * (that only fires on a real DELETE statement). This trait replaces it: when
 * a model using it is soft-deleted, every relation named in cascadeDeletes()
 * is soft-deleted too, so a restore of the parent has intact children again.
 *
 * Models declare which relations to cascade:
 *
 *     protected function cascadeDeletes(): array
 *     {
 *         return ['jobs', 'invoices'];
 *     }
 */
trait CascadesSoftDeletes
{
    protected static function bootCascadesSoftDeletes(): void
    {
        static::deleting(function ($model) {
            if ($model->isForceDeleting()) {
                return;
            }

            foreach ($model->cascadeDeletes() as $relation) {
                $related = $model->{$relation}();

                // HasOne/BelongsTo-style single-record relations expose get()
                // via a wrapped Collection when we call getResults(); HasMany
                // exposes it directly. Normalise both to a Collection.
                $records = $related->getResults();
                $records = $records instanceof \Illuminate\Support\Collection
                    ? $records
                    : collect($records ? [$records] : []);

                $records->each(fn ($record) => $record->delete());
            }
        });
    }

    /**
     * Relation method names on this model whose records should be
     * soft-deleted alongside it.
     */
    protected function cascadeDeletes(): array
    {
        return [];
    }
}
