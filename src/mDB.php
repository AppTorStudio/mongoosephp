<?php

namespace Mongoose;

use MongoDB\Client;
use MongoDB\Collection;

class mDB
{

    private static $client;
    private static $collection;
    private static $connectOptions;

    public static function setConnectOptions($connectOptions)
    {

        self::$connectOptions = $connectOptions;
    }

    public static function connect($connectOptions = null)
    {
        echo "connect";

        if ($connectOptions) {
            self::$connectOptions = $connectOptions;
        }

        $params = [
            "username"         => self::$connectOptions["username"] ?? null,
            "authSource"       => self::$connectOptions["authSource"] ?? null,
            "password"         => self::$connectOptions["password"] ?? null,
            "connectTimeoutMS" => self::$connectOptions["connectTimeoutMS"] ?? null,
            "socketTimeoutMS"  => self::$connectOptions["socketTimeoutMS"] ?? null,
            'poolSize'         => self::$connectOptions["poolSize"] ?? null,
        ];

        self::$client = new Client(self::$connectOptions["server"] ?? null, $params);

        $db               = self::$connectOptions["database"] ?? null;
        self::$collection = self::$client->$db;

    }

    public static function id($id)
    {
        if (gettype($id) == 'string') {
            if (preg_match('/^[0-9a-f]{24}$/i', $id) === 1) {
                return $id ? new \MongoDB\BSON\ObjectID($id) : new \MongoDB\BSON\ObjectID();
            } else {
                return false;
            }
        } else {
            return $id;
        }

    }

    public static function collection($collection): Collection
    {
        if (!isset(self::$client)) {
            self::connect();
        }

        return self::$collection->$collection;
    }

    public static function normalization($array)
    {

        if (is_object($array) && get_class($array) == "MongoDB\Driver\Cursor") {
            $array = $array->toArray();
        }

        foreach ($array as $element) {
            if (is_array($element)) {
                $element = self::normalization($element);
            } else if (is_object($element) && get_class($element) == "MongoId") {
                $element = (string) $element;
            }
        }
        return $array;
    }

    public static function denormalization($array)
    {

        foreach ($array as $key => &$val) {
            if (in_array(gettype($val), ['object', 'array'])) {
                $val = self::denormalization($val);
            } else {
                if (gettype($val) == 'string' && strlen($val) == 24) {
                    $val = mDB::id($val);
                }

            }
        }
        return $array;

    }

}