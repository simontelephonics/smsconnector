<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\Collection
 */
final class CollectionTest extends \Telnyx\TestCase
{
    /**
     * @before
     */
    public function setUpFixture()
    {
        $this->fixture = Collection::constructFrom([
            'url' => '/things',
            'data' => [
                ['id' => 1],
                ['id' => 2],
                ['id' => 3]
            ],
            'meta' => [
                'page_size' => 3,
                'page_number' => 2,
                'total_results' => 6,
                'total_pages' => 4
            ]
        ]);
    }

    public function testCanList()
    {
        $this->stubRequest(
            'GET',
            '/things',
            [],
            null,
            false,
            [
                'data' => [
                    ['id' => 1],
                    ['id' => 2],
                    ['id' => 3]
                ],
                'meta' => [
                    'page_size' => 3,
                    'page_number' => 2,
                    'total_results' => 6,
                    'total_pages' => 4
                ]
            ]
        );

        $resources = $this->fixture->all();
        $this->assertTrue(is_array($resources['data']));
    }

    public function testCanRetrieve()
    {
        $this->stubRequest(
            'GET',
            '/things/1',
            [],
            null,
            false,
            [
                'id' => 1,
            ]
        );

        $this->fixture->retrieve(1);
    }

    public function testCanCreate()
    {
        $this->stubRequest(
            'POST',
            '/things',
            [
                'foo' => 'bar',
            ],
            null,
            false,
            [
                'id' => 2,
            ]
        );

        $this->fixture->create([
            'foo' => 'bar',
        ]);
    }

    public function testCanIterate()
    {
        $seen = [];
        foreach ($this->fixture['data'] as $item) {
            array_push($seen, $item['id']);
        }

        $this->assertSame([1, 2, 3], $seen);
    }

    public function testSupportsIteratorToArray()
    {
        $seen = [];
        foreach (iterator_to_array($this->fixture) as $item) {
            array_push($seen, $item['id']);
        }

        $this->assertSame([1, 2, 3], $seen);
    }

    public function testHeaders()
    {
        $this->stubRequest(
            'POST',
            '/things',
            [
                'foo' => 'bar',
            ],
            [
                'Telnyx-Account: acct_foo',
                'Idempotency-Key: qwertyuiop',
            ],
            false,
            [
                'id' => 2,
            ]
        );

        $this->fixture->create([
            'foo' => 'bar',
        ], [
            'telnyx_account' => 'acct_foo',
            'idempotency_key' => 'qwertyuiop',
        ]);
    }

    public function testEmptyCollection()
    {
        $emptyCollection = Collection::emptyCollection();
        $this->assertEquals([], $emptyCollection->data);
    }

    public function testIsEmpty()
    {
        $empty = Collection::constructFrom(['data' => []]);
        $this->assertTrue($empty->isEmpty());

        $notEmpty = Collection::constructFrom(['data' => [['id' => 1]]]);
        $this->assertFalse($notEmpty->isEmpty());
    }

    public function testNextPage()
    {
        $this->stubRequest(
            'GET',
            '/things',
            [
                'page[number]' => 3
            ],
            null,
            false,
            [
                'data' => [
                    ['id' => 1],
                    ['id' => 2],
                    ['id' => 3]
                ],
                'meta' => [
                    'page_size' => 3,
                    'page_number' => 2,
                    'total_results' => 6,
                    'total_pages' => 4
                ]
            ]
        );

        $nextPage = $this->fixture->nextPage();
        $ids = [];
        foreach ($nextPage->data as $element) {
            array_push($ids, $element['id']);
        }
        $this->assertEquals([1, 2, 3], $ids);
    }

    public function testPreviousPage()
    {
        $this->stubRequest(
            'GET',
            '/things',
            [
                'page[number]' => 1
            ],
            null,
            false,
            [
                'data' => [
                    ['id' => 1],
                    ['id' => 2],
                    ['id' => 3]
                ],
                'meta' => [
                    'page_size' => 3,
                    'page_number' => 2,
                    'total_results' => 6,
                    'total_pages' => 4
                ]
            ]
        );

        $previousPage = $this->fixture->previousPage();
        $ids = [];
        foreach ($previousPage->data as $element) {
            array_push($ids, $element['id']);
        }
        $this->assertEquals([1, 2, 3], $ids);
    }
}
