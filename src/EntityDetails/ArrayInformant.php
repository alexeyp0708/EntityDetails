<?php


namespace Alpa\EntityDetails;


use Alpa\Tools\ArrayClone\ArrayClone;

class ArrayInformant implements IInformant
{
    protected array $observed;
    protected bool $is_recursive=false;
    private static ?string $mark=null;
    private static bool $is_find=true;
    private static array $stack=[];
    public function __construct(array &$observed, bool $is_recursive = false)
    {
        $this->init();
        $this->is_recursive=$is_recursive;
        $this->observed=&$observed;
    }
    protected function init()
    {
        $this->initElements();
    }

    public static function newObject(&$observed, bool $is_recursive = false): IInformant
    {
        // TODO: Implement newObject() method.
        return new static($observed,$is_recursive);
    }

    protected function initElements()
    {
        $this->elements=[];
        $mark=$this->mark($this->observed);
        $this->observed[$mark]=&$this->elements;
        $is_find=self::$is_find;
        if($is_find){
            self::$is_find=false;
        }
        try {
            foreach ($this->observed as $key=>& $value){
                 if(is_array($value)){
                     $mark_val = $this->getmark($value);
                     if($mark_val!==null){
                         $this->elements[$key]=&$value[$mark_val];
                     } else {
                         if($this->is_recursive){
                             $this->elements[$key]=static::newObject($value,true);
                         } else {
                             //$this->elements[$key]=&$value;
                             $cloner=new ArrayClone();
                             $this->elements[$key]=$cloner->clone($value); //or clone
                         }    
                     }
                 }  else if(is_object($value)){
                     $this->elements[$key]=ObjectInformant::newObject($value,$this->is_recursive);
                 } else {
                     $this->elements[$key]=$value;
                 }             
            }
        }  finally {
            if($is_find){
                self::$is_find=true;
                self::restoreData();
            }
        }
    }

    private static function mark(&$array): string
    {
        if(static::$mark===null){
            static::$mark=uniqid(); 
        }
        $mark = self::$mark;
        while (array_key_exists($mark, $array)) {
            $mark .= '@&';
        };
        $array[$mark] = null;
        $data = &$array;
        self::addInStack($array);
        return $mark;
    }

    private function getmark($array): ?string
    {
        $mark =  self::$mark;
        $count = 0;
        while (!array_key_exists($mark, $array) && $count < 5) {
            $mark .= '@&';
            $count++;
        }
        if (!array_key_exists($mark, $array)) {
            $mark = null;
        }
        return $mark;
    }

    private function unmark(&$array)
    {
        $mark = self::getmark($array);
        if ($mark !== null) {
            unset($array[$mark]);
        }
    }

    private static function addInStack(&$data)
    {
        self::$stack[] = &$data;
    }

    private static function restoreData()
    {
        foreach (self::$stack as $key => &$value) {
            self::unmark($value);
            unset(self::$stack[$key]);
        }
        unset($value);
        self::$stack=[];
        self::$mark=null;
    }
}