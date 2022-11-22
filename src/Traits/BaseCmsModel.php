<?php

namespace Juanfv2\BaseCms\Traits;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Juanfv2\BaseCms\Contracts\RepositoryInterface;

/**
 *
 * MyBaseRepository
 *
 * @package namespace App\Repositories;
 */
trait BaseCmsModel
{

    /**
     * Collection of Criteria
     *
     * @var Collection
     */
    protected $criteria;

    /**
     * @var bool
     */
    protected $skipCriteria = false;

    protected $jQuery;

    /**
     * Push Criteria for filter the query
     *
     * @param $criteria
     *
     * @return $this
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function pushCriteria($criteria)
    {
        if (is_null($this->criteria)) {
            $this->criteria = new Collection();
        }
        if (is_null($this->jQuery)) {
            $this->jQuery = $this->query();
            $this->jQuery->setModel($this);
        }
        $this->criteria->push($criteria);

        return $this;
    }

    /**
     * Get Collection of Criteria
     *
     * @return Collection
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    public function getJQuery()
    {
        return $this->jQuery;
    }

    public function setJQuery($q)
    {
        $this->jQuery = $q;
    }

    /**
     * Applies the given where conditions to the model.
     *
     * @param array $where
     * @return void
     */
    protected function applyConditions(array $where)
    {
        foreach ($where as $field => $value) {
            if (is_array($value)) {
                list($field, $condition, $val) = $value;
                $this->model = $this->model->where($field, $condition, $val);
            } else {
                $this->model = $this->model->where($field, '=', $value);
            }
        }
    }

    /**
     * Apply criteria in current Query
     *
     * @return $this
     */
    public function applyCriteria()
    {
        if ($this->skipCriteria === true) {
            return $this;
        }

        $criteria = $this->getCriteria();

        if ($criteria) {
            foreach ($criteria as $c) {
                $c->apply($this);
            }
        }

        return $this;
    }

    /**
     * Retrieve all data of repository
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function mAll($columns = ['*'])
    {
        $this->applyCriteria();

        $results = $this->jQuery->get($columns);

        $this->setJQuery(null);

        return $results;
    }

    /**
     * Retrieve all data of repository
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function mQueryWithCriteria()
    {
        $this->applyCriteria();

        $results = $this->jQuery;

        $this->setJQuery(null);

        return $results;
    }

    public function mDistinct()
    {
        $this->applyCriteria();

        $results = $this->jQuery->distinct()->get();

        $this->setJQuery(null);

        return $results;
    }

    /**
     * Count results of repository
     *
     * @param array $where
     * @param string $columns
     *
     * @return int
     */
    public function mCount($columns = '*')
    {
        $this->applyCriteria();

        $result = $this->jQuery->count($columns);

        $this->setJQuery(null);

        return $result;
    }

    public function mGroupBy($fieldName)
    {
        $this->applyCriteria();

        $result = $this->jQuery->groupBy($fieldName);

        return $result;
    }

    public function mfindWithoutFail($id, $columns = ['*'])
    {
        try {
            return $this->find($id, $columns);
        } catch (Exception $e) {
            return;
        }
    }

    public function mSave(array $attributes)
    {
        $this->fill($attributes);
        $this->save();

        $this->mUpdateRelations($attributes);
        $this->withoutEvents(function () {
            $this->save();
        });

        return $this;
    }

    public function mUpdateRelations($attributes)
    {
        foreach ($attributes as $key => $val) {
            if (
                isset($this) &&
                method_exists($this, $key) &&
                is_a(@$this->$key(), 'Illuminate\Database\Eloquent\Relations\Relation')
            ) {
                $methodClass = get_class($this->$key($key));
                switch ($methodClass) {
                    case 'Illuminate\Database\Eloquent\Relations\BelongsToMany':
                        $new_values = Arr::get($attributes, $key, []);
                        if ($new_values && is_array($new_values[0]) && count($new_values) > 0) {
                            $data = [];
                            foreach ($new_values as $val) {
                                $data[$val[$this->$key()->getRelatedPivotKeyName()]] = $val;
                            }
                            $this->$key()->sync($data);
                        } else {
                            if (array_search('', $new_values) !== false) {
                                unset($new_values[array_search('', $new_values)]);
                            }
                            $this->$key()->sync(array_values($new_values));
                        }
                        break;
                    case 'Illuminate\Database\Eloquent\Relations\BelongsTo':
                        $model_key = $this->$key()->getQualifiedForeignKeyName();
                        $new_value = Arr::get($attributes, $key, null);
                        $new_value = $new_value == '' ? null : $new_value;
                        $this->$model_key = $new_value;
                        break;
                    case 'Illuminate\Database\Eloquent\Relations\HasOne':
                        break;
                    case 'Illuminate\Database\Eloquent\Relations\HasOneOrMany':
                        break;
                    case 'Illuminate\Database\Eloquent\Relations\HasMany':
                        $new_values = Arr::get($attributes, $key, []);
                        if (array_search('', $new_values) !== false) {
                            unset($new_values[array_search('', $new_values)]);
                        }

                        list($temp, $model_key) = explode('.', $this->$key($key)->getQualifiedForeignKeyName());

                        foreach ($this->$key as $rel) {
                            if (!in_array($rel->id, $new_values)) {
                                $rel->$model_key = null;
                                $rel->save();
                            }
                            unset($new_values[array_search($rel->id, $new_values)]);
                        }

                        if (count($new_values) > 0) {
                            $related = get_class($this->$key()->getRelated());
                            foreach ($new_values as $val) {
                                $rel = $related::find($val);
                                // logger(__FILE__ . ':' . __LINE__ . ' $rel ', [$this->id, $this->getTable(), $key, $val, $related, $rel]);
                                if ($rel) {
                                    $rel->$model_key = $this->id;
                                    $rel->save();
                                }
                            }
                        }
                        break;
                }
            }
        }

        return $this;
    }
}
