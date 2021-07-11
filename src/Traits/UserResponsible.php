<?php

namespace Juanfv2\BaseCms\Traits;

use Illuminate\Support\Facades\DB;

trait UserResponsible
{
    /**
     * The "booting" method of the trait.
     */
    protected static function bootUserResponsible(): void
    {
        static::creating(function ($resource) {
            $resource->createdBy = auth()->id();
        });
        static::updating(function ($resource) {
            $resource->updatedBy = auth()->id();
        });
    }

    public function getCreatedByPersonAttribute()
    {
        $value = cache()->remember("createdBy-{$this->createdBy}", 3600, function () {
            return DB::table('auth_users')
                ->select('name')
                ->where('id', $this->createdBy)
                ->value('fullName');
        });
        return $value;
    }

    public function getUpdatedByPersonAttribute()
    {
        $value = cache()->remember("updatedBy-{$this->updatedBy}", 3600, function () {
            return DB::table('auth_users')
                ->select('name')
                ->where('id', $this->createdBy)
                ->value('fullName');
        });
        return $value;
    }
}
