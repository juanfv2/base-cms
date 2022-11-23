<?php

namespace Juanfv2\BaseCms\Traits;

trait IsPivot
{
    /**
     * Scope a query to only include "ID"
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeId($query, $id)
    {
        $pks = [];
        $pkValues = explode('_', $id);
        foreach ($this->primaryKey as $k => $value) {
            $pks[$value] = $pkValues[$k];
        }

        return $query->where($pks);
    }

    /**
     * Define the keys in model
     */

    // /**
    //  * Indicates if the IDs are auto-incrementing.
    //  *
    //  * @var bool
    //  */
    // public $incrementing = false;
    // protected $primaryKey = [
    //     'product_id',
    //     'option_id',
    //     'option_value_id',
    // ];

    public function getIdAttribute()
    {
        $pks = [];
        foreach ($this->primaryKey as $value) {
            $pks[] = $this->$value;
        }

        return implode('_', $pks);
    }

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if (! is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the primary key value for a save query.
     *
     * @param  mixed  $keyName
     * @return mixed
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }
}
