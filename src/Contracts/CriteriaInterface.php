<?php
namespace Juanfv2\BaseCms\Contracts;

/**
 * Interface CriteriaInterface
 * @package Juanfv2\BaseCms\Contracts
 * @author Anderson Andrade <contato@andersonandra.de>
 */
interface CriteriaInterface
{
    /**
     * Apply criteria in query repository
     *
     * @param                     $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository);
}
