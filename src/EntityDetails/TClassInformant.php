<?php


namespace Alpa\EntityDetails;


trait TClassInformant
{
    use TReflectorInformant;
    protected static function getCache($hash): ?IInformant
    {
        $class_name = $hash;
        if (is_object($class_name)) {
            $class_name = get_class($class_name);
        }
        return CacheRepository::getCache($class_name);
    }

    protected static function setCache($hash, IInformant $object)
    {
        $class_name = $hash;
        if (is_object($class_name)) {
            $class_name = get_class($class_name);
        }
        CacheRepository::setCache($class_name,$object);
    }
}