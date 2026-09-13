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
}
