<?php
/**
 * Created by PhpStorm.
 * User: AlexeyP
 * Date: 25.02.2021
 * Time: 17:41
 */

namespace Alpa\EntityDetails\Proxy;

use Alpa\ProxyObject\Handlers\InstanceActions;
use Alpa\ProxyObject\Proxy;

class ClassInformantProxy extends ObjectInformantProxy
{
    protected static $access_prop_list=[
        'name',
        'file',
        'interfaces',
        'traits',
        'parent',
        'constants',
        'methods',
        'properties'
    ];
  
    protected static function get_name($target, string $prop, $value_or_args = null, Proxy $proxy)
    {
        static ::validateTargetType($target);
        return $target->genName();
    }    
    protected static function get_file($target, string $prop, $value_or_args = null, Proxy $proxy)
    {
        static ::validateTargetType($target);
        return $target->genName();
    }  
    protected static function get_interfaces($target, string $prop, $value_or_args = null, Proxy $proxy)
    {
        static ::validateTargetType($target);
        return $target->genInterfaces();
    } 
    protected static function get_traits($target, string $prop, $value_or_args = null, Proxy $proxy)
    {
        static ::validateTargetType($target);
        return $target->genTraits();
    }
    protected static function get_parent($target, string $prop, $value_or_args = null, Proxy $proxy)
    {
        static ::validateTargetType($target);
        return $target->genParentClass();
    }    
 
    protected static function get_class($target, string $prop, $value_or_args = null, Proxy $proxy)
    {
        static ::validateTargetType($target);
        throw new \Exception("No access");
    }
    protected static function validateTargetType($target){}
}

;
