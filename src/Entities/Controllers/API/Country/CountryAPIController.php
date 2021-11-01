<?php

namespace App\Http\Controllers\API\Country;

use App\Models\Country\Country;

use App\Http\Controllers\AppBaseController;
use App\Repositories\Country\CountryRepository;

/**
 * Class CountryController
 *
 * @package App\Http\Controllers\API
 */
class CountryAPIController extends AppBaseController
{
    /** @var CountryRepository */
    public $modelRepository;
    public $rules;
    public $modelNameCamel = 'Country';

    public function __construct(CountryRepository $modelRepo)
    {
        $this->modelRepository = $modelRepo;
        $this->rules = Country::$rules;
    }
}
