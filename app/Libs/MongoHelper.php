<?php
namespace App\Libs;

use MongoDB\Client as MongoClient;
use MongoDB\Driver\Command;
use MongoDB\Driver\Exception\Exception;
//use MongoDB\Driver\Manager;
//use MongoDB\Driver\Query;

class MongoHelper
{
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

    /**
     * Find one
     * @param  [type]     $database    [description]
     * @param  [type]     $table       [description]
     * @param  array      $queryString [description]
     * @param  array      $options     [description]
     * @return [type]                  [description]
     */
    public function findOne($database, $table, $queryString = [], $options = [])
    {
        try {
            $mgClient = new MongoClient($this->connectionString, $this->auth);
            $collection = $mgClient->$database->$table;
            $result = $collection->findOne($queryString);

            return $result;
        }
        catch (Exception $e) {
            $this->catchException($e);
        }
    }

    /**
     * Execute command
     * @param  [type]     $database [description]
     * @param  [type]     $query    [description]
     * @return [type]               [description]
     */
    public function excuteCommand($database, $query)
    {
        $mgClient = new MongoClient($this->connectionString, $this->auth);
        $manager = $mgClient->getManager();
        $command = new \MongoDB\Driver\Command($query);
        return $manager->executeCommand($database, $command);
    }

    /**
     * Create Index
     * @param  [type]     $database [description]
     * @param  [type]     $table    [description]
     * @param  [type]     $index    [description]
     * @param  array      $option   [description]
     * @return [type]               [description]
     */
    public function createIndex($database, $table, $index, $option=[])
    {
        try {
            $mgClient = new MongoClient($this->connectionString, $this->auth);
            $collection = $mgClient->$database->$table;
            $result = $collection->createIndex($index, $option);

            return $result;
        }
        catch (Exception $e) {
            $this->catchException($e);
        }
    }

    /**
     * Count query
     * @param  [type]     $database [description]
     * @param  [type]     $table    [description]
     * @param  array      $options  [description]
     * @return [type]               [description]
     */
    public function count($database, $table, $options = [])
    {
        try {
            $queryString = [
                [
                    '$group' => [
                        '_id' => null,
                        'count' => [
                            '$sum' => 1
                        ]
                    ]
                ]
            ];
            $result = current($this->aggregate($database, $table, $queryString));

            return isset($result->count) ? $result->count : 0;

        }
        catch (Exception $e) {
            $this->catchException($e);
        }
    }

    /**
     * Max
     * @param  [type]     $database [description]
     * @param  [type]     $table    [description]
     * @param  [type]     $column   [description]
     * @param  array      $options  [description]
     * @return [type]               [description]
     */
    public function max($database, $table, $column, $options = [])
    {
        try {
            if(strlen($column) <= 0) {
                return -1;
            }

            $queryString = [
                [
                    '$group' => [
                        '_id' => null,
                        'max' => [
                            '$max' => "$$column"
                        ]
                    ]
                ]
            ];
            $result = current($this->aggregate($database, $table, $queryString));
            return isset($result->max) ? $result->max : 0;
        }
        catch (Exception $e) {
            $this->catchException($e);
        }
    }

    /**
     * Min
     * @param  [type]     $database [description]
     * @param  [type]     $table    [description]
     * @param  [type]     $column   [description]
     * @param  array      $options  [description]
     * @return [type]               [description]
     */
    public function min($database, $table, $column, $options = [])
    {
        try
        {
            if(strlen($column) <= 0) {
                return -1;
            }
            $queryString = [
                [
                    '$group' => [
                        '_id' => null,
                        'min' => [
                            '$min' => "$$column"
                        ]
                    ]
                ]
            ];
            $result = current($this->aggregate($database, $table, $queryString));
            return isset($result->min) ? $result->min : 0;
        }
        catch (Exception $e)
        {
            $this->catchException($e);
        }
    }

