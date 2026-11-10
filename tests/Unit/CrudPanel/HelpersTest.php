<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\config\CrudPanel\BaseCrudPanel;
use Illuminate\Http\Request;

class HelpersTest extends BaseCrudPanel
{
    public function testBackpackFormInputParsesRepeatableFieldsFunction()
    {
        $input = [
            'form' => [
                [
                    'name' => 'repeatable[0][name]',
                    'value' => 'first row name',
                ],
                [
                    'name' => 'repeatable[0][age]',
                    'value' => '23',
                ],
                [
                    'name' => 'repeatable[1][name]',
                    'value' => 'second row name',
                ],
                [
                    'name' => 'repeatable[1][age]',
                    'value' => '24',
                ],
            ],
        ];

        $request = new Request($input);
        app()->handle($request);

        $expectedOutput = [
            'repeatable' => [
                [
                    'name' => 'first row name',
                    'age' => '23',
                ],
                [
                    'name' => 'second row name',
                    'age' => '24',
                ],
            ],
        ];

        $this->assertEquals($expectedOutput, backpack_form_input());
    }

    public function testBackpackFormInputParsesDotNotationFields()
    {
        $input = [
            'form' => [
                [
                    'name' => 'address[street]',
                    'value' => 'street name',
                ],
                [
                    'name' => 'address[postal_code]',
                    'value' => '234',
                ],
            ],
        ];

        $request = new Request($input);
        app()->handle($request);

        $expectedOutput = [
            'address' => [
                'street' => 'street name',
                'postal_code' => '234',
            ],
        ];

        $this->assertEquals($expectedOutput, backpack_form_input());
    }

    public function testBackpackFormInputHandleDifferentInputTypesAtSameTime()
    {
        $input = [
            'form' => [
                [
                    'name' => 'address[street]',
                    'value' => 'street name',
                ],
                [
                    'name' => 'address[postal_code]',
                    'value' => '234',
                ],
                [
                    'name' => 'repeatable[0][name]',
                    'value' => 'first row name',
                ],
                [
                    'name' => 'repeatable[0][age]',
                    'value' => '23',
                ],
                [
                    'name' => 'repeatable[1][name]',
                    'value' => 'second row name',
                ],
                [
                    'name' => 'repeatable[1][age]',
                    'value' => '24',
                ],
                [
                    'name' => 'simple_field',
                    'value' => 'simple value',
                ],
            ],
        ];

        $request = new Request($input);
        app()->handle($request);

        $expectedOutput = [
            'address' => [
                'street' => 'street name',
                'postal_code' => '234',
            ],
            'repeatable' => [
                [
                    'name' => 'first row name',
                    'age' => '23',
                ],
                [
                    'name' => 'second row name',
                    'age' => '24',
                ],
            ],
            'simple_field' => 'simple value',
        ];

        $this->assertEquals($expectedOutput, backpack_form_input());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('internalUrlsProvider')]
    public function testBackpackSafeRedirectUrlAllowsInternalUrls(string $url)
    {
        $this->assertSame($url, backpack_safe_redirect_url($url, 'fallback'));
    }

    public static function internalUrlsProvider(): array
    {
        return [
            'absolute path' => ['/admin/user'],
            'absolute path with query and fragment' => ['/admin/user?page=2&name=%20john#top'],
            'relative path' => ['admin/user'],
            'query only' => ['?page=2'],
            'http on app host' => ['http://localhost/admin/user'],
            'https on app host' => ['https://localhost/admin/user'],
            'app host with port' => ['http://localhost:8080/admin/user'],
            'app host uppercase' => ['HTTP://LOCALHOST/admin/user'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('unsafeUrlsProvider')]
    public function testBackpackSafeRedirectUrlRejectsUnsafeUrls($url)
    {
        $this->assertSame('fallback', backpack_safe_redirect_url($url, 'fallback'));
    }

    public static function unsafeUrlsProvider(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
            'array' => [['/admin']],
            'external http' => ['http://evil.com'],
            'external https' => ['https://evil.com/admin/user'],
            'protocol relative' => ['//evil.com'],
            'protocol relative with path' => ['//evil.com/localhost'],
            'backslash after slash' => ['/\\evil.com'],
            'double backslash' => ['\\\\evil.com'],
            'leading space' => [' //evil.com'],
            'leading tab' => ["\t//evil.com"],
            'leading newline' => ["\n//evil.com"],
            'embedded null byte' => ["/admin\0//evil.com"],
            'scheme without slashes' => ['https:evil.com'],
            'userinfo with app host' => ['https://localhost@evil.com'],
            'subdomain of app host' => ['https://localhost.evil.com'],
            'javascript scheme' => ['javascript:alert(1)'],
            'javascript scheme with app host' => ['javascript://localhost/%0aalert(1)'],
            'data scheme' => ['data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg=='],
            'ftp on app host' => ['ftp://localhost/file'],
            'malformed url' => ['http:///evil.com'],
        ];
    }

    public function testBackpackSafeRedirectUrlReturnsNullFallbackByDefault()
    {
        $this->assertNull(backpack_safe_redirect_url('https://evil.com'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('safeLinkUrlProvider')]
    public function testBackpackSafeLinkUrlKeepsSafeUrls($url)
    {
        $this->assertSame($url, backpack_safe_link_url($url));
    }

    public static function safeLinkUrlProvider(): array
    {
        return [
            'https' => ['https://example.com/path?query=1'],
            'http' => ['http://example.com'],
            'protocol relative' => ['//example.com/image.jpg'],
            'absolute path' => ['/storage/image.jpg'],
            'relative path' => ['storage/image.jpg'],
            'mailto' => ['mailto:someone@example.com'],
            'tel' => ['tel:+123456789'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('unsafeLinkUrlProvider')]
    public function testBackpackSafeLinkUrlRejectsUnsafeUrls($url)
    {
        $this->assertNull(backpack_safe_link_url($url));
    }

    public static function unsafeLinkUrlProvider(): array
    {
        return [
            'javascript' => ['javascript:alert(1)'],
            'javascript uppercase' => ['JAVASCRIPT:alert(1)'],
            'javascript with leading whitespace' => ["  \tjavascript:alert(1)"],
            'javascript with tab inside the scheme' => ["java\tscript:alert(1)"],
            'javascript with newline inside the scheme' => ["java\nscript:alert(1)"],
            'javascript with slashes' => ['javascript://%0aalert(1)'],
            'vbscript' => ['vbscript:msgbox(1)'],
            'data' => ['data:text/html,<script>alert(1)</script>'],
            'not a string' => [['javascript:alert(1)']],
        ];
    }
}
