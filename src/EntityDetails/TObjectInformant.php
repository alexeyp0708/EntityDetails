<?php


namespace Alpa\EntityDetails;


trait TObjectInformant
{
    use TReflectorInformant;

    protected static function getCache($object): ?IInformant
    {
        if (is_object($object)) {
            $object = spl_object_hash($object);
        }
        return CacheRepository::getCache($object);
    }

    protected static function setCache($hash, IInformant $object)
    {
        if (is_object($hash)) {
            $hash = spl_object_hash($hash);
            CacheRepository::setCache($hash,$object);
        }
    }
}