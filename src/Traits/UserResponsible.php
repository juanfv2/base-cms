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
            $resource->created_by = auth()->id();
            $resource->updated_by = auth()->id();
        });
        static::updating(function ($resource) {
            $resource->updated_by = auth()->id();
        });
    }

    public function getCreatedByPersonAttribute()
    {
        if (! $this->created_by) {
            return '';
        }
        $rCountry = session('r-country', request()->header('r-country', '.l.'));
        $value = cache()->remember("$rCountry-created_by-{$this->created_by}", 3600, fn () => DB::table('auth_users')->where('id', $this->created_by)->value('name'));

        return $value;
    }

    public function getUpdatedByPersonAttribute()
    {
        if (! $this->updated_by) {
            return '';
        }
        $rCountry = session('r-country', request()->header('r-country', '.l.'));
        $value = cache()->remember("$rCountry-updated_by-{$this->updated_by}", 3600, fn () => DB::table('auth_users')->where('id', $this->updated_by)->value('name'));

        return $value;
    }
}