    /*
    WHERE	        $match
    GROUP BY	    $group
    HAVING	        $match
    SELECT	        $project
    ORDER BY	    $sort
    LIMIT	        $limit
    SUM()	        $sum
    COUNT()	        $sum
    join            $lookup
    $unwind duplicates each document in the pipeline, once per array element.
    https://docs.mongodb.com/manual/reference/sql-aggregation-comparison/
    https://docs.mongodb.com/manual/reference/operator/aggregation/lookup/
    http://stackoverflow.com/questions/36277826/join-more-than-one-field-using-aggregate-lookup
    http://stackoverflow.com/questions/35813854/how-to-join-multiple-collections-with-lookup-mongodb
    https://namvuhn.wordpress.com/2015/11/16/mongodb-tim-hieu-aggregation-framework-trong-mongodb/
    http://stackoverflow.com/questions/39091662/mongodb-aggregate-with-group-and-lookup
    https://www.mongodb.com/blog/post/joins-and-other-aggregation-enhancements-coming-in-mongodb-3-2-part-1-of-3-introduction
    */
   /**
    * Aggregate
    * @param  [type]     $database    [description]
    * @param  [type]     $table       [description]
    * @param  array      $queryString [description]
    * @param  array      $options     [description]
    * @return [type]                  [description]
    */
    public function aggregate($database, $table, $queryString = [], $options = [])
    {
        try {
            $mgClient = new MongoClient($this->connectionString, $this->auth);
            $collection = $mgClient->$database->$table;

            $result = $collection->aggregate($queryString, $options);
            return $result->toArray();
        }
        catch (Exception $e) {
            $this->catchException($e);
        }
    }

    /*
     * $options = [
     * "projection" => ['_id' => 0]
     * 'sort' => [ 'name' => 1], 'limit' => 5 => 1 asc; -1 desc
     * ];
     *
     * */
    /**
     * Find many
     * @param  [type]     $database    [description]
     * @param  [type]     $table       [description]
     * @param  array      $queryString [description]
     * @param  array      $options     [description]
     * @return [type]                  [description]
     */
    public function findMany($database, $table, $queryString = [], $options = [])
    {
        try {
            $mgClient = new MongoClient($this->connectionString, $this->auth);
            $collection = $mgClient->$database->$table;
            $result = $collection->find($queryString, $options);

            return $result->toArray();
        }
        catch (Exception $e) {
            $this->catchException($e);
        }
    }

    /**
     * All
     * @param  [type]     $database [description]
     * @param  [type]     $table    [description]
     * @param  array      $options  [description]
     * @return [type]               [description]
     */
    public function all($database, $table, $options = [])
    {
        try {
            $mgClient = new MongoClient($this->connectionString, $this->auth);
            $collection = $mgClient->$database->$table;
            $result = $collection->find([], $options);

            return $result->toArray();
        }
        catch (Exception $e) {
            $this->catchException($e);
        }
    }

    /**
     * Insert one
     * @param  [type]     $database [description]
     * @param  [type]     $table    [description]
     * @param  array      $data     [description]
     * @param  array      $options  [description]
     * @return [type]               [description]
     */
    public function insertOne($database, $table, $data = [], $options = [])
    {
        try {
            $mgClient = new MongoClient($this->connectionString, $this->auth);
            $collection = $mgClient->$database->$table;
            $insertOneResult = $collection->insertOne($data);

            return $insertOneResult->getInsertedId();
        }
        catch (Exception $e) {
            $this->catchException($e);
        }
    }

    /**
     * Insert many
     * @param  [type]     $database [description]
     * @param  [type]     $table    [description]
     * @param  array      $data     [description]
     * @param  array      $options  [description]
     * @return [type]               [description]
     */
    public function insertMany($database, $table, $data = [], $options = [])
    {
        try {
            $mgClient = new MongoClient($this->connectionString, $this->auth);
            $collection = $mgClient->$database->$table;
            $result = $collection->insertMany($data);

            return $result->getInsertedCount();
        }
        catch (Exception $e) {
            $this->catchException($e);
        }
    }

