<?php

namespace Juanfv2\BaseCms\Criteria;

use Illuminate\Http\Request;
use Juanfv2\BaseCms\Contracts\CriteriaInterface;
use Juanfv2\BaseCms\Contracts\RepositoryInterface;

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
    protected $fieldsSearchable; // $kConditional =, LIKE, >, <, =>, ...

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
        $this->model            = $model;
        $this->table            = $this->model->getModel()->getTable();
        $this->fieldsSearchable = $repository->getFieldsSearchable();
        $conditions             = $this->request->get('conditions', '');
        $joins                  = $this->request->get('joins', '');
        $select                 = $this->request->get('select', '');
        $sorts                  = $this->request->get('sorts', '');
        $withCount              = $this->request->get('withCount', '');
        $with                   = $this->request->get('with', '');
        $conditions             = json_decode(urldecode($conditions));
        $joins                  = json_decode(urldecode($joins));
        $select                 = $select ? explode(',', urldecode($select)) : null;
        $sorts                  = json_decode(urldecode($sorts));
        $withCount              = json_decode(urldecode($withCount));
        $with                   = json_decode(urldecode($with));

        // logger(__FILE__ . ':' . __LINE__ . ' $this->request ', [$this->request]);

        if (is_array($conditions)) {
            $this->mGroup($conditions, '_ini_', 'and');
        }

        if (is_array($joins)) {
            foreach ($joins as $k) {
                $split = explode('.', $k->c);
                if (count($split) < 3) {
                    continue;
                }

                $joinType = '';
                $joinTable  = $split[0];
                $foreignKey = $split[1];

                switch (count($split)) {
                    case 5:
                        $ownTable   = $split[2];
                        $ownerKey   = $split[3];
                        $joinType   = $split[4];
                        break;
                    case 4:
                        $ownTable   = $split[2];
                        $ownerKey   = $split[3];
                        if (strlen($split[3]) == 1) {
                            $joinType   = $split[3];
                        }
                        break;
                    default:
                        $ownTable   = $this->table;
                        $ownerKey   = $split[2];

                        break;
                }

                switch ($joinType) {
                    case '<':
                        $this->model = $this->model->leftJoin($joinTable, $joinTable . '.' . $foreignKey, '=', $ownTable . '.' . $ownerKey);
                        break;
                    case '>':
                        $this->model = $this->model->leftJoin($joinTable, $joinTable . '.' . $foreignKey, '=', $ownTable . '.' . $ownerKey);
                        break;

                    default:
                        $this->model = $this->model->join($joinTable, $joinTable . '.' . $foreignKey, '=', $ownTable . '.' . $ownerKey);
                        break;
                }

                if (isset($k->v)) {
                    $this->model = $this->model->addSelect($k->v);
                }
            } // end for ...
        }

        if (is_array($select)) {
            foreach ($select as $k) {
                $this->model = $this->model->addSelect($k);
            }
        } else {
            $this->model = $this->model->addSelect($this->table . '.*');
        }

        if ($sorts) {
            foreach ($sorts as $k) {
                $this->model = $this->model->orderBy($k->field, ($k->order == 1 ? 'asc' : 'desc'));
            }
        }

        if ($withCount) {
            $this->model->withCount($withCount);
        }

        if ($with) {
            $this->model->with($with);
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

        $q = $this->model->forNestedWhere();

        foreach ($kParent as $k) {
            // logger(__FILE__ . ':' . __LINE__ . ' inner $k ', [$k]);
            if (is_array($k)) {

                $qw = $this->mGroup($k, '_nested_', $_kOperatorStrParam);
                $q->addNestedWhereQuery($qw->getQuery(), $_kOperatorStrParam);
                continue; // continuar con el siguiente.
            }

            $noValue             = '--false--';
            $_kOperatorStr       = 'AND';
            $_kConditionalStr    = '=';                                           // $kConditional =, LIKE, >, <, =>, ...
            $condition           = explode(' ', $k->c);
            $_kValue             = property_exists($k, 'v') ? $k->v : $noValue;
            $_kValueIsOptionNull = strpos($_kConditionalStr, 'null') !== false;
            // logger(__FILE__ . ':' . __LINE__ . ' $condition ', [$condition, $k->c, $_kValue]);

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
                $_kOperatorStrParam = 'OR';
                continue; // next
            }

            $kFieldStrK = str_replace("$this->table.", '', $_kFieldStr);
            // logger(__FILE__ . ':' . __LINE__ . ' in_array($kFieldStrK, $fieldsSearchable) ', [$kFieldStrK, $fieldsSearchable, in_array($kFieldStrK, $fieldsSearchable)]);
            if (in_array($kFieldStrK, $this->fieldsSearchable)) {
                continue; // next
            }

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
                $q->whereNull($_kFieldStr, $_kOperatorStr, $isNot);
            } elseif (strpos($_kConditionalStr, 'in') !== false) {
                $isNot = $_kConditionalStr === 'not-in';
                $q->whereIn($_kFieldStr, $_kValue, $_kOperatorStr, $isNot);
            } elseif ($_kOperatorStr == 'OR') {
                $q->orWhere($_kFieldStr, $_kConditionalStr, [$_kValue]);
            } else {
                $q->where($_kFieldStr, $_kConditionalStr, [$_kValue]);
            }
        } // end for ...

        if ($query == '_nested_') {
            return $q;
        }
        // logger(__FILE__ . ':' . __LINE__ . ' $q->toSql() ', [$_kOperatorStrParam, $q->toSql()]);

        if ($query == '_ini_') {
            $this->model = $this->model->addNestedWhereQuery($q->getQuery());
        }
    }

    public static function conditionz($conditions = [], $_kOperatorStrNested = '')
    {
        if ($conditions) {

            // $_kOperatorStrNested = null; // $kOperator AND, OR ...

            $where = $_kOperatorStr = '';
            $_kConditionalStr = '='; // $kConditional =, LIKE, >, <, =>, ...

            foreach ($conditions as $index => $k) {

                $_kOperatorStr = $_kOperatorStr == '' ? 'AND' : $_kOperatorStr;
                $_kOperatorStrNested = $_kOperatorStrNested == '' ? 'AND' : $_kOperatorStrNested;

                if ($index == 0) {
                    $_kOperatorStr = $_kOperatorStrNested = '';
                }

                if (is_array($k)) {
                    $where .= " $_kOperatorStrNested (" . RequestGenericCriteria::conditionz($k, 'OR') . ')';
                    continue; // continuar con el siguiente.
                }
                $condition = explode(' ', $k->c);

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
                if ($index == 0) {
                    $_kOperatorStr = '';
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
                    $isNot = $_kConditionalStr === 'not-null' ? ' NOT' : '';
                    $where .= " $_kOperatorStr $kFieldStr$isNot $_kConditionalStr '$_kValue'";
                } elseif (strpos($_kConditionalStr, 'in') !== false) {
                    $isNot = $_kConditionalStr === 'not-in' ? ' NOT' : '';
                    $inStr = implode("','", $_kValue);
                    $where .= " $_kOperatorStr $kFieldStr$isNot $_kConditionalStr ('$inStr')";
                } else {
                    $where .= " $_kOperatorStr $kFieldStr $_kConditionalStr '$_kValue'";
                }
            }

            return $where;
        } else {
            return '';
        }
    }
}
