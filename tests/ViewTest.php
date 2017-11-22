<?php

namespace Isholao\Template\Tests;

use PHPUnit\Framework\TestCase;

/**
 * @author Ishola O <ishola.tolu@outlook.com>
 */
class ViewTest extends TestCase
{

    function testDataSetterAndGetter()
    {
        $v = new \Isholao\Template\View();
        $v->setData('name', 'me');

        $this->assertSame('me', $v->name);
    }

}
