<?php

namespace App\Models;
use App\Libs\MongoHelper;
use Illuminate\Support\Facades\DB;

class Setting extends BaseModel
{
    protected $table        = 'setting';
    protected $connection   = 'mongodb8';
    protected $database     = 'sp_common';
    protected $dates        = ['deleted_at','updated_at','created_at'];

    public function field()
    {
        $field['key']               = 'key';
        $field['title']             = 'title';
        $field['value']             = 'value';
        $field['type']              = 'type';
        $field['status']            = 'status';
        $field['updater']           = 'updater';
        $field['created_at']        = 'created_at';
        $field['updated_at']        = 'updated_at';
        $field['deleted_at']        = 'deleted_at';
        return $field;
    }

    public function scopeGetSetting($query)
    {
        $setting = $query->select('key', 'value')->get();
        $data = array();
        foreach($setting as $obj)
        {
            $data1 = array($obj->key => $obj->value);
            $data = array_merge($data, $data1);
        }
        return $data;

    }
}