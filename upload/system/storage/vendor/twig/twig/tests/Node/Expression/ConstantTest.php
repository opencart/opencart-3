<?php

namespace Twig\Tests\Node\Expression;

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Twig\Node\Expression\ConstantExpression;
use Twig\Test\NodeTestCase;

class ConstantTest extends NodeTestCase
{
    public function testConstructor()
    {
        $node = new ConstantExpression('foo', 1);

        $this->assertEquals('foo', $node->getAttribute('value'));
    }

    public static function provideTests(): iterable
    {
        $tests = [];

        $node = new ConstantExpression('foo', 1);
        $tests[] = [$node, '"foo"'];

        return $tests;
    }
}
