<?php

namespace App\Repositories\Auth;

use App\Models\Auth\XFile;

use Juanfv2\BaseCms\Repositories\BaseRepository;

/**
 * Class XFileRepository
 * @package App\Repositories
 * @version September 8, 2020, 4:56 pm UTC
 */

class XFileRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return XFile::class;
    }
}