    /**
     * Update one
     * @param  [type]     $database  [description]
     * @param  [type]     $table     [description]
     * @param  [type]     $condition [description]
     * @param  array      $update    [description]
     * @param  array      $options   [description]
     * @return [type]                [description]
     */
    public function updateOne($database, $table, $condition, $update = [], $options = [])
    {
        try {
            $mgClient = new MongoClient($this->connectionString, $this->auth);
            $collection = $mgClient->$database->$table;
            $updateResult = $collection->updateOne($condition, ['$set' => $update]);

            return $updateResult->getModifiedCount();
        }
        catch (Exception $e) {
            $this->catchException($e);
        }
    }

    /**
     * Update many
     * @param  [type]     $database  [description]
     * @param  [type]     $table     [description]
     * @param  [type]     $condition [description]
     * @param  array      $update    [description]
     * @param  array      $options   [description]
     * @return [type]                [description]
     */
    public function updateMany($database, $table, $condition, $update = [], $options = [])
    {
        try {
            $mgClient = new MongoClient($this->connectionString, $this->auth);
            $collection = $mgClient->$database->$table;
            $result = $collection->updateMany($condition, ['$set' => $update]);

            return $result->getModifiedCount();
        }
        catch (Exception $e) {
            $this->catchException($e);
        }
    }

    /**
     * Delete one
     * @param  [type]     $database  [description]
     * @param  [type]     $table     [description]
     * @param  [type]     $condition [description]
     * @param  array      $options   [description]
     * @return [type]                [description]
     */
    public function deleteOne($database, $table, $condition, $options = [])
    {
        try {
            $mgClient = new MongoClient($this->connectionString, $this->auth);
            $collection = $mgClient->$database->$table;
            $result = $collection->deleteOne($condition);

            return $result->getDeletedCount();
        }
        catch (Exception $e) {
            $this->catchException($e);
        }
    }

    /**
     * Delete many
     * @param  [type]     $database  [description]
     * @param  [type]     $table     [description]
     * @param  [type]     $condition [description]
     * @param  array      $options   [description]
     * @return [type]                [description]
     */
    public function deleteMany($database, $table, $condition, $options = [])
    {
        try {
            $mgClient = new MongoClient($this->connectionString,$this->auth);
            $collection = $mgClient->$database->$table;
            $result = $collection->deleteMany($condition);

            return $result->getDeletedCount();
        }
        catch (Exception $e) {
            $this->catchException($e);
        }
    }

    /**
     * Find one and replace
     * @param  [type]     $database  [description]
     * @param  [type]     $table     [description]
     * @param  [type]     $condition [description]
     * @param  array      $replace   [description]
     * @param  array      $options   [description]
     * @return [type]                [description]
     */
    public function findOneAndReplace($database, $table, $condition ,$replace = [], $options = [])
    {
        try {
            $mgClient = new MongoClient($this->connectionString,$this->auth);
            $collection = $mgClient->$database->$table;

            $result = $collection->findOneAndReplace(
                $condition,
                $replace,
                [ 'returnDocument' => \MongoDB\Operation\FindOneAndReplace::RETURN_DOCUMENT_AFTER ]
            );

            return $result;
        }
        catch (Exception $e) {
            $this->catchException($e);
        }
    }

    /**
     * Catch Exception
     * @param  [type]     $e [description]
     * @return [type]        [description]
     */
    public function catchException($e)
    {
        $filename = basename(__FILE__);
        echo "The $filename script has experienced an error.<br>";
        echo "It failed with the following exception:<br>";
        echo "Exception:", $e->getMessage(), "<br>";
        echo "In file:", $e->getFile(), "<br>";
        echo "On line:", $e->getLine(), "<br>";
        die();
    }
}
