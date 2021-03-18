<?php

namespace Alpa\EntityDetails\Tests\Constraints;

use PHPUnit\Framework\Constraint;

class IsThrow extends Constraint\Constraint
{
    public function matches($other): bool
    {
        $call =& $other['call'];
        $throw =& $other['throw'] ?? \Throwable::class;
        $check = false;
        try {
            $call();
        } catch (\Throwable $e) {
            if (is_string($throw)) {
                if (is_string($throw) && is_a($e, $throw)) {
                    $check = true;
                }
            } else if (is_callable($throw)) {
                $check = $throw($e);
            }
        };
        unset($call, $throw);
        return $check;
    }

    public function toString(): string
    {
        return 'Check for an exception';
    }
}