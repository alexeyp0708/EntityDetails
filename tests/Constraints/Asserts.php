<?php

namespace Alpa\EntityDetails\Tests\Constraints;


use function PHPUnit\Framework\assertTrue;

trait Asserts
{
    protected function assertThrow(callable $call, $throw = \Throwable::class, $message = ''): void
    {
        $other = [
            'call' => &$call,
            'throw' => &$throw
        ];
        static::assertThat($other, new IsThrow, $message);
        unset($other['call']);
        unset($other['throw']);
        unset($other);
    }
    protected function assertRunCallback(callable $call,$message=''):void
    {
        $result=(bool)$call();
        $this->assertTrue($result,$message);
    }
}