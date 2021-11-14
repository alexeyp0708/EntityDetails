<?php


namespace Alpa\EntityDetails\Tests\Proxy;


use Alpa\EntityDetails\ArrayInformant;
use Alpa\EntityDetails\IInformant;
use Alpa\EntityDetails\Proxy\ClassInformant;
use Alpa\EntityDetails\Proxy\ObjectInformant;
use Alpa\EntityDetails\Tests\Fixtures\ExampleChildClass;
use PHPUnit\Framework\TestCase;

class ClassInformantTest extends TestCase
{
    protected static $fixtures=[];
    
    public static function setUpBeforeClass(): void
    {
        $observed = ExampleChildClass::class;
        self::$fixtures['informant']=new class ($observed) extends ClassInformant {
            public static function getClasses():array
            {
                return parent::getClasses();
            }
        }; 
    }
    public static function test_getGlasses()
    {
        $informant=self::$fixtures['informant'];
        $classes=$informant->getClasses();
        static::assertTrue(
            count(array_diff_assoc($classes,[
                        'Informant'=>IInformant::class,
                        'ClassInformant'=>ClassInformant::class,
                        'ObjectInformant'=>ObjectInformant::class,
                        'ArrayInformant'=>ArrayInformant::class
                    ]
                )
            )===0
        );
    }
}