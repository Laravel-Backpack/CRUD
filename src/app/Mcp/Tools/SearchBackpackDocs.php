<?php

namespace Backpack\CRUD\app\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class SearchBackpackDocs extends Tool
{
    protected string $description = 'Search Backpack for Laravel documentation. Use for ALL questions about Backpack fields, columns, filters, operations, widgets, CrudControllers, and the Backpack admin panel. Do NOT use search-docs for Backpack questions — it has no Backpack index.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'queries' => $schema->array()
                ->items($schema->string()->description('Search query'))
                ->description('List of search queries. Pass multiple if unsure of terminology, e.g. ["relationship field", "select2 belongs to"].')
                ->required(),
            'token_limit' => $schema->integer()
                ->description('Maximum tokens to return. Defaults to 30000.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $queries = $request->get('queries', []);

        if (is_string($queries)) {
            $queries = json_decode($queries, true) ?? [];
        }

        $tokenLimit = min((int) ($request->get('token_limit') ?? 30000), 100000);

        return Response::text($this->searchBundledDocs($queries, $tokenLimit));
    }

    private function searchBundledDocs(array $queries, int $tokenLimit): string
    {
        $docsPath = dirname(__DIR__, 4).DIRECTORY_SEPARATOR.'docs';

        if (! is_dir($docsPath)) {
            return 'Backpack documentation files not found.';
        }

        $results = [];
        $totalChars = 0;
        $charLimit = $tokenLimit * 4; // rough chars-per-token estimate

        $relevantFiles = $this->findRelevantFiles($docsPath, $queries);

        foreach ($relevantFiles as $file) {
            $content = file_get_contents($file);

            if ($content === false) {
                continue;
            }

            $remaining = $charLimit - $totalChars;
            if ($remaining <= 0) {
                break;
            }

            // this is just an hacky solution, because we have backpack docs with over 100kb
            // token usage with docs like this will be HUGE HUGE.
            // i've set a 10x token limit higher than the default laravel, and even then some files
            // do not fully fit. just for testing this was consuming tons of tokens, so I just
            // cut the hair of the file a bit, some things will obviously not work.
            if (strlen($content) > $remaining) {
                $content = substr($content, 0, $remaining);
            }

            $results[] = $content;
            $totalChars += strlen($content);
        }

        if ($results === []) {
            return 'No matching Backpack documentation found for: '.implode(', ', $queries);
        }

        return implode("\n\n---\n\n", $results);
    }

    /**
     * Score and rank docs files by relevance to the given queries.
     *
     * @return string[]
     */
    private function findRelevantFiles(string $docsPath, array $queries): array
    {
        $files = glob($docsPath.DIRECTORY_SEPARATOR.'*.md') ?: [];
        $scored = [];

        foreach ($files as $file) {
            $filename = strtolower(basename($file, '.md'));
            $score = 0;

            foreach ($queries as $rawQuery) {
                $query = strtolower((string) $rawQuery);
                $words = preg_split('/[\s\-_]+/', $query, -1, PREG_SPLIT_NO_EMPTY) ?: [];

                foreach ($words as $word) {
                    if (strlen($word) < 3) {
                        continue;
                    }

                    if (str_contains($filename, $word)) {
                        $score += 10;
                    }
                }
            }

            // For files that scored on filename, also count content hits
            if ($score > 0) {
                $content = strtolower(file_get_contents($file) ?: '');

                foreach ($queries as $rawQuery) {
                    $words = preg_split('/[\s\-_]+/', strtolower((string) $rawQuery), -1, PREG_SPLIT_NO_EMPTY) ?: [];

                    foreach ($words as $word) {
                        if (strlen($word) < 3) {
                            continue;
                        }

                        $score += substr_count($content, $word);
                    }
                }
            }

            // Light content pass for files that didn't match filename
            if ($score === 0) {
                $content = strtolower(file_get_contents($file) ?: '');

                foreach ($queries as $rawQuery) {
                    $words = preg_split('/[\s\-_]+/', strtolower((string) $rawQuery), -1, PREG_SPLIT_NO_EMPTY) ?: [];

                    foreach ($words as $word) {
                        if (strlen($word) < 3) {
                            continue;
                        }

                        $hits = substr_count($content, $word);
                        $score += $hits > 3 ? $hits : 0;
                    }
                }
            }

            if ($score > 0) {
                $scored[$file] = $score;
            }
        }

        arsort($scored);

        return array_keys($scored);
    }
}
