<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCharacterTel extends FormRequest
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
            'tel_name'  => 'required'
        ];
    }

    /**
     * Custom errors attributes name
     * @author baohq
     * @date   2017-10-17
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
}
