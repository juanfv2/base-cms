<?php

namespace Juanfv2\BaseCms\Requests\Country;

use Juanfv2\BaseCms\Models\Country\Country;
use InfyOm\Generator\Request\APIRequest;

class UpdateCountryAPIRequest extends APIRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return Country::$rules;
    }
}
