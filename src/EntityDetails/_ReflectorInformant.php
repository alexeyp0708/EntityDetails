<?php
/**
 * Created by PhpStorm.
 * User: AlexeyP
 * Date: 25.02.2021
 * Time: 17:41
 */

namespace Alpa\EntityDetails;


abstract class ReflectorInformant implements IInformant
{
    protected ?Reflector $reflect=null;
    protected $observed;
    protected bool $is_recursive=false;
    
    function __construct($observed,$is_recursive=false)
    {
        if($observed instanceof static)
        {
            throw new \Exception('Argument[0] to constructor must not belong to class '.static::class);
        }
        $this->initObserved($observed);
        $this->init();
        static::setCache($observed,$this);
    }
    
    protected function initObserved($observed,$is_recursive=false)
    {
        $reflect=static::getReflector($observed);
        $this->reflect=$reflect;
        $this->is_recursive=$is_recursive;
        $this->observed=$observed;
    }
    public function getObserved()
    {
        return $this->observed;
    }
    
    /**
     * @param object|string $observed
     * @return ReflectionClass
     * @throws \ReflectionException
     */
    public static function getReflectionClass($observed): ReflectionClass
    {
        /*if($observed instanceof Reflector){            
            return $observed;
        }*/
        if($observed instanceof \ReflectionClass){
            $observed = $observed->getName();
        }
        return  new ReflectionClass($observed);
    }

    /**
     * @param object $observed
     * @return ReflectionObject
     */
    public static function getReflectionObject(object $observed) : ReflectionObject
    {
       /* if($observed instanceof Reflector){
            return $observed;
        }*/
        return  new ReflectionObject($observed);
    }


    /**
     * @param ReflectionObject|ReflectionClass|object|string $observed
     * @param bool $parseObjectClass
     * @return ReflectionObject|ReflectionClass
     * @throws \ReflectionException
     */

    public static function getReflector ($observed,bool $parseObjectClass=false):? Reflector
    {
        if($observed instanceof Reflector){
            return $observed;
        }
        if(is_object($observed)){
            if($parseObjectClass){
                return static::getReflectionClass($observed);
            } else {
                return static::getReflectionObject($observed);
            }
        } else if(is_string($observed)){
            return static::getReflectionClass($observed);
        }
        return null;
    }
    /**
     * @param object|string $class
     * @param false $is_recursive
     * @return IInformant
     * @throws \Exception
     */
    public  static function newObject (&$observed,bool $is_recursive=false):IInformant
    {
        return static::getCache($observed)??new static($observed,$is_recursive);
    }
    abstract protected static function getCache($data): ? IInformant;
    abstract protected static function setCache($data, IInformant $object);
    abstract protected function init();
}