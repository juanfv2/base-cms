<?php

namespace Juanfv2\BaseCms\Contracts;

/**
 * Interface CriteriaInterface
 *
 * @author Anderson Andrade <contato@andersonandra.de>
 */
interface CriteriaInterface
{
    /**
     * Apply criteria in query repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository);
}
