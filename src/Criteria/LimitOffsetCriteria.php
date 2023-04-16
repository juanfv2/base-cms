<?php

namespace Juanfv2\BaseCms\Criteria;

use Illuminate\Http\Request;
use Juanfv2\BaseCms\Contracts\CriteriaInterface;
use Juanfv2\BaseCms\Contracts\RepositoryInterface;

/**
 * Class LimitOffsetCriteria
 */
class LimitOffsetCriteria implements CriteriaInterface
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Apply criteria in query repository.
     *
     * @param  \Prettus\Repository\Contracts\RepositoryInterface  $repository
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $limit = $this->request->get('limit', null);
        $offset = $this->request->get('offset', null);

        if ($limit) {
            $model = $model->limit($limit);
        }

        if ($offset && $limit) {
            $model = $model->skip($offset);
        }

        return $model;
    }
}
