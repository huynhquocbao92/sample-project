<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

/**
 * Sample form request
 * @author baohq
 * @date   2017-09-29
 */
class StoreSample extends FormRequest
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
            'text'              =>  'required|max:255',
            'password'          =>  'required|max:255',
            'textarea'          =>  'required',
            'select'            =>  'required|max:255',
            // 'checkbox'          =>  'required|max:255',
            'radio'             =>  'required|max:255',
            'image'             =>  'required|max:255',
            'start_date'        =>  'required',
            'map_address'       =>  'required',
            'map_latitude'      =>  'required',
            'map_longitude'     =>  'required',
            'map_zoom'          =>  'required'
        ];
    }

    /**
     * Configure the validator instance
     * @author baohq
     * @date   2017-09-29
     * @param  $validator
     * @return
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Add some action here
        });
    }

    /**
     * Custom errors attributes name
     * @author baohq
     * @date   2017-09-29
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
