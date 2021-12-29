<?php

namespace Alpa\EntityDetails\Proxy;

use Alpa\Tools\ProxyObject\Proxy;

class ObjectInformant extends ClassInformant
{
    use \Alpa\EntityDetails\TObjectInformant;

    public  static function newObject (&$class,$is_recursive=false):Proxy
    {
        $info= \Alpa\EntityDetails\TObjectInformant::newObject($class,$is_recursive);
        $ProxyClass=static::class.'Proxy';
        return $ProxyClass::proxy($info);
    }
    
}