<?php

namespace Juanfv2\BaseCms\Criteria;

use Illuminate\Http\Request;
use Juanfv2\BaseCms\Contracts\CriteriaInterfaceModel;

/**
 * Class LimitOffsetCriteria
 */
class LimitOffsetCriteriaModel implements CriteriaInterfaceModel
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
     * @param $model
     * @return void
     */
    public function apply(&$model)
    {
        $limit = (int) $this->request->get('limit', null);
        $offset = (int) $this->request->get('offset', null);

        if ($limit) {
            $model->getJQuery()->limit($limit);
        }

        if ($offset && $limit) {
            $model->getJQuery()->skip($offset);
        }
    }
}
