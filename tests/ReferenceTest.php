<?php

declare(strict_types=1);

namespace Pharaonic\DotArray\Test;

use PHPUnit\Framework\TestCase;
use Pharaonic\DotArray\DotArray;

class ReferenceTest extends TestCase
{
    public function testSetReferenceIsFluent(): void
    {
        $array = [];
        $dot = new DotArray();

        $this->assertSame($dot, $dot->setReference($array));
    }

    public function testSetWritesToTheReferencedArray(): void
    {
        $array = ['user' => ['name' => 'Old']];
        (new DotArray())->setReference($array)->set('user.name', 'New')->set('user.role', 'admin');

        $this->assertSame(['user' => ['name' => 'New', 'role' => 'admin']], $array);
    }

    public function testExternalChangesAreVisible(): void
    {
        $array = ['a' => 1];
        $dot = (new DotArray())->setReference($array);
        $array['b'] = 2;

        $this->assertSame(2, $dot->get('b'));
    }

    public function testDeleteRemovesFromTheReferencedArray(): void
    {
        $array = ['user' => ['name' => 'Raggi', 'password' => 'secret']];
        (new DotArray())->setReference($array)->delete('user.password');

        $this->assertSame(['user' => ['name' => 'Raggi']], $array);
    }

    public function testPullRemovesFromTheReferencedArray(): void
    {
        $array = ['token' => 'abc', 'keep' => 1];
        $dot = (new DotArray())->setReference($array);

        $this->assertSame('abc', $dot->pull('token'));
        $this->assertSame(['keep' => 1], $array);
    }

    public function testClearEmptiesTheReferencedArray(): void
    {
        $array = ['a' => 1];
        (new DotArray())->setReference($array)->clear();

        $this->assertSame([], $array);
    }

    public function testSetArrayReplacesTheReferencedArray(): void
    {
        $array = ['a' => 1];
        (new DotArray())->setReference($array)->setArray(['b' => 2]);

        $this->assertSame(['b' => 2], $array);
    }

    public function testWildcardSetAndDeleteWriteToTheReferencedArray(): void
    {
        $array = ['users' => [['name' => 'A', 'password' => 'x'], ['name' => 'B', 'password' => 'y']]];

        (new DotArray())->setReference($array)
            ->set('users.*.active', true)
            ->delete('users.*.password');

        $this->assertSame([
            'users' => [['name' => 'A', 'active' => true], ['name' => 'B', 'active' => true]],
        ], $array);
    }

    public function testArrayAccessWritesToTheReferencedArray(): void
    {
        $array = ['a' => ['b' => 1]];
        $dot = (new DotArray())->setReference($array);

        $dot['a.c'] = 2;
        $dot[] = 'appended';
        unset($dot['a.b']);

        $this->assertSame(['a' => ['c' => 2], 0 => 'appended'], $array);
    }

    public function testReadsDoNotModifyTheReferencedArray(): void
    {
        $array = ['users' => [['name' => 'A']]];
        $dot = (new DotArray())->setReference($array);

        $dot->get('users.*.missing.deep');
        $dot->has('users.*.missing');
        $dot->get('missing.path');

        $this->assertSame(['users' => [['name' => 'A']]], $array);
    }

    public function testConstructorCopiesTheArray(): void
    {
        $array = ['a' => 1];
        $dot = new DotArray($array);
        $dot->set('a', 2);

        $this->assertSame(['a' => 1], $array);
    }
}
