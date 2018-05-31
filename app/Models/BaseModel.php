<?php
namespace App\Models;
use App\AppTraits\SettingTrait;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use MongoDB\Client as MongoDBClient;

class BaseModel extends Eloquent
{
    use SettingTrait;
    protected $table;
    protected $database;

    private $connectionString = '';
    private $auth;

    public function __construct($database = null, $table = null)
    {
        $this->connectionString = env('MG_DB_CONNECTION') . '://' . env('MG_DB_HOST') . ':' . env('MG_DB_PORT');
        $this->auth = [
            'username' => env('MG_DB_USERNAME', ''),
            'password' => env('MG_DB_PASSWORD', ''),
        ];
    }


    public function genMemId()
    {
        return md5(uniqid(mt_rand(), true));
    }
    public function getDatabase()
    {
        return $this->database;
    }
    public function getCollection()
    {
        return $this->table;
    }

    public function getClient()
    {
        $client = new MongoDBClient($this->connectionString, $this->auth);
        return $client->selectCollection($this->database, $this->table);
    }
}