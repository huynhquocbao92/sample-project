<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Validator;

class SampleData extends Model
{
    protected $table = 'sample_data';

    /**
     * Validation rules
     * @author baohq
     * @date   2017-09-26
     * @param  array      $merge [description]
     * @return [type]            [description]
     */
    public static function rules($merge=[])
    {
        return array_merge([
            'text'              =>  'required|max:255',
            'password'          =>  'required|max:255',
            'textarea'          =>  'required',
            'select'            =>  'required|max:255',
            // 'checkbox'          =>  'required|max:255',
            'radio'             =>  'required|max:255',
            'image'             =>  'required|max:255',
            'start_date'        =>  'required'
        ], $merge);
    }

    /**
     * Validation
     * @author baohq
     * @date   2017-09-26
     * @return [type]     [description]
     */
    public static function validation(Request $request)
    {
        try {
            // Validation with rules
            $validator = Validator::make($request->all(), self::rules());
            $attributeName = array();
            foreach($request->all() as $key => $value) {
                // Get field name text from language
                $attributeName[$key] = trans('label.'.$key);
            }
            // Set errors attribute field name
            $validator->setAttributeNames($attributeName);
            return $validator;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
