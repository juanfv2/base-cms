<?php

namespace  Juanfv2\BaseCms\Traits;

trait HasFile
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneXFile($field)
    {
        return $this->hasOne(\App\Models\Misc\XFile::class, 'entity_id')
            ->where('entity', $this->table)
            ->where('field', $field)->withDefault([
                'id' => 1,
                'name' => 'logo.png',
                'field' => $field,
                'entity' => $this->table,
                'extension' => 'png',
                'publicPath' => '/storage/assets/images/logo.png',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyXFile($field)
    {
        return $this->hasMany(\App\Models\Misc\XFile::class, 'entity_id')
            ->where('entity', $this->table)
            ->where('field', $field);
    }
}
