<?php


namespace Alpa\EntityDetails;


use Alpa\Tools\ArrayClone\ArrayClone;

class ArrayInformant implements IInformant
{
    protected array $observed;
    protected bool $is_recursive = false;
    private static ?string $token_array_key = null;
    private static bool $is_find = true;
    private static array $stack = [];
    public array $elements = [];

    public function __construct(array &$observed, bool $is_recursive = false)
    {
        $this->init();
        $this->is_recursive = $is_recursive;
        $this->observed =& $observed;
    }

    protected function init()
    {
        $this->initElements();
    }

    public static function newObject(&$observed, bool $is_recursive = false): IInformant
    {
        // TODO: Implement newObject() method.
        return new static($observed, $is_recursive);
    }

    protected function initElements()
    {
        $this->elements = [];
        $token = self::markArray($this->observed);
        $this->observed[$token] =& $this->elements;
        $is_find = self::$is_find;
        if ($is_find) {
            self::$is_find = false;
        }
        try {
            foreach ($this->observed as $key => & $value) {
                if (is_array($value)) {
                    $token_val = self::getTokenArray($value);
                    if ($token_val !== null) {
                        $this->elements[$key] =& $value[$token_val];
                    } else {
                        if ($this->is_recursive) {
                            $this->elements[$key] = static::newObject($value, true);
                        } else {
                            //$this->elements[$key]=&$value;
                            $cloner = new ArrayClone();
                            $this->elements[$key] = $cloner->clone($value); //or clone
                        }
                    }
                } else if (is_object($value)) {
                    $this->elements[$key] = ObjectInformant::newObject($value, $this->is_recursive);
                } else {
                    $this->elements[$key] = $value;
                }
            }
        } finally {
            if ($is_find) {
                self::$is_find = true;
                self::restoreData();
            }
        }
    }
     
    private static function initTokenKey():string
    {
        if (static::$token_array_key === null) {
            static::$token_array_key ='_'. uniqid();
        }
        return static::$token_array_key;
    }

    /**
     * @param array $array
     * @return string    The token must be unique.
     * if, by coincidence, there is an element in the array whose key matches the marking,
     * then the marking will be modified to a unique marker.
     */
    private static function generateUniqTokenKey(array $array)
    {
        $token_array_key=static::initTokenKey();
        while (array_key_exists($token_array_key, $array)) {
            $token_array_key = '#'.$token_array_key;
        };
        return $token_array_key;
    }

    /**
     * marks an array with a unique key and  return  key
     * @param $array
     * @return string
     */
    private static function markArray(&$array): string
    {
        $token_array_key=static::generateUniqTokenKey($array);
        $array[$token_array_key] = null;
        //self::addInStack($array);
        return $token_array_key;
    }

    /**
     * @param $array
     * @return string|null
     */
    private static function getTokenArray($array): ?string
    {
        $token=null;
        $last_token=self::$token_array_key;
        while(array_key_exists($last_token, $array)){
            $token=$last_token;
            $last_token='#'.$token;
        }
        return $token;
    }
    
    private static function unmarkArray(&$array)
    {
        $token = self::getTokenArray($array);
        if ($token !== null) {
            unset($array[$token]);
        }
    }

    private static function addInStack(&$data)
    {
        self::$stack[] = &$data;
    }

    private static function restoreData()
    {
        foreach (self::$stack as $key => &$value) {
            self::unmarkArray($value);
            unset(self::$stack[$key]);
        }
        unset($value);
        self::$stack = [];
        self::$token_array_key = null;
    }
}