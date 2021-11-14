<?php
namespace  Alpa\EntityDetails\Tests;

use Alpa\EntityDetails\ArrayInformant;
use Alpa\EntityDetails\Info;
use Alpa\EntityDetails\ReflectionClass;
use Alpa\EntityDetails\ReflectionObject;
use Alpa\EntityDetails\Tests\Constraints\Asserts;
use Alpa\EntityDetails\Tests\Fixtures\ExampleClass;
use PHPUnit\Framework\TestCase;
class Test0{}
class Test01 extends Test0{}
interface ITest2{
    public function test ():Test0;
}

class Test2 implements ITest2 {
    public function test ():Test01
    {
        return new Test01();        
    }
}
class TestTest  extends TestCase
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
        parent::tearDown();
    }
    public function test_test()
    {
        $obj=new Test2();
        $obj2=$obj->test();
    }
}