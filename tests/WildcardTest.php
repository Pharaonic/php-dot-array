<?php

declare(strict_types=1);

namespace Pharaonic\DotArray\Test;

use PHPUnit\Framework\TestCase;
use Pharaonic\DotArray\DotArray;

class WildcardTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function users(): array
    {
        return [
            'users' => [
                ['name' => 'Ahmed', 'email' => 'ahmed@example.com', 'profile' => ['age' => 30]],
                ['name' => 'Sara', 'profile' => ['age' => 25]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function groups(): array
    {
        return [
            'groups' => [
                ['users' => [['name' => 'A'], ['name' => 'B']]],
                ['users' => [['name' => 'C']]],
            ],
        ];
    }

    // get()

    public function testGetReturnsOneEntryPerElement(): void
    {
        $dot = new DotArray($this->users());

        $this->assertSame(['Ahmed', 'Sara'], $dot->get('users.*.name'));
        $this->assertSame([30, 25], $dot->get('users.*.profile.age'));
        $this->assertSame([['age' => 30], ['age' => 25]], $dot->get('users.*.profile'));
    }

    public function testGetFillsMissingValuesWithDefault(): void
    {
        $dot = new DotArray($this->users());

        $this->assertSame(['ahmed@example.com', null], $dot->get('users.*.email'));
        $this->assertSame(['ahmed@example.com', 'x'], $dot->get('users.*.email', 'x'));
        $this->assertSame([null, null], $dot->get('users.*.phone'));
    }

    public function testGetUsesTheDefaultForNullValues(): void
    {
        $dot = new DotArray(['users' => [['email' => null], ['email' => 'b@example.com'], []]]);

        $this->assertSame(['-', 'b@example.com', '-'], $dot->get('users.*.email', '-'));
        $this->assertSame([null, 'b@example.com', null], $dot->get('users.*.email'));
        $this->assertTrue($dot->has('users.0.email'));
    }

    public function testGetNestedWildcardsFlattenIntoOneList(): void
    {
        $dot = new DotArray($this->groups());

        $this->assertSame(['A', 'B', 'C'], $dot->get('groups.*.users.*.name'));
        $this->assertSame(
            [[['name' => 'A'], ['name' => 'B']], [['name' => 'C']]],
            $dot->get('groups.*.users')
        );
    }

    public function testGetSkipsBranchesThatDoNotReachTheLastWildcard(): void
    {
        $dot = new DotArray([
            'groups' => [
                ['users' => [['name' => 'A']]],
                ['title' => 'no users'],
                ['users' => 'not an array'],
                ['users' => []],
                ['users' => [['name' => 'B']]],
            ],
        ]);

        $this->assertSame(['A', 'B'], $dot->get('groups.*.users.*.name'));
    }

    public function testGetSkipsNonArrayElementsWhenKeysFollowTheWildcard(): void
    {
        $dot = new DotArray(['items' => [1, ['id' => 2], 'x', ['id' => 3]]]);

        $this->assertSame([2, 3], $dot->get('items.*.id'));
        $this->assertSame([1, ['id' => 2], 'x', ['id' => 3]], $dot->get('items.*'));
    }

    public function testGetWithoutMatchesReturnsDefault(): void
    {
        $dot = new DotArray(['empty' => [], 'scalar' => 'x', 'scalars' => [1, 2]]);

        $this->assertNull($dot->get('empty.*.name'));
        $this->assertSame('d', $dot->get('empty.*.name', 'd'));
        $this->assertSame('d', $dot->get('scalar.*', 'd'));
        $this->assertSame('d', $dot->get('missing.*.name', 'd'));
        $this->assertSame('d', $dot->get('scalars.*.name', 'd'));
    }

    public function testGetOnAssociativeArraysReturnsAList(): void
    {
        $dot = new DotArray(['roles' => ['admin' => ['level' => 1], 'editor' => ['level' => 2]]]);

        $this->assertSame([1, 2], $dot->get('roles.*.level'));
        $this->assertSame([['level' => 1], ['level' => 2]], $dot->get('roles.*'));
    }

    public function testGetWithLeadingWildcard(): void
    {
        $dot = new DotArray([['id' => 1], ['id' => 2]]);

        $this->assertSame([1, 2], $dot->get('*.id'));
        $this->assertSame([['id' => 1], ['id' => 2]], $dot->get('*'));
    }

    public function testGetKeepsArrayValuesIntact(): void
    {
        $dot = new DotArray(['a' => [['tags' => [1, 2]], ['tags' => [3]]]]);

        $this->assertSame([[1, 2], [3]], $dot->get('a.*.tags'));
    }

    public function testGetWithMixedAssociativeAndIndexedArrays(): void
    {
        $dot = new DotArray([
            'products' => [
                'p1' => ['variants' => [['price' => 10], ['price' => 12]]],
                'p2' => ['variants' => [['price' => 20]]],
            ],
        ]);

        $this->assertSame([10, 12, 20], $dot->get('products.*.variants.*.price'));
    }

    public function testWildcardMustBeAWholeSegment(): void
    {
        $dot = new DotArray(['a' => ['b*' => 1, 'b' => 2]]);

        $this->assertSame(1, $dot->get('a.b*'));
    }

    // has()

    public function testHasRequiresEveryMatchedElementToHaveThePath(): void
    {
        $dot = new DotArray($this->users());

        $this->assertTrue($dot->has('users.*.name'));
        $this->assertTrue($dot->has('users.*.profile.age'));
        $this->assertFalse($dot->has('users.*.email'));
        $this->assertFalse($dot->has('users.*.phone'));
    }

    public function testHasCountsNullValues(): void
    {
        $dot = new DotArray(['users' => [['email' => null], ['email' => 'x']]]);

        $this->assertTrue($dot->has('users.*.email'));
    }

    public function testHasWithNestedWildcards(): void
    {
        $dot = new DotArray($this->groups());

        $this->assertTrue($dot->has('groups.*.users.*.name'));
        $this->assertFalse($dot->has('groups.*.users.*.email'));
    }

    public function testHasIsFalseWithoutMatches(): void
    {
        $dot = new DotArray(['empty' => [], 'scalar' => 'x']);

        $this->assertFalse($dot->has('empty.*.name'));
        $this->assertFalse($dot->has('empty.*'));
        $this->assertFalse($dot->has('scalar.*'));
        $this->assertFalse($dot->has('missing.*'));
    }

    public function testHasWithTrailingWildcard(): void
    {
        $dot = new DotArray(['list' => [null, 0]]);

        $this->assertTrue($dot->has('list.*'));
    }

    // set()

    public function testSetUpdatesEveryElement(): void
    {
        $dot = new DotArray($this->users());
        $dot->set('users.*.active', true);

        $this->assertSame([true, true], $dot->get('users.*.active'));
        $this->assertSame('Ahmed', $dot->get('users.0.name'));
    }

    public function testSetCreatesNestedKeysInsideEachElement(): void
    {
        $dot = new DotArray($this->users());
        $dot->set('users.*.settings.theme', 'dark');

        $this->assertSame(['dark', 'dark'], $dot->get('users.*.settings.theme'));
    }

    public function testSetWithNestedWildcards(): void
    {
        $dot = new DotArray($this->groups());
        $dot->set('groups.*.users.*.active', true);

        $this->assertSame([
            'groups' => [
                ['users' => [['name' => 'A', 'active' => true], ['name' => 'B', 'active' => true]]],
                ['users' => [['name' => 'C', 'active' => true]]],
            ],
        ], $dot->all());
    }

    public function testSetNeverCreatesWildcardBranches(): void
    {
        $dot = new DotArray(['empty' => [], 'groups' => [['title' => 'no users']]]);
        $dot->set('missing.*.active', true);
        $dot->set('empty.*.active', true);
        $dot->set('groups.*.users.*.active', true);

        $this->assertSame(['empty' => [], 'groups' => [['title' => 'no users']]], $dot->all());
    }

    public function testSetLeavesNonArrayElementsAloneWhenKeysFollowTheWildcard(): void
    {
        $dot = new DotArray(['items' => [1, ['id' => 2]]]);
        $dot->set('items.*.id', 9);

        $this->assertSame(['items' => [1, ['id' => 9]]], $dot->all());
    }

    public function testSetWithTrailingWildcardReplacesEveryElement(): void
    {
        $dot = new DotArray(['flags' => ['a' => false, 'b' => true]]);
        $dot->set('flags.*', null);

        $this->assertSame(['flags' => ['a' => null, 'b' => null]], $dot->all());
    }

    // delete()

    public function testDeleteRemovesTheKeyFromEveryElement(): void
    {
        $dot = new DotArray($this->users());

        $this->assertTrue($dot->delete('users.*.profile'));
        $this->assertSame([
            'users' => [
                ['name' => 'Ahmed', 'email' => 'ahmed@example.com'],
                ['name' => 'Sara'],
            ],
        ], $dot->all());
    }

    public function testDeleteReturnsTrueWhenAnyValueWasDeleted(): void
    {
        $dot = new DotArray($this->users());

        $this->assertTrue($dot->delete('users.*.email'));
        $this->assertFalse($dot->has('users.0.email'));
        $this->assertFalse($dot->delete('users.*.email'));
        $this->assertFalse($dot->delete('missing.*.email'));
    }

    public function testDeleteWithNestedWildcards(): void
    {
        $dot = new DotArray($this->groups());

        $this->assertTrue($dot->delete('groups.*.users.*.name'));
        $this->assertSame(['groups' => [['users' => [[], []]], ['users' => [[]]]]], $dot->all());
    }

    public function testDeleteWithTrailingWildcardEmptiesTheArray(): void
    {
        $dot = new DotArray(['users' => [1, 2], 'other' => 1]);

        $this->assertTrue($dot->delete('users.*'));
        $this->assertSame(['users' => [], 'other' => 1], $dot->all());
    }

    // pull() / count()

    public function testPullWithWildcard(): void
    {
        $dot = new DotArray($this->users());

        $this->assertSame(['ahmed@example.com', null], $dot->pull('users.*.email'));
        $this->assertFalse($dot->has('users.0.email'));
    }

    public function testCountWithWildcardCountsMatchedElements(): void
    {
        $dot = new DotArray($this->groups());

        $this->assertSame(3, $dot->count('groups.*.users.*.name'));
        $this->assertSame(0, $dot->count('groups.*.missing.*'));
    }

    // Consistency between operations

    public function testOperationsAgreeOnMatchedElements(): void
    {
        $data = [
            'rows' => [
                ['cells' => [['v' => 1], ['v' => 2]]],
                ['cells' => 'skip'],
                ['nothing' => true],
                ['cells' => [['v' => 3], 'skip']],
            ],
        ];
        $dot = new DotArray($data);

        $this->assertSame([1, 2, 3], $dot->get('rows.*.cells.*.v'));
        $this->assertTrue($dot->has('rows.*.cells.*.v'));

        $dot->set('rows.*.cells.*.v', 0);
        $this->assertSame([0, 0, 0], $dot->get('rows.*.cells.*.v'));

        $dot->delete('rows.*.cells.*.v');
        // Nothing follows the trailing wildcard, so the scalar element is matched as well.
        $this->assertSame([[], [], [], 'skip'], $dot->get('rows.*.cells.*'));
        $this->assertSame('skip', $dot->get('rows.1.cells'));
        $this->assertSame('skip', $dot->get('rows.3.cells.1'));
    }
}
