<?php

namespace Juanfv2\BaseCms\Contracts;

/**
 * Interface CriteriaInterface
 *
 * @author Anderson Andrade <contato@andersonandra.de>
 */
interface CriteriaInterfaceModel
{
    /**
     * Apply criteria in query repository
     *
     * @return void
     */
    public function apply(&$model);
}
