<?php

namespace Alpa\EntityDetails\Tests;

use Alpa\EntityDetails\CacheRepository;
use Alpa\EntityDetails\ClassInformant;
use Alpa\EntityDetails\IInformant;
use Alpa\EntityDetails\ObjectInformant;
use Alpa\EntityDetails\ReflectorInformant;
use Alpa\EntityDetails\Tests\Constraints\Asserts;
use Alpa\EntityDetails\Tests\Fixtures\ExampleChildClass;
use Alpa\EntityDetails\Tests\Fixtures\ExampleClass;
use Alpa\EntityDetails\Tests\Fixtures\ExampleInterface;
use Alpa\EntityDetails\Tests\Fixtures\ExampleTrait;
use PHPUnit\Framework\TestCase;


class ClassInformantTest extends TestCase
{
    use Asserts;

    protected static $fixtures = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $observed = ExampleChildClass::class;
        $info = new class($observed) extends ClassInformant {
            public static function setCache($data, IInformant $object)
            {
                parent::setCache($data, $object);
            }

            public static function getCache($data): ?IInformant
            {
                return parent::getCache($data);
            }

            public function initName()
            {
                return parent::initName();
            }

            public function initFile()
            {
                parent::initFile();
            }

            public function initInterfaces()
            {
                parent::initInterfaces();
            }

            public function initTraits()
            {
                parent::initTraits();
            }

            public function initParentClass()
            {
                parent::initParentClass();
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
        $reflect = new \ReflectionClass($info);
        $instance = $reflect->newInstanceWithoutConstructor();
        $instance->initObserved($observed, true);
        static::$fixtures['data_informant'] = [
            'class' => get_class($info),
            'observed' => $observed,
            'instance' => $instance,
            'reflect' => $reflect,
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
        $test_class = new class {
        };
        $class = static::$fixtures['data_informant']['class'];
        $reflect = new \ReflectionClass($class);

        $cache = CacheRepository::getAllCache();
        $instance1 = $reflect->newInstanceWithoutConstructor();
        $this->assertTrue(null===CacheRepository::getCache(get_class($test_class)));
        $class::setCache($test_class, $instance1);
        $this->assertTrue(CacheRepository::getCache(get_class($test_class))===$instance1);
        $instance2 = $class::getCache($test_class);
        $this->assertTrue($instance1 === $instance2);
    }

    public function test_initName()
    {
        $instance = static::$fixtures['data_informant']['instance'];
        $observed = static::$fixtures['data_informant']['observed'];
        $instance->initName();
        $this->assertTrue($instance->name === $observed);
    }

    public function test_initFile()
    {
        $instance = static::$fixtures['data_informant']['instance'];
        $observed = static::$fixtures['data_informant']['observed'];
        $file_name = (new \ReflectionClass($observed))->getFileName();
        $instance->initFile();
        $this->assertTrue($instance->file === $file_name);
    }

    public function test_initInterfaces()
    {
        $instance = static::$fixtures['data_informant']['instance'];
        $this->assertTrue(count($instance->interfaces) === 0);
        $instance->initInterfaces();
        $this->assertArrayHasKey(ExampleInterface::class, $instance->interfaces);
        $this->assertTrue($instance->interfaces[ExampleInterface::class] instanceof ClassInformant);
        $methods = $instance->interfaces[ExampleInterface::class]->methods['native'];
        $this->assertTrue($methods['~ own']['owner'] === ExampleInterface::class);
        $this->assertTrue($methods['~% ownStatic']['owner'] === ExampleInterface::class);
    }

    public function test_initTraits()
    {
        $instance = static::$fixtures['data_informant']['instance'];
        $this->assertTrue(count($instance->traits) === 0);
        $instance->initTraits();
        $this->assertArrayHasKey(ExampleTrait::class, $instance->traits);
        $this->assertTrue($instance->traits[ExampleTrait::class] instanceof ClassInformant);
        $methods = $instance->traits[ExampleTrait::class]->methods['native'];
        $this->assertTrue($methods['traitMethod']['owner'] === ExampleTrait::class);
    }

    public function test_initParent()
    {
        $instance = static::$fixtures['data_informant']['instance'];
        $this->assertTrue(count($instance->parent) === 0);
        $instance->initParentClass();
        $this->assertArrayHasKey(ExampleClass::class, $instance->parent);
        $this->assertTrue($instance->parent[ExampleClass::class] instanceof ClassInformant);
    }

    public function test_initConstants()
    {
        $instance = static::$fixtures['data_informant']['instance'];
        $observed = static::$fixtures['data_informant']['observed'];
        $this->assertTrue(count($instance->constants) === 0);
        $instance->initConstants();
        $this->assertTrue(
            $instance->constants['HELLO']['value'] === 'hello' &&
            $instance->constants['HELLO']['owner'] === ExampleClass::class
        );
    }

    public function test_initMethods()
    {
        $instance = static::$fixtures['data_informant']['instance'];
        $this->assertTrue(count($instance->methods['all']) === 0);
        $this->assertTrue(count($instance->methods['native']) === 0);

        $instance->initMethods();
        $this->assertTrue(
            isset($instance->methods['all']['own']) &&
            $instance->methods['all']['own']['params'][0]['name'] === 'arg1' &&
            $instance->methods['all']['own']['params'][0]['type'] === 'string' &&
            $instance->methods['all']['own']['params'][1]['name'] === '& arg2' &&
            $instance->methods['all']['own']['params'][1]['type'] === '? int' &&
            $instance->methods['all']['own']['params'][1]['value'] === 9 &&
            $instance->methods['all']['own']['& return']['type'] === '? string' &&
            $instance->methods['all']['own']['owner'] === ExampleChildClass::class
        );

        $this->assertTrue(
            $instance->methods['all']['parent']['owner'] === ExampleClass::class
            && $instance->methods['all']['parentReplace']['owner'] === ExampleChildClass::class
            && $instance->methods['all']['traitReplace']['owner'] === ExampleChildClass::class
            // методы трейта устанавливают владельцем класс в котором определены.
            && $instance->methods['all']['traitMethod']['owner'] === ExampleChildClass::class
            && $instance->methods['all']['%* ownProtectedStatic']['owner'] === ExampleChildClass::class
            && $instance->methods['all']['%** ownPrivateStatic']['owner'] === ExampleChildClass::class
            && $instance->methods['all']['%*** ownFinalStatic']['owner'] === ExampleChildClass::class
        );
        $this->assertTrue(
            isset($instance->methods['native']['own'])
            && isset($instance->methods['native']['parentReplace'])
            && isset($instance->methods['native']['traitReplace'])
            && isset($instance->methods['native']['traitMethod'])
            && !isset($instance->methods['native']['parent'])
        );
    }

    public function test_initProperties()
    {
        $instance = static::$fixtures['data_informant']['instance'];
        $observed = static::$fixtures['data_informant']['observed'];
        $class = static::$fixtures['data_informant']['class'];
        $this->assertTrue(count($instance->properties['all']) === 0);
        $this->assertTrue(count($instance->properties['native']) === 0);
        $instance->initProperties();
        $this->assertTrue(
            $instance->properties['all']['parentProp']['owner'] === ExampleClass::class
            && $instance->properties['all']['parentProp']['value'] === 'hello'
            && $instance->properties['all']['* protectedProp']['owner'] === ExampleChildClass::class
            && $instance->properties['all']['* protectedProp']['value'] === 'hello'
            && $instance->properties['all']['** privateProp']['owner'] === ExampleChildClass::class
            && $instance->properties['all']['** privateProp']['value'] === 'hello'
            && $instance->properties['all']['%** privateStaticProp']['owner'] === ExampleChildClass::class
            && $instance->properties['all']['%** privateStaticProp']['value'] === 'hello'
            && $instance->properties['all']['%** privateStaticProp']['type'] === '? string'
            && $instance->properties['all']['%* protectedStaticProp']['value'] === 'hello'
            && $instance->properties['all']['% publicStaticProp']['value'] === 'hello'
            && $instance->properties['all']['publicObjectProp']['owner'] === ExampleChildClass::class
            && $instance->properties['all']['publicObjectProp']['type'] === ExampleClass::class
            && $instance->properties['all']['publicObjectProp']['value'] === null
            && $instance->properties['all']['% publicStaticObjectProp']['owner'] === ExampleChildClass::class
            && $instance->properties['all']['% publicStaticObjectProp']['type'] === ExampleClass::class
            && $instance->properties['all']['% publicStaticObjectProp']['value'] instanceof ObjectInformant
            && $instance->properties['all']['% publicStaticObjectProp']['value']->class->name === ExampleClass::class
            && $instance->properties['all']['% publicStaticInformantObject']['owner'] === ExampleChildClass::class
            && $instance->properties['all']['% publicStaticInformantObject']['type'] === IInformant::class
            && $instance->properties['all']['% publicStaticInformantObject']['value'] === ExampleChildClass::$publicStaticInformantObject

        // Note: with recursiveness, add a check of the object for the Reflector and Informator
        );

        $this->assertTrue(
            isset($instance->properties['native']['* protectedProp'])
            && isset($instance->properties['native']['** privateProp'])
            && isset($instance->properties['native']['%** privateStaticProp'])
            && !isset($instance->properties['native']['parentProp'])
            && isset($instance->properties['native']['publicObjectProp'])
            && isset($instance->properties['native']['% publicStaticObjectProp'])
        );
    }
}