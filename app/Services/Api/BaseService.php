<?php

namespace App\Services\Api;



use App\AppTraits\SettingTrait;
use App\Libs\MongoHelper;

class BaseService
{
    use SettingTrait;
    public $db;
    public function __construct()
    {
        $this->db = new MongoHelper();
    }
}