<?php

declare(strict_types=1);

namespace Pharaonic\DotArray\Test;

use PHPUnit\Framework\TestCase;
use Pharaonic\DotArray\DotArray;

class EscapingTest extends TestCase
{
    public function testEscapedDotReadsALiteralDot(): void
    {
        $dot = new DotArray([
            'config' => [
                'app.name' => 'Pharaonic',
                'app' => ['name' => 'Nested'],
            ],
        ]);

        $this->assertSame('Pharaonic', $dot->get('config.app\.name'));
        $this->assertSame('Nested', $dot->get('config.app.name'));
        $this->assertTrue($dot->has('config.app\.name'));
    }

    public function testEscapedDotWritesAndDeletesALiteralKey(): void
    {
        $dot = new DotArray();
        $dot->set('hosts.example\.com.port', 443);

        $this->assertSame(['hosts' => ['example.com' => ['port' => 443]]], $dot->all());

        $this->assertTrue($dot->delete('hosts.example\.com'));
        $this->assertSame(['hosts' => []], $dot->all());
    }

    public function testEscapedTrailingDotIsKept(): void
    {
        $dot = new DotArray(['a.' => 1, 'a' => 2]);

        $this->assertSame(1, $dot->get('a\.'));
        $this->assertSame(2, $dot->get('a.'));
    }

    public function testEscapedStarReadsALiteralStarKey(): void
    {
        $dot = new DotArray([
            'items' => [
                '*' => ['name' => 'Literal Star'],
                'other' => ['name' => 'Other'],
            ],
        ]);

        $this->assertSame('Literal Star', $dot->get('items.\*.name'));
        $this->assertSame(['Literal Star', 'Other'], $dot->get('items.*.name'));
    }

    public function testEscapedStarWritesAndDeletesALiteralStarKey(): void
    {
        $dot = new DotArray(['items' => ['a' => ['v' => 1]]]);
        $dot->set('items.\*.v', 2);

        $this->assertSame(['items' => ['a' => ['v' => 1], '*' => ['v' => 2]]], $dot->all());

        $this->assertTrue($dot->delete('items.\*'));
        $this->assertSame(['items' => ['a' => ['v' => 1]]], $dot->all());
    }

    public function testEscapedBackslash(): void
    {
        $dot = new DotArray(['a\\' => ['b' => 1], 'c\\.d' => 2]);

        // `\\` is one literal backslash, so the dot after it still separates keys.
        $this->assertSame(1, $dot->get('a\\\\.b'));
        $this->assertSame(2, $dot->get('c\\\\\\.d'));
    }

    public function testOtherBackslashesAreLiteral(): void
    {
        $dot = new DotArray(['App\\Models\\User' => ['table' => 'users'], 'end\\' => 1]);

        $this->assertSame('users', $dot->get('App\\Models\\User.table'));
        $this->assertSame(1, $dot->get('end\\'));
    }

    public function testEscapingInsideWildcardPaths(): void
    {
        $dot = new DotArray([
            'sites' => [
                ['meta' => ['og.title' => 'One']],
                ['meta' => ['og.title' => 'Two']],
            ],
        ]);

        $this->assertSame(['One', 'Two'], $dot->get('sites.*.meta.og\.title'));

        $dot->set('sites.*.meta.og\.title', 'Same');
        $this->assertSame(['Same', 'Same'], $dot->get('sites.*.meta.og\.title'));
    }

    // Malformed paths

    public function testLeadingAndTrailingDotsAndSpacesAreIgnored(): void
    {
        $dot = new DotArray(['user' => ['name' => 'Raggi']]);

        $this->assertSame('Raggi', $dot->get('.user.name'));
        $this->assertSame('Raggi', $dot->get('user.name.'));
        $this->assertSame('Raggi', $dot->get(' user.name '));
        $this->assertSame(['name' => 'Raggi'], $dot->get('user.'));
    }

    public function testConsecutiveDotsMeanAnEmptyKey(): void
    {
        $dot = new DotArray(['user' => ['' => ['name' => 'Empty key']]]);

        $this->assertSame('Empty key', $dot->get('user..name'));
        $this->assertFalse((new DotArray(['user' => ['name' => 1]]))->has('user..name'));
    }
}
