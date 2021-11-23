<?php

namespace App\Repositories\Misc;

use App\Models\Misc\BulkError;
use Juanfv2\BaseCms\Repositories\BaseRepository;

/**
 * Class BulkErrorRepository
 * @package App\Repositories
 * @version November 4, 2021, 10:09 pm UTC
 */
class BulkErrorRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return BulkError::class;
    }
}
