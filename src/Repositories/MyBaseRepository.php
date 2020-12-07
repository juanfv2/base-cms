<?php
/**
 * Created by IntelliJ IDEA.
 * User: Juan
 * Date: 7/6/17
 * Time: 4:46 PM
 */

namespace Juanfv2\BaseCms\Repositories;

use Juanfv2\BaseCms\Models\Auth\GenericModel;
use Illuminate\Database\Eloquent\Model;
use InfyOm\Generator\Common\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

class MyBaseRepository extends BaseRepository
{

    public $table = '';
    public $primaryKey = 'id';

    /**
     * Configure the Model
     **/
    public function model()
    {
        return GenericModel::class;
    }

    public function count(array $where = [], $columns = '*')
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->count();

        $this->resetModel();
        $this->resetScope();

        return $result;
    }

    public function distinct($field)
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->distinct($field)->get();

        $this->resetModel();
        $this->resetScope();

        return $result;
    }

    /**
     * @return Model
     * @throws RepositoryException
     */
    public function reMakeModel()
    {
        $model = $this->app->make($this->model());
        $model->table = $this->table;
        $model->primaryKey = $this->primaryKey;

        logger(__FILE__ . ':' . __LINE__ . ' $model ', [$model]);

        if (!$model instanceof Model) {
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }
}
