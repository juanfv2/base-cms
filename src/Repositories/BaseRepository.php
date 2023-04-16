<?php

namespace Juanfv2\BaseCms\Repositories;

use Exception;
use Illuminate\Container\Container as Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Juanfv2\BaseCms\Contracts\RepositoryInterface;

/**
 * @deprecated version
 *
 * MyBaseRepository
 */
abstract class BaseRepository implements RepositoryInterface
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

    protected $table = '';

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->criteria = new Collection();
        $this->makeModel();
        $this->makePresenter();
        $this->boot();
    }

    /**
     * @param  null  $presenter
     * @return PresenterInterface
     *
     * @throws RepositoryException
     */
    public function makePresenter($presenter = null)
    {
        $presenter = ! is_null($presenter) ? $presenter : $this->presenter();

        if (! is_null($presenter)) {
            $this->presenter = is_string($presenter) ? $this->app->make($presenter) : $presenter;

            //    if (!$this->presenter instanceof PresenterInterface) {
            //        throw new RepositoryException("Class {$presenter} must be an instance of Prettus\\Repository\\Contracts\\PresenterInterface");
            //    }

            return $this->presenter;
        }

        return null;
    }

    /**
     * Specify Presenter class name
     *
     * @return string
     */
    public function presenter()
    {
        return null;
    }

    public function boot()
    {
        //
    }

    /**
     * @return Model
     *
     * @throws Exception
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());
        if ($this->table) {
            $model->setTable($this->table);
        }
        if (! $model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * @throws Exception
     */
    public function resetModel()
    {
        $this->makeModel();
    }

    public function useTable($table)
    {
        $this->table = $table;
        $this->resetModel();
    }

    /**
     * Get Searchable Fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->model->hidden ?? [];
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    abstract public function model();

    /**
     * Push Criteria for filter the query
     *
     * @return $this
     *
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function pushCriteria($criteria)
    {
        $this->criteria->push($criteria);

        return $this;
    }

    /**
     * Applies the given where conditions to the model.
     *
     * @return void
     */
    protected function applyConditions(array $where)
    {
        foreach ($where as $field => $value) {
            if (is_array($value)) {
                [$field, $condition, $val] = $value;
                $this->model = $this->model->where($field, $condition, $val);
            } else {
                $this->model = $this->model->where($field, '=', $value);
            }
        }
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

    /**
     * Apply criteria in current Query
     *
     * @return $this
     */
    protected function applyCriteria()
    {
        if ($this->skipCriteria === true) {
            return $this;
        }

        $criteria = $this->getCriteria();

        if ($criteria) {
            foreach ($criteria as $c) {
                $this->model = $c->apply($this->model, $this);
            }
        }

        return $this;
    }

    public function distinct(array $where = ['*'], $columns = ['*'])
    {
        $this->applyCriteria();
        // $this->applyScope();

        $results = $this->model->distinct($where)->get($columns);

        $this->resetModel();
        // $this->resetScope();

        return $results;
    }

    /**
     * Retrieve all data of repository
     *
     * @param  array  $columns
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        $this->applyCriteria();
        // $this->applyScope();

        if ($this->model instanceof Builder) {
            $results = $this->model->get($columns);
        } else {
            $results = $this->model->all($columns);
        }

        $this->resetModel();
        // $this->resetScope();

        return $results;
    }

    public function distinctForChunk(array $where = ['*'], $columns = '*')
    {
        $this->applyCriteria();
        // $this->applyScope();

        $results = $this->model->distinct($where);

        $this->resetModel();
        // $this->resetScope();

        return $results;
    }

    /**
     * Retrieve all data of repository
     *
     * @param  array  $columns
     * @return mixed
     */
    public function allForChunk($columns = ['*'])
    {
        $this->applyCriteria();
        // $this->applyScope();

        $results = $this->model;

        $this->resetModel();
        // $this->resetScope();

        return $results;
    }

    /**
     * Count results of repository
     *
     * @param  string  $columns
     * @return int
     */
    public function count(array $where = [], $columns = '*')
    {
        $this->applyCriteria();
        // $this->applyScope();

        if ($where) {
            $this->applyConditions($where);
        }

        $result = $this->model->count($columns);

        $this->resetModel();
        // $this->resetScope();

        return $result;
    }

    public function groupBy($fieldName)
    {
        $this->applyCriteria();
        $result = $this->model->groupBy($fieldName);

        return $result;
    }

    /**
     * Find data by id
     *
     * @param  array  $columns
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        $this->applyCriteria();
        // $this->applyScope();
        $model = $this->model->findOrFail($id, $columns);
        $this->resetModel();

        return $model;
    }

    public function findWithoutFail($id, $columns = ['*'])
    {
        try {
            return $this->find($id, $columns);
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * Find data by multiple fields
     *
     * @param  array  $columns
     * @return mixed
     */
    public function findWhere(array $where, $columns = ['*'])
    {
        $this->applyCriteria();
        // $this->applyScope();

        $this->applyConditions($where);

        $model = $this->model->get($columns);
        $this->resetModel();

        return $model;
    }

    public function create(array $attributes)
    {
        $model = $this->model->newInstance($attributes);
        $model->save();
        $this->resetModel();

        $model = $this->updateRelations($model, $attributes);
        $model->withoutEvents(function () use ($model) {
            $model->save();
        });

        return $model;
    }

    public function update($model, array $attributes)
    {
        $model->fill($attributes);
        $model->save();
        $this->resetModel();

        $model = $this->updateRelations($model, $attributes);
        $model->withoutEvents(function () use ($model) {
            $model->save();
        });

        return $model;
    }

    /**
     * Delete a entity in repository by id
     *
     * @return int
     */
    public function delete($id)
    {
        $model = $this->find($id);
        $originalModel = clone $model;

        $this->resetModel();

        $deleted = $model->delete();

        return $deleted;
    }

    public function updateRelations($model, $attributes)
    {
        foreach ($attributes as $key => $val) {
            if (
                isset($model) &&
                method_exists($model, $key) &&
                is_a(@$model->$key(), 'Illuminate\Database\Eloquent\Relations\Relation')
            ) {
                $methodClass = get_class($model->$key($key));
                switch ($methodClass) {
                    case \Illuminate\Database\Eloquent\Relations\BelongsToMany::class:
                        $new_values = Arr::get($attributes, $key, []);
                        if ($new_values && is_array($new_values[0]) && (is_countable($new_values) ? count($new_values) : 0) > 0) {
                            $data = [];
                            foreach ($new_values as $val) {
                                $data[$val[$model->$key()->getRelatedPivotKeyName()]] = $val;
                            }
                            $model->$key()->sync($data);
                        } else {
                            if (array_search('', $new_values) !== false) {
                                unset($new_values[array_search('', $new_values)]);
                            }
                            $model->$key()->sync(array_values($new_values));
                        }
                        break;
                    case \Illuminate\Database\Eloquent\Relations\BelongsTo::class:
                        $model_key = $model->$key()->getQualifiedForeignKeyName();
                        $new_value = Arr::get($attributes, $key, null);
                        $new_value = $new_value == '' ? null : $new_value;
                        $model->$model_key = $new_value;
                        break;
                    case \Illuminate\Database\Eloquent\Relations\HasOne::class:
                        break;
                    case \Illuminate\Database\Eloquent\Relations\HasOneOrMany::class:
                        break;
                    case \Illuminate\Database\Eloquent\Relations\HasMany::class:
                        $new_values = Arr::get($attributes, $key, []);
                        if (array_search('', $new_values) !== false) {
                            unset($new_values[array_search('', $new_values)]);
                        }

                        [$temp, $model_key] = explode('.', $model->$key($key)->getQualifiedForeignKeyName());

                        foreach ($model->$key as $rel) {
                            if (! in_array($rel->id, $new_values)) {
                                $rel->$model_key = null;
                                $rel->save();
                            }
                            unset($new_values[array_search($rel->id, $new_values)]);
                        }

                        if ((is_countable($new_values) ? count($new_values) : 0) > 0) {
                            $related = get_class($model->$key()->getRelated());
                            foreach ($new_values as $val) {
                                $rel = $related::find($val);
                                $rel->$model_key = $model->id;
                                $rel->save();
                            }
                        }
                        break;
                }
            }
        }

        return $model;
    }

    /**
     * Update or Create an entity in repository
     *
     *
     * @return mixed
     *
     * @throws ValidatorException
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        // $this->applyScope();

        // if (!is_null($this->validator)) {
        //     $this->validator->with(array_merge($attributes, $values))->passesOrFail(ValidatorInterface::RULE_CREATE);
        // }

        // $temporarySkipPresenter = $this->skipPresenter;

        // $this->skipPresenter(true);

        // event(new RepositoryEntityCreating($this, $attributes));

        $model = $this->model->updateOrCreate($attributes, $values);

        // $this->skipPresenter($temporarySkipPresenter);
        $this->resetModel();

        // event(new RepositoryEntityUpdated($this, $model));

        return $model;
    }
}
