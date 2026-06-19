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

            if ($totalChars >= $charLimit) {
                break;
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
     * Collect all .md files recursively under docsPath.
     *
     * @return string[]
     */
    private function collectMdFiles(string $docsPath): array
    {
        $files = [];
        $rit = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($docsPath));

        foreach ($rit as $file) {
            if ($file->isDir() || $file->getExtension() !== 'md') {
                continue;
            }

            $files[] = $file->getPathname();
        }

        return $files;
    }

    /**
     * Build a scoring key from a file path: relative path with / and _ replaced by -,
     * leading underscores stripped from filename part.
     *
     * Example: fields/select2-from-ajax.md → fields-select2-from-ajax
     */
    private function scoringKey(string $filePath, string $docsPath): string
    {
        $rel = ltrim(str_replace([$docsPath, DIRECTORY_SEPARATOR], ['', '-'], $filePath), '-');
        $rel = str_replace(['.md', '_'], ['', '-'], $rel);
        // Remove leading underscores from filename part (overview files start with _)
        $rel = preg_replace('/-_/', '-', $rel) ?? $rel;

        return strtolower($rel);
    }

    /**
     * Score and rank docs files by relevance to the given queries.
     *
     * Scoring strategy:
     *  - +3000 per query word that exactly matches the file basename
     *          (e.g. "create" → operations/create.md beats inline-create.md)
     *  - +2000 per query word that exactly matches a path segment
     *  - +500  per query word that is a substring of any segment
     *  - For files that score on path, content word frequency is added
     *  - Root-level files (no subdir) also get a light content-only pass
     *          (threshold ≥5 hits) so conceptual files like crud-testing.md
     *          still surface for queries whose words aren't in the filename
     *
     * @return string[]
     */
    private function findRelevantFiles(string $docsPath, array $queries): array
    {
        $files = $this->collectMdFiles($docsPath);
        $scored = [];

        foreach ($files as $file) {
            $key = $this->scoringKey($file, $docsPath);
            $basename = strtolower(basename($file, '.md'));
            $segments = preg_split('/[-]+/', $key, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            // Is this file directly in the docs root (no subdir)?
            $isRoot = dirname($file) === $docsPath;
            $score = 0;

            foreach ($queries as $rawQuery) {
                $words = preg_split('/[\s\-_]+/', strtolower((string) $rawQuery), -1, PREG_SPLIT_NO_EMPTY) ?: [];

                foreach ($words as $word) {
                    if (strlen($word) < 3) {
                        continue;
                    }

                    if ($word === $basename) {
                        $score += 3000;
                    } elseif (in_array($word, $segments, true)) {
                        $score += 2000;
                    } elseif (str_contains($key, $word)) {
                        $score += 500;
                    }
                }
            }

            // For files that scored on path, also add content frequency
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

            // Light content-only pass for root files that got no path score.
            // Subdirectory files are skipped — their paths are descriptive enough.
            if ($score === 0 && $isRoot) {
                $content = strtolower(file_get_contents($file) ?: '');

                foreach ($queries as $rawQuery) {
                    $words = preg_split('/[\s\-_]+/', strtolower((string) $rawQuery), -1, PREG_SPLIT_NO_EMPTY) ?: [];

                    foreach ($words as $word) {
                        if (strlen($word) < 3) {
                            continue;
                        }

                        $hits = substr_count($content, $word);
                        $score += $hits >= 5 ? $hits : 0;
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
