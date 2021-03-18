<?php
namespace  Alpa\EntityDetails\Tests;

use Alpa\EntityDetails\Info;
use Alpa\EntityDetails\ReflectionClass;
use Alpa\EntityDetails\ReflectionObject;
use Alpa\EntityDetails\Tests\Constraints\Asserts;
use Alpa\EntityDetails\Tests\Fixtures\ExampleClass;
use PHPUnit\Framework\TestCase;


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
/*        $map=new \Ds\Map();
        $a=['hello'=>'qwer'];
        $b=['hello'=>'qwer'];
        $map->put($a,'zzz');       
        $r1=$map->get($a);
        $r2=$map->get($b);
        self::assertTrue($r1===$r2);
        $keys=$map->keys();
        $a['hello']='qwer2';
        self::assertTrue($a===$keys->get(0));*/
    }
  
}