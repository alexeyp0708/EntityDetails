<?php

namespace Alpa\EntityDetails\Tests;

use Alpa\EntityDetails\Informant;
use Alpa\EntityDetails\ClassInformant;
use Alpa\EntityDetails\ObjectInformant;
use Alpa\EntityDetails\Tests\Constraints\Asserts;
use Alpa\EntityDetails\Tests\Fixtures\ExampleChildClass;
use Alpa\EntityDetails\Tests\Fixtures\ExampleClass;
use PHPUnit\Framework\TestCase;


class ObjectInformantTest extends TestCase
{
    use Asserts;

    protected static $fixtures = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $observed = new ExampleChildClass();
        $info = new class($observed) extends ObjectInformant {
            public static function setCache($data, Informant $object)
            {
                parent::setCache($data, $object);
            }

            public static function getCache($data): ?Informant
            {
                return parent::getCache($data);
            }

            public function initClass()
            {
                return parent::initClass();
            }

            public function initConstants()
            {
                parent::initConstants();
            }

            public function initMethods()
            {
                parent::initMethods();
            }

            public function initProperties()
            {
                parent::initProperties();
            }
        };
        $reflect = new \ReflectionObject($info);
        $instance = $reflect->newInstanceWithoutConstructor();
        $instance->initObserved($observed, true);
        static::$fixtures['data_informant'] = [
            'reflect' => $reflect,
            'class' => get_class($info),
            'observed' => $observed,
            'instance' => $instance
        ];
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {

    }

    public function test_setCache_and_getCache()
    {
        $test = new class {
        };
        $class = static::$fixtures['data_informant']['class'];
        $reflect = static::$fixtures['data_informant']['reflect'];
        $cache = $reflect->getProperty('cache');
        $cache->setAccessible(true);
        $instance1 = $reflect->newInstanceWithoutConstructor();
        $this->assertTrue(!isset($cache->getValue()[spl_object_hash($test)]));
        $class::setCache($test, $instance1);
        $this->assertTrue(isset($cache->getValue()[spl_object_hash($test)]));
        $instance2 = $class::getCache($test);
        $this->assertTrue($instance1 === $instance2);
    }

    public function test_initClass()
    {
        $instance = static::$fixtures['data_informant']['instance'];
        $observed = static::$fixtures['data_informant']['observed'];
        $instance->initClass();
        $this->assertTrue($instance->class instanceof ClassInformant
            && $instance->class->name === get_class($observed)
        );
    }

    public function test_initProperties()
    {
        $instance = static::$fixtures['data_informant']['instance'];
        $observed = static::$fixtures['data_informant']['observed'];
        $this->assertTrue(count($instance->properties) === 0);
        $this->assertTrue(count($instance->native_properties) === 0);
        $instance->initProperties();
        $this->assertTrue(
            $instance->properties['parentProp']['owner'] === ExampleClass::class
            && $instance->properties['parentProp']['value'] === 'hello'
            && $instance->properties['publicProp']['owner'] === ExampleChildClass::class
            && $instance->properties['publicProp']['value'] === 'hello'
            && $instance->properties['* protectedProp']['owner'] === ExampleChildClass::class
            && $instance->properties['* protectedProp']['value'] === 'hello'
            && $instance->properties['** privateProp']['owner'] === ExampleChildClass::class
            && $instance->properties['** privateProp']['value'] === 'hello'
        );
        $this->assertTrue(
            isset($instance->native_properties['publicProp'])
            && isset($instance->native_properties['* protectedProp'])
            && isset($instance->native_properties['** privateProp'])
            && isset($instance->native_properties['%** privateStaticProp'])
            && !isset($instance->native_properties['parentProp'])
        );        
        // поле проерка на рекурсивный анализ массивов и обьектов
    }

    public function _test_()
    {

    }
}