<?php


namespace Alpa\EntityDetails\Tests\Fixtures;


interface ExampleInterface
{
    public function  & own(string $arg1,?int & $arg2=9):?string;
    public static function ownStatic();
}