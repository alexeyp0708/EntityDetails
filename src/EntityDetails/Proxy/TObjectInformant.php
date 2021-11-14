<?php


namespace Alpa\EntityDetails\Proxy;

use Alpa\ProxyObject\Proxy;

trait TObjectInformant
{
    use \Alpa\EntityDetails\TObjectInformant;
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
        $info= parent::newObject($class,$is_recursive);
        $ProxyClass=static::class.'Proxy';
        return $ProxyClass::proxy($info);
    }
}
