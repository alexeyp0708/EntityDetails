<?php

namespace Alpa\EntityDetails\Proxy;

use Alpa\EntityDetails\IInformant;
use Alpa\EntityDetails\TReflectorInformant;
use Alpa\Tools\ProxyObject\Proxy;

class ClassInformant implements IInformant
{
    use \Alpa\EntityDetails\TClassInformant;
    protected function init()
    {

    }
    public static function getClasses():array
    {
        $classes=TReflectorInformant::getClasses();
        return array_replace($classes,[
            'ClassInformant'=>ClassInformant::class,
            'ObjectInformant'=>ObjectInformant::class,
            //'ArrayInformant'=>ArrayInformant::class
        ]);
    }

    /**
     * @param $class
     * @param false $is_recursive
     * @return Proxy
     */
    public  static function newObject (&$class,$is_recursive=false):Proxy
    {
        $info= \Alpa\EntityDetails\TClassInformant::newObject($class,$is_recursive);
        $ProxyClass=static::class.'Proxy';
        return $ProxyClass::proxy($info);
    }
}