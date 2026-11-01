<?php

namespace Backpack\CRUD\Tests\Feature;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Http\Controllers\ListSearchCrudController;
use Backpack\CRUD\Tests\config\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @covers Backpack\CRUD\app\Http\Controllers\Operations\ListOperation
 */
class ListOperationSearchTest extends BaseDBCrudPanel
{
    protected string $searchUrl;

    protected function defineRoutes($router)
    {
        $router->crud(config('backpack.base.route_prefix').'/list-search', ListSearchCrudController::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->searchUrl = config('backpack.base.route_prefix').'/list-search/search';
        $this->actingAs(User::find(1));

        // the seeded users have faker names (no digits), so only these entries contain numbers
        foreach (['Number 0 user', 'Number 1 user', 'Number 123 user', 'Number 10 user'] as $index => $name) {
            User::create([
                'name' => $name,
                'email' => "number{$index}@example.com",
                'password' => bcrypt('secret'),
            ]);
        }
    }

    public static function searchTermProvider(): array
    {
        return [
            'zero' => ['0', ['Number 0 user', 'Number 10 user']],
            'one' => ['1', ['Number 1 user', 'Number 123 user', 'Number 10 user']],
            'multi-digit number' => ['123', ['Number 123 user']],
            'double zero' => ['00', []],
            'text' => ['Number', ['Number 0 user', 'Number 1 user', 'Number 123 user', 'Number 10 user']],
        ];
    }

    #[DataProvider('searchTermProvider')]
    public function test_search_term_filters_the_entries(string $searchTerm, array $expectedNames)
    {
        $response = $this->postJson($this->searchUrl, [
            'start' => 0,
            'length' => 10,
            'search' => ['value' => $searchTerm, 'regex' => 'false'],
        ]);

        $response->assertOk();
        $response->assertJsonPath('recordsTotal', User::count());
        $response->assertJsonPath('recordsFiltered', count($expectedNames));
        $this->assertCount(count($expectedNames), $response->json('data'));

        $this->assertEqualsCanonicalizing($expectedNames, $this->namesInResponse($response));
    }

    public function test_empty_search_term_returns_all_entries()
    {
        $response = $this->postJson($this->searchUrl, [
            'start' => 0,
            'length' => 10,
            'search' => ['value' => '', 'regex' => 'false'],
        ]);

        $response->assertOk();
        $response->assertJsonPath('recordsFiltered', User::count());
        $this->assertCount(User::count(), $response->json('data'));
    }

    public function test_malformed_search_input_is_ignored()
    {
        $requests = [
            'search as string' => ['search' => 'Number 0'],
            'search without value' => ['search' => ['regex' => 'false']],
            'search value as array' => ['search' => ['value' => ['0']]],
        ];

        foreach ($requests as $case => $searchInput) {
            $response = $this->postJson($this->searchUrl, array_merge(['start' => 0, 'length' => 10], $searchInput));

            $response->assertOk();
            $this->assertSame(User::count(), $response->json('recordsFiltered'), "Failed for case: {$case}");
        }
    }

    private function namesInResponse($response): array
    {
        return array_map(fn ($row) => trim(strip_tags($row[0])), $response->json('data'));
    }
}
