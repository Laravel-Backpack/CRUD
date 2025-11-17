<?php

namespace Backpack\CRUD\app\Library\CrudTesting\FieldTesters;

/**
 * Base class for field testing strategies.
 * Each field type can have a specific tester that extends this class.
 */
abstract class FieldTester
{
    protected array $field;

    public function __construct(array $field)
    {
        $this->field = $field;
    }

    /**
     * Get the field selector for browser tests.
     *
     * @return string
     */
    abstract public function getSelector(): string;

    /**
     * Fill the field with a test value.
     *
     * @param  mixed  $browser  Laravel Dusk Browser instance
     * @param  mixed  $value
     * @return mixed
     */
    abstract public function fill($browser, $value);

    /**
     * Generate a fake value for this field type.
     *
     * @return mixed
     */
    abstract public function generateFakeValue();

    /**
     * Assert that the field is visible.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function assertVisible($browser): void
    {
        $browser->assertVisible($this->getSelector());
    }

    /**
     * Assert that the field has a specific value.
     *
     * @param  mixed  $browser
     * @param  mixed  $value
     * @return void
     */
    public function assertValue($browser, $value): void
    {
        $browser->assertInputValue($this->getSelector(), $value);
    }

    /**
     * Get field name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->field['name'];
    }

    /**
     * Get field label.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->field['label'] ?? ucfirst(str_replace('_', ' ', $this->field['name']));
    }

    /**
     * Check if field is required.
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->field['required'] ?? false;
    }

    /**
     * Create a field tester instance for the given field configuration.
     *
     * @param  array  $field
     * @return FieldTester
     */
    public static function make(array $field): FieldTester
    {
        $type = $field['type'] ?? 'text';

        $testerClass = static::resolveTesterClass($type);

        return new $testerClass($field);
    }

    /**
     * Resolve the tester class for a given field type.
     *
     * @param  string  $type
     * @return string
     */
    protected static function resolveTesterClass(string $type): string
    {
        // 1. Check for custom override in config
        $customClass = config("backpack.crud-testing.field_testers.{$type}");
        if ($customClass && class_exists($customClass)) {
            return $customClass;
        }

        // 2. Try convention-based class name in this namespace
        $conventionClass = static::getConventionBasedClass($type);
        if (class_exists($conventionClass)) {
            return $conventionClass;
        }

        // 3. Try convention-based class name in custom paths
        foreach (static::getCustomTesterPaths() as $namespace) {
            $customPath = $namespace.'\\'.static::getTesterClassName($type);
            if (class_exists($customPath)) {
                return $customPath;
            }
        }

        // 4. Fallback to TextFieldTester
        return TextFieldTester::class;
    }

    /**
     * Get convention-based class name for a field type.
     *
     * @param  string  $type
     * @return string
     */
    protected static function getConventionBasedClass(string $type): string
    {
        return __NAMESPACE__.'\\'.static::getTesterClassName($type);
    }

    /**
     * Get the class name for a field type following naming conventions.
     *
     * @param  string  $type
     * @return string
     */
    protected static function getTesterClassName(string $type): string
    {
        // Convert snake_case to PascalCase
        return str_replace('_', '', ucwords($type, '_')).'FieldTester';
    }

    /**
     * Get custom tester namespaces from configuration.
     *
     * @return array
     */
    protected static function getCustomTesterPaths(): array
    {
        return config('backpack.crud-testing.field_tester_namespaces', []);
    }
}
