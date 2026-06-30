<?php

use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testSetAndGet(): void
    {
        $c = new Container();
        $c->set('foo', 'bar');
        $this->assertEquals('bar', $c->get('foo'));
    }

    public function testSingleton(): void
    {
        $c = new Container();
        $c->singleton('counter', function () {
            static $i = 0;
            return ++$i;
        });

        $this->assertEquals(1, $c->get('counter'));
        $this->assertEquals(1, $c->get('counter'));
    }

    public function testAlias(): void
    {
        $c = new Container();
        $c->set('real', 'value');
        $c->alias('virtual', 'real');
        $this->assertEquals('value', $c->get('virtual'));
    }

    public function testHas(): void
    {
        $c = new Container();
        $this->assertFalse($c->has('missing'));
        $c->set('present', 'yes');
        $this->assertTrue($c->has('present'));
    }
}
