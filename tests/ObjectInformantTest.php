<?php

namespace Alpa\EntityDetails\Tests;

use Alpa\EntityDetails\CacheRepository;
use Alpa\EntityDetails\IInformant;
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
            public static function setCache($data, IInformant $object)
            {
                parent::setCache($data, $object);
            }

            public static function getCache($data): ?IInformant
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
            public function initObserved($observed,$is_recursive=false)
            {
                parent::initObserved($observed,$is_recursive);
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
        $instance1 = $reflect->newInstanceWithoutConstructor();
        $this->assertTrue(null===CacheRepository::getCache(spl_object_hash($test)));
        $class::setCache($test, $instance1);
        $this->assertTrue(CacheRepository::getCache(spl_object_hash($test))===$instance1);
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
        $this->assertTrue(count($instance->properties['all']) === 0);
        $this->assertTrue(count($instance->properties['native']) === 0);
        $instance->initProperties();
        $this->assertTrue(
            $instance->properties['all']['parentProp']['owner'] === ExampleClass::class
            && $instance->properties['all']['parentProp']['value'] === 'hello'
            && $instance->properties['all']['publicProp']['owner'] === ExampleChildClass::class
            && $instance->properties['all']['publicProp']['value'] === 'hello'
            && $instance->properties['all']['* protectedProp']['owner'] === ExampleChildClass::class
            && $instance->properties['all']['* protectedProp']['value'] === 'hello'
            && $instance->properties['all']['** privateProp']['owner'] === ExampleChildClass::class
            && $instance->properties['all']['** privateProp']['value'] === 'hello'
            && $instance->properties['all']['publicObjectProp']['owner']===ExampleChildClass::class
            && $instance->properties['all']['publicObjectProp']['type']===ExampleClass::class
            && $instance->properties['all']['publicObjectProp']['value'] instanceof ObjectInformant
            && $instance->properties['all']['publicObjectProp']['value']->class->name===ExampleClass::class
            && $instance->properties['all']['% publicStaticObjectProp']['owner']===ExampleChildClass::class
            && $instance->properties['all']['% publicStaticObjectProp']['type']===ExampleClass::class
            && $instance->properties['all']['% publicStaticObjectProp']['value'] instanceof ObjectInformant
            && $instance->properties['all']['% publicStaticObjectProp']['value']->class->name===ExampleClass::class

        );
        $this->assertTrue(
            isset($instance->properties['native']['publicProp'])
            && isset($instance->properties['native']['* protectedProp'])
            && isset($instance->properties['native']['** privateProp'])
            && isset($instance->properties['native']['%** privateStaticProp'])
            && !isset($instance->properties['native']['parentProp'])
        );
    }
    public function test_initProperties_recursive()
    {
        
    }
    public function _test_()
    {

    }
}