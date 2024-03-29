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
            $resource->updatedBy = auth()->id();
        });
        static::updating(function ($resource) {
            $resource->updatedBy = auth()->id();
        });
    }

    public function getCreatedByPersonAttribute()
    {
        if (! $this->createdBy) {
            return '';
        }
        $rCountry = session('r-country', request()->header('r-country', '.l.'));
        $value = cache()->remember("$rCountry-createdBy-{$this->createdBy}", 3600, fn () => DB::table('auth_users')->where('id', $this->createdBy)->value('name'));

        return $value;
    }

    public function getUpdatedByPersonAttribute()
    {
        if (! $this->updatedBy) {
            return '';
        }
        $rCountry = session('r-country', request()->header('r-country', '.l.'));
        $value = cache()->remember("$rCountry-updatedBy-{$this->updatedBy}", 3600, fn () => DB::table('auth_users')->where('id', $this->updatedBy)->value('name'));

        return $value;
    }
}
