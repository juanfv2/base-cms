<?php

namespace Juanfv2\BaseCms\Criteria;

use Illuminate\Http\Request;
use App\Contracts\CriteriaInterface;
use App\Contracts\RepositoryInterface;

/**
 * Class RequestGenericCriteria
 * @package namespace Juanfv2\BaseCms\Criteria
 */
class RequestGenericCriteria implements CriteriaInterface
{

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;
    protected $model;

    // protected $kOperatorStr = 'AND'; // $kOperator AND, OR ...
    protected $kConditionalStr = '='; // $kConditional =, LIKE, >, <, =>, ...

    protected $kOperatorStrNested; // $kConditional =, LIKE, >, <, =>, ...
    protected $kOperatorStrNestedIndex; // $kConditional =, LIKE, >, <, =>, ...

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Apply criteria in query repository
     *
     * @param                     $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $this->model      = $model;
        $table            = $this->model->getModel()->getTable();
        $fieldsSearchable = $repository->getFieldsSearchable();
        $limit            = $this->request->get('limit', null);
        $offset           = $this->request->get('offset', null);
        $conditions       = $this->request->get('conditions', '');
        $joins            = $this->request->get('joins', '');
        $select           = $this->request->get('select', '');
        $sorts            = $this->request->get('sorts', '');
        $conditions       = json_decode(urldecode($conditions));
        $joins            = json_decode(urldecode($joins));
        $select           = $select ? explode(',', urldecode($select)) : null;
        $sorts            = json_decode(urldecode($sorts));

        if (is_array($conditions) && is_array($fieldsSearchable) && count($fieldsSearchable)) {

            $_kOperatorStrNested = null; // $kOperator AND, OR ...

            foreach ($conditions as $k) {
                // logger(__FILE__ . ':' . __LINE__ . ' $k ', [$k]);

                if (is_array($k)) {
                    if (!isset($_kOperatorStrNested)) {
                        $_kOperatorStrNested = 'AND';
                    }
                    // logger(__FILE__ . ':' . __LINE__ . ' $_kOperatorStrNested ', [$_kOperatorStrNested]);
                    $this->mGroup($k, null, $_kOperatorStrNested);
                    continue; // continuar con el siguiente.
                }
                $_kOperatorStr = 'AND';
                $_kConditionalStr = '='; // $kConditional =, LIKE, >, <, =>, ...
                $condition = explode(' ', $k->c);
                // logger(__FILE__ . ':' . __LINE__ . ' $condition ', [$condition]);

                switch (count($condition)) {
                    case 3:
                        list($_kOperatorStr, $kFieldStr, $_kConditionalStr) = $condition;
                        break;
                    case 2:
                        list($_kOperatorStr, $kFieldStr) = $condition;
                        break;
                    default:
                        list($kFieldStr) = $condition;
                }
                if ($kFieldStr === 'OR') {
                    $_kOperatorStrNested = 'OR';
                    continue; // next
                }
                // todo: is too difficult
                // dump($kFieldStr, $fieldsSearchable, in_array($kFieldStr, $fieldsSearchable));
                if (in_array($kFieldStr, $fieldsSearchable)) {
                    continue; // next
                }

                $noValue = '--false--';
                $_kValue = property_exists($k, 'v') ? $k->v : $noValue;
                $_kValueIsOptionNull = strpos($_kConditionalStr, 'null') !== false;

                if (!$_kValueIsOptionNull && $_kValue === $noValue) {
                    continue;
                }

                if ($_kConditionalStr === 'like') {
                    $_kValue = '%' . $_kValue . '%';
                }
                if ($_kConditionalStr === 'like>') {
                    $_kConditionalStr = 'like';
                    $_kValue = $_kValue . '%';
                }
                if ($_kConditionalStr === '<like') {
                    $_kConditionalStr = 'like';
                    $_kValue = '%' . $_kValue;
                }

                if ($_kValueIsOptionNull) {
                    $isNot = $_kConditionalStr === 'not-null';
                    $this->model = $this->model->whereNull($kFieldStr, $_kOperatorStr, $isNot);
                } elseif (strpos($_kConditionalStr, 'in') !== false) {
                    $isNot = $_kConditionalStr === 'not-in';
                    $this->model = $this->model->whereIn($kFieldStr, $_kValue, $_kOperatorStr, $isNot);
                } else {
                    $this->model = $this->model->where($kFieldStr, $_kConditionalStr, [$_kValue], $_kOperatorStr);
                }
            } // end for...
        }

        if (is_array($joins)) {
            foreach ($joins as $k) {
                $split = explode('.', $k->c);
                if (count($split) < 3) {
                    continue;
                }
                $joinTable = $split[0];
                $foreignKey = $split[1];
                $ownerKey = $split[2];

                $this->model = $this->model->leftJoin($joinTable, $joinTable . '.' . $foreignKey, '=', $table . '.' . $ownerKey);
                if (isset($k->v)) {
                    $joinSelect = $k->v;
                    foreach ($joinSelect as $joinK) {
                        $this->model = $this->model->addSelect($joinTable . '.' . $joinK);
                    }
                }
            } // end for ...
        }

        if (is_array($select)) {
            foreach ($select as $k) {
                $this->model = $this->model->addSelect($table . '.' . $k);
            }
        } else {
            $this->model = $this->model->addSelect($table . '.*');
        }

