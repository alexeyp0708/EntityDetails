<?php
/**
 * Created by PhpStorm.
 * User: AlexeyP
 * Date: 25.02.2021
 * Time: 17:41
 */

namespace Alpa\EntityDetails\Proxy;

use Alpa\Tools\ProxyObject\Handlers\InstanceActions;
use Alpa\Tools\ProxyObject\Handlers\StaticActions;
use Alpa\Tools\ProxyObject\Proxy;

class ObjectInformantProxy extends StaticActions
{
    protected static $access_prop_list = [
        //'name',
        //'file',
        //'interfaces',
        //'traits',
        //'parent',
        'constants',
        'methods',
        'properties',
        'class'
    ];

    protected static function get($target, string $prop, $value_or_args = null, Proxy $proxy)
    {
        static::validateTargetType($target);
        throw new \Exception("No access");
    }

    protected static function set($target, string $prop, $value_or_args, Proxy $proxy): void
    {
        static::validateTargetType($target);
        throw new \Exception("No access");
    }

    protected static function isset($target, string $prop, $value_or_args = null, Proxy $proxy): bool
    {
        static::validateTargetType($target);
        return in_array($prop, static::$access_prop_list);
    }

    protected static function unset($target, string $prop, $value_or_args = null, Proxy $proxy): void
    {
        static::validateTargetType($target);
        throw new \Exception("No access");
    }

    protected static function call($target, string $prop, array $value_or_args = [], Proxy $proxy)
    {
        static::validateTargetType($target);
        throw new \Exception("No access");
    }

    protected static function invoke($target, $prop = null, array $value_or_args = [], Proxy $proxy)
    {
        static::validateTargetType($target);
        throw new \Exception("No access");
    }

    protected static function toString($target, $prop = null, $value_or_args = null, Proxy $proxy): string
    {
        static::validateTargetType($target);
        throw new \Exception("No access");
    }

    protected static function iterator($target, $prop = null, $value_or_args = null, Proxy $proxy): \Traversable
    {
        static::validateTargetType($target);
        $props = static::$access_prop_list;
        return new class ($props, $proxy) implements \Iterator {
            protected array $props = [];
            protected Proxy $proxy;
            protected int $key = 0;

            public function __construct(array $props, Proxy $proxy)
            {
                $this->props = $props;
                $this->proxy = $proxy;
            }

            public function rewind()
            {
                $this->key = 0;
            }

            public function key()
            {
                return $this->props[$this->key];
            }

            public function current()
            {
                $prop = $this->key();
                return $this->proxy->$prop;
            }

            public function next()
            {
                $this->key++;
            }

            public function valid(): bool
            {
                return isset($this->props[$this->key]);
            }
        };
    }

    protected static function get_constants($target, string $prop, $value_or_args = null, Proxy $proxy)
    {
        static::validateTargetType($target);
        return $target->genConstants();
    }

    protected static function get_methods($target, string $prop, $value_or_args = null, Proxy $proxy)
    {
        static::validateTargetType($target);
        return $target->genMethods();
    }

    protected static function get_properties($target, string $prop, $value_or_args = null, Proxy $proxy)
    {
        static::validateTargetType($target);
        return $target->genMethods();
    }

    protected static function get_class($target, string $prop, $value_or_args = null, Proxy $proxy)
    {
        static::validateTargetType($target);
        return $target->genClass();
    }

    protected static function validateTargetType($target)
    {
    }
};
