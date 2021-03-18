<?php

namespace Alpa\EntityDetails\Tests;

use Alpa\EntityDetails\Informant;
use Alpa\EntityDetails\ClassInformant;
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
        $info = new class(ExampleChildClass::class) extends ClassInformant {
            public static function setCache($data, Informant $object)
            {
                parent::setCache($data, $object);
            }

            public static function getCache($data): ?Informant
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

        $cache = $reflect->getProperty('cache');
        $cache->setAccessible(true);
        $instance1 = $reflect->newInstanceWithoutConstructor();
        $this->assertTrue(!isset($cache->getValue()[get_class($test_class)]));
        $class::setCache($test_class, $instance1);
        $this->assertTrue(isset($cache->getValue()[get_class($test_class)]));
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
        $methods = $instance->interfaces[ExampleInterface::class]->native_methods;
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
        $methods = $instance->traits[ExampleTrait::class]->native_methods;
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
        $this->assertTrue(count($instance->methods) === 0);
        $this->assertTrue(count($instance->native_methods) === 0);
        $instance->initMethods();

        $this->assertTrue(
            isset($instance->methods['own']) &&
            $instance->methods['own']['params'][0]['name'] === 'arg1' &&
            $instance->methods['own']['params'][0]['type'] === 'string' &&
            $instance->methods['own']['params'][1]['name'] === '& arg2' &&
            $instance->methods['own']['params'][1]['type'] === '? int' &&
            $instance->methods['own']['params'][1]['value'] === 9 &&
            $instance->methods['own']['& return']['type'] === '? string' &&
            $instance->methods['own']['owner'] === ExampleChildClass::class
        );
        
        $this->assertTrue(
            $instance->methods['parent']['owner'] === ExampleClass::class
            && $instance->methods['parentReplace']['owner'] === ExampleChildClass::class
            && $instance->methods['traitReplace']['owner'] === ExampleChildClass::class
            // методы трейта устанавливают владельцем класс в котором определены.
            && $instance->methods['traitMethod']['owner'] === ExampleChildClass::class
            && $instance->methods['%* ownProtectedStatic']['owner'] === ExampleChildClass::class
            && $instance->methods['%** ownPrivateStatic']['owner'] === ExampleChildClass::class
            && $instance->methods['%*** ownFinalStatic']['owner'] === ExampleChildClass::class
        );
        $this->assertTrue(
            isset($instance->native_methods['own'])
            && isset($instance->native_methods['parentReplace'])
            && isset($instance->native_methods['traitReplace'])
            && isset($instance->native_methods['traitMethod'])
            && !isset($instance->native_methods['parent'])
        );
    }

    public function test_initProperties()
    {
        $instance = static::$fixtures['data_informant']['instance'];
        $observed = static::$fixtures['data_informant']['observed'];
        $class = static::$fixtures['data_informant']['class'];
        $this->assertTrue(count($instance->properties) === 0);
        $this->assertTrue(count($instance->native_properties) === 0);
        $instance->initProperties();
        $this->assertTrue(
            $instance->properties['parentProp']['owner'] === ExampleClass::class
            && $instance->properties['parentProp']['value']==='hello'
            && $instance->properties['* protectedProp']['owner'] === ExampleChildClass::class
            && $instance->properties['* protectedProp']['value']==='hello'
            && $instance->properties['** privateProp']['owner'] === ExampleChildClass::class
            && $instance->properties['** privateProp']['value']==='hello'
            && $instance->properties['%** privateStaticProp']['owner'] === ExampleChildClass::class
            && $instance->properties['%** privateStaticProp']['value'] === 'hello'
            && $instance->properties['%** privateStaticProp']['type'] === '? string'
            && $instance->properties['%* protectedStaticProp']['value'] === 'hello'
            && $instance->properties['% publicStaticProp']['value'] === 'hello'
        );
        
        $this->assertTrue(
            isset($instance->native_properties['* protectedProp'])
            && isset($instance->native_properties['** privateProp'])
            && isset($instance->native_properties['%** privateStaticProp'])
            && !isset($instance->native_properties['parentProp'])
        );
        //  проерка на рекурсивный анализ массивов и обьектов
    }

    public function _test_()
    {

    }
}