<?php
namespace  Alpa\EntityDetails\Tests;

use Alpa\EntityDetails\CacheRepository;
use Alpa\EntityDetails\IInformant;
use Alpa\EntityDetails\ReflectionClass;
use Alpa\EntityDetails\ReflectionObject;
use Alpa\EntityDetails\Reflector;
use Alpa\EntityDetails\TReflectorInformant;
use Alpa\EntityDetails\Tests\Constraints\Asserts;
use Alpa\EntityDetails\Tests\Fixtures\ExampleClass;
use PHPUnit\Framework\TestCase;


class InformantTest  extends TestCase
{
    use Asserts;
    public static function setUpBeforeClass():void
    {
        parent::setUpBeforeClass();
    }
    public static function tearDownAfterClass():void
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
    
    public function test_getReflectionClass()
    {
        $class=ExampleClass::class;
        /*$reflectIn=new ReflectionClass($class);
        $reflectOut=Info::getReflectionClass($reflectIn);
        $this->assertTrue($reflectOut===$reflectIn,
        'InfoType::getReflectionClass => argument[1] instanceof '.ReflectionClass::class);
        */
        $reflectOut=TReflectorInformant::getReflectionClass($class);
        $this->assertTrue($reflectOut instanceof ReflectionClass && $class===$reflectOut->name,
            'InfoType::getReflectionClass => argument[0] is a class name => return instanceof '.ReflectionClass::class);
        $reflectOut=TReflectorInformant::getReflectionClass(new $class);
        $this->assertTrue($reflectOut instanceof ReflectionClass && $class===$reflectOut->name,
            'InfoType::getReflectionClass => argument[0] is a class name => return instanceof '.ReflectionClass::class);
    }
    public function test_getReflectionObject()
    {
        $class=ExampleClass::class;
       /* $reflectIn=new ReflectionObject(new $class);
        $reflectOut=Info::getReflectionObject($reflectIn);
        $this->assertTrue($reflectOut===$reflectIn,
       'InfoType::getReflectionObject => argument[1] instanceof '.ReflectionObject::class);*/
        $reflectOut=TReflectorInformant::getReflectionObject(new $class);
        $this->assertTrue($reflectOut instanceof ReflectionObject && $class===$reflectOut->name,
            'InfoType::getReflectionClass => argument[0] is a class name => return instanceof '.ReflectionObject::class);
    }
    public function test_getReflector()
    {
        $class=ExampleClass::class;
        $reflectIn=new ReflectionObject(new $class);
        $reflectOut=TReflectorInformant::getReflector($reflectIn);
        $this->assertTrue($reflectOut===$reflectIn,
            'InfoType::getReflector => argument[1] instanceof '.ReflectionObject::class);
        $reflectIn=new ReflectionClass($class);
        $reflectOut=TReflectorInformant::getReflector($reflectIn); 
        $this->assertTrue($reflectOut===$reflectIn,
            'InfoType::getReflector => argument[1] instanceof '.ReflectionClass::class);
        $reflectOut=TReflectorInformant::getReflector($class);
        $this->assertTrue($reflectOut instanceof ReflectionClass,
            'InfoType::getReflector => argument[1] instanceof '.ReflectionClass::class);
        $reflectOut=TReflectorInformant::getReflector(new $class,true);
        $this->assertTrue($reflectOut instanceof ReflectionClass,
            'InfoType::getReflector => argument[1] instanceof '.ReflectionClass::class);
        $reflectOut=TReflectorInformant::getReflector(new $class);
        $this->assertTrue($reflectOut instanceof ReflectionObject,
            'InfoType::getReflector => argument[1] instanceof '.ReflectionClass::class);
    }
    
    public function test_new()
    {
        $test_class =get_class(new class { public $hello=1;});
        $test_class2 =get_class(new class { public $hello=2;});
        $info1= new class($test_class)  implements IInformant{
            use TReflectorInformant;
            protected static $cache=[];
            protected function init()
            {}
            protected static function getCache($str):?IInformant
            {
                return CacheRepository::getCache($str);
            }
            protected static function setCache($str,$object)
            {
                CacheRepository::setCache($str,$object);
            }
        };
        $info2=$info1::newObject($test_class);
        $this->assertTrue($info1===$info2,'Info::newObject => return cached object');
        $info3=$info1::newObject($test_class2);
        $this->assertTrue($info3!==$info1,'Info::newObject => return new object');
        $info4=$info1::newObject($test_class2);
        $this->assertTrue($info3===$info4,'Info::newObject => return cached object 2');
    }
}