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

        // v2: section-extraction with content-optimized ranking
        $results = [];
        $totalChars = 0;
        $charLimit = $tokenLimit * 4; // rough chars-per-token estimate
        $maxFiles = 10;

        $relevantFiles = $this->findRelevantFiles($docsPath, $queries);

        foreach ($relevantFiles as $file) {
            if (count($results) >= $maxFiles) {
                break;
            }

            $content = $this->extractRelevantSections($file, $queries);

            if ($content === '') {
                continue;
            }

            if ($totalChars >= $charLimit) {
                break;
            }

            $fileName = str_replace([$docsPath.DIRECTORY_SEPARATOR, '.md', DIRECTORY_SEPARATOR], ['', '', ' › '], $file);
            $results[] = "## {$fileName}\n\n{$content}";
            $totalChars += strlen($content);
        }

        if ($results === []) {
            return 'No matching Backpack documentation found for: '.implode(', ', $queries);
        }

        return implode("\n\n---\n\n", $results);
    }

    /**
     * Extract sections from a markdown file that are relevant to the queries.
     * Returns the heading + content of matching sections, or the first portion
     * of the file if no specific sections match.
     */
    private function extractRelevantSections(string $filePath, array $queries): string
    {
        $raw = file_get_contents($filePath);

        if ($raw === false) {
            return '';
        }

        $words = [];

        foreach ($queries as $q) {
            $words = array_merge($words, preg_split('/[\s\-_]+/', strtolower((string) $q), -1, PREG_SPLIT_NO_EMPTY) ?: []);
        }

        $words = array_unique(array_filter($words, fn ($w) => strlen($w) >= 3));

        if ($words === []) {
            return $raw;
        }

        // Split on all heading levels (## through ######)
        $sections = preg_split('/(?=^#{2,6} )/m', $raw);
        $matching = [];
        $intro = '';

        foreach ($sections as $section) {
            $lower = strtolower($section);
            $hits = 0;

            foreach ($words as $word) {
                $hits += substr_count($lower, $word);
            }

            if ($hits >= 3) {
                $matching[] = trim($section);
            } elseif ($intro === '' && ! str_starts_with(trim($section), '#')) {
                // Capture the intro/title portion (content before first heading)
                $intro = trim($section);
            }
        }

        // Prepend the intro (title + first paragraph) if there is one
        if ($intro !== '' && $intro !== '0') {
            array_unshift($matching, $intro);
        }

        // If too many sections matched, limit to avoid overwhelming
        $maxSections = 6;

        if (count($matching) > $maxSections) {
            $matching = array_slice($matching, 0, $maxSections);
            $matching[] = '*(additional matching sections omitted — refine your query for more specific results)*';
        }

        return implode("\n\n", $matching);
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
     *  - +2000 per query word that exactly matches a path segment
     *  - +500  per query word that is a substring of any segment
     *  - +5× per content word occurrence (applied to ALL files)
     *  - Top 10 files returned with relevant sections extracted
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

            // Content frequency bonus — applied to ALL files.
            // Weighted so content relevance competes with filename matches
            // without letting common words in large files dominate.
            $content = strtolower(file_get_contents($file) ?: '');

            foreach ($queries as $rawQuery) {
                $words = preg_split('/[\s\-_]+/', strtolower((string) $rawQuery), -1, PREG_SPLIT_NO_EMPTY) ?: [];

                foreach ($words as $word) {
                    if (strlen($word) < 3) {
                        continue;
                    }

                    $score += substr_count($content, $word) * 5;
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