        if ($sorts) {
            if (isset($sorts->sortField) && !isset($sorts->multiSortMeta)) {
                $this->model = $this->model->orderBy($sorts->sortField, ($sorts->sortOrder == 1 ? 'asc' : 'desc'));
            } elseif (isset($sorts->multiSortMeta)) {
                foreach ($sorts->multiSortMeta as $k) {
                    $this->model = $this->model->orderBy($k->field, ($k->order == 1 ? 'asc' : 'desc'));
                }
            }
        }

        if ($limit) {
            $model = $model->limit($limit);
        }

        if ($offset && $limit) {
            $model = $model->skip($offset);
        }

        return $this->model;
    }

    /**
     * @param $k
     * @param null $query
     * @param int $currentIndex
     */
    private function mGroup($kParent, $query = null, $_kOperatorStrParam = 'AND')
    {
        // logger(__FILE__ . ':' . __LINE__ . ' mGroup $k ', [$k]);
        // logger(__FILE__ . ':' . __LINE__ . ' mGroup $query ', [$query]);
        // logger(__FILE__ . ':' . __LINE__ . ' mGroup $_kOperatorStrParam ', [$_kOperatorStrParam]);

        $this->model = $this->model->where(function ($q) use ($kParent, $query) {

            $_kOperatorStrNested = null; // $kOperator AND, OR ..

            foreach ($kParent as $k) {

                // logger(__FILE__ . ':' . __LINE__ . ' inner $k ', [$k]);

                $mQuery = $q;
                if ($query) {
                    $mQuery = $query;
                }

                if (is_array($k)) {
                    if (!isset($_kOperatorStrNested)) {
                        $_kOperatorStrNested = 'AND';
                    }
                    // logger(__FILE__ . ':' . __LINE__ . ' inner $_kOperatorStrNested ', [$_kOperatorStrNested]);
                    $this->mGroup($k, $mQuery, $_kOperatorStrNested);
                    continue; // continuar con el siguiente.
                }

                $_kOperatorStr = 'AND';
                $_kConditionalStr = '='; // $kConditional =, LIKE, >, <, =>, ...
                $condition = explode(' ', $k->c);
                // logger(__FILE__ . ':' . __LINE__ . ' $condition ', [$condition]);

                switch (count($condition)) {
                    case 3:
                        list($_kOperatorStr, $_kFieldStr, $_kConditionalStr) = $condition;
                        break;
                    case 2:
                        list($_kOperatorStr, $_kFieldStr) = $condition;
                        break;
                    default:
                        list($_kFieldStr) = $condition;
                }

                if ($_kFieldStr === 'OR') {
                    $_kOperatorStrNested = 'OR';
                    continue; // next
                }
                $_kOperatorStrNested = null;

                $noValue = '--false--';
                $_kValue = property_exists($k, 'value') ? $k->v : $noValue;
                $_kValueIsOptionNull = strpos($_kConditionalStr, 'null') !== false;

                if (!$_kValueIsOptionNull && $_kValue === $noValue) {
                    continue;
                }

                if ($_kConditionalStr === 'like') {
                    $_kValue = '%' . $_kValue . '%';
                }
                if ($_kConditionalStr === 'like>') {
                    $_kConditionalStr = 'like';
                    $_kValue = $_kValue . '%';
                }
                if ($_kConditionalStr === '<like') {
                    $_kConditionalStr = 'like';
                    $_kValue = '%' . $_kValue;
                }

                if ($_kValueIsOptionNull) {
                    $isNot = $_kConditionalStr === 'not-null';
                    $mQuery->whereNull($_kFieldStr, $_kOperatorStr, $isNot);
                } elseif (strpos($_kConditionalStr, 'in') !== false) {
                    $isNot = $_kConditionalStr === 'not-in';
                    $mQuery->whereIn($_kFieldStr, $_kValue, $_kOperatorStr, $isNot);
                } else {
                    $mQuery->where($_kFieldStr, $_kConditionalStr, [$_kValue], $_kOperatorStr);
                }
            } // end for ...
        }, null, null, $_kOperatorStrParam);
    }

    public function conditionz($conditions = array())
    {
        if ($conditions) {
            $conditionsStr = '';
            $params = array();
            $where = ' WHERE';
            $equalizer = '=';
            $separator = $keyStr = $keyOrder = '';

            foreach ($conditions as $k => $v) {
                if ($k === 'ORDER') {
                    $keyOrder = " $k BY $v";
                    unset($conditions['ORDER']);
                    continue;
                }
                //$where .= " $k = '$v'";
                $arr = explode(' ', $k);

                // array_pad complete the array with your values, here is : ''.
                // list($dir, $act) = array_pad(explode('/',$url), 2, '');

                switch (count($arr)) {
                    case 3:
                        list($separator, $keyStr, $equalizer) = $arr;
                        break;
                    case 2:
                        list($keyStr, $equalizer) = $arr;
                        break;
                    default:
                        list($keyStr) = $arr;
                }

                $vStr = ":$keyStr";
                $params[$vStr] = $v;
                $where .= " $separator $keyStr $equalizer $vStr";
                $separator = 'AND';
                $equalizer = '=';
            }

            $conditionsStr = $where . $keyOrder;

            return [$conditionsStr, $params];
        } else {
            return '';
        }
    }
}
