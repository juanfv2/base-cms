<?php

namespace Juanfv2\BaseCms\Requests\Auth;

use Juanfv2\BaseCms\Models\Auth\Role;
use InfyOm\Generator\Request\APIRequest;

class CreateRoleAPIRequest extends APIRequest
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
    return Role::$rules;
  }
}
