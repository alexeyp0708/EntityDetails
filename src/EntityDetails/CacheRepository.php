<?php


namespace Alpa\EntityDetails;


class CacheRepository
{
    private static array $cache=[];
    public static function getCache(string $name)
    {
        return self::$cache[$name]??null;
    }
    public static function setCache(string $name,$data)
    {
        self::$cache[$name]=$data;
    } 
    public static function getAllCache()
    {
        return Static::$cache;
    }
}