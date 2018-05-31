<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\UsersController;

class StoreUsers extends FormRequest
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
        return [
            'full_name'             => 'required',
            'login_id'              => 'required|min:4|max:12|unique:users,login_id,'.$this->id,
            'password'              => 'required|min:8|max:16|',
            'password_confirm'      => 'required|same:password'
        ];
    }

    /**
     * Custom errors attributes name
     * @author baohq
     * @date   2017-10-12
     * @return attributes array
     */
    public function attributes()
    {
        $attributeName = array();
        foreach($this->request->all() as $key => $value) {
            // Get field name text from language
            $attributeName[$key] = trans('label.'.$key);
        }
        return $attributeName;
    }

    public function withValidator($validator)
    {
        $alphaReg       = '/^[a-z0-9]*$/i';
        $japanAlphaReg  = '/[!@#$%^&*()+=\-_\[\]\';,.\/{}|":<>?~\\\\]/';

        $validator->after(function ($validator) use($alphaReg) {
            // Check login_id (only has alphanumeric)
            if(!preg_match($alphaReg, $this->login_id)) {
                $validator->errors()->add('login_id', trans('label.login_id').trans('message.invalid'));
            }
        })->after(function ($validator) use($alphaReg) {
            // Check password (only has alphanumeric)
            if(!preg_match($alphaReg, $this->password)) {
                $validator->errors()->add('password', trans('label.password').trans('message.invalid'));
            }
        })->after(function ($validator) use($japanAlphaReg) {
            // Check full_name (only has japanese and alphanumeric)
            if(preg_match($japanAlphaReg, $this->full_name)) {
               $validator->errors()->add('full_name', trans('label.full_name').trans('message.invalid'));
            }
        });
    }
}
