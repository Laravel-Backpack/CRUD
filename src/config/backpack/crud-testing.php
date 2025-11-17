<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CRUD Testing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Backpack CRUD testing framework.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Controller Discovery Paths
    |--------------------------------------------------------------------------
    |
    | Paths where the framework should look for CrudControllers.
    | By default, it searches in app/Http/Controllers.
    |
    */
    'discovery_paths' => [
        app_path('Http/Controllers'),
        app_path('Http/Controllers/Admin'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Browser Testing Driver
    |--------------------------------------------------------------------------
    |
    | The browser testing driver to use. Options: 'dusk', 'http'
    | - 'dusk': Use Laravel Dusk for full browser automation
    | - 'http': Use simple HTTP testing (faster but less comprehensive)
    |
    */
    'driver' => env('CRUD_TEST_DRIVER', 'dusk'),

    /*
    |--------------------------------------------------------------------------
    | Test Data Factory
    |--------------------------------------------------------------------------
    |
    | Whether to automatically create factory definitions for discovered models.
    |
    */
    'auto_generate_factories' => false,

    /*
    |--------------------------------------------------------------------------
    | Test Database
    |--------------------------------------------------------------------------
    |
    | Database connection to use for testing.
    |
    */
    'test_database' => [
        'connection' => env('DB_CONNECTION', 'sqlite'),
        'database' => env('DB_DATABASE', ':memory:'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Screenshot on Failure
    |--------------------------------------------------------------------------
    |
    | Whether to take screenshots when browser tests fail.
    | Only works with Dusk driver.
    |
    */
    'screenshot_on_failure' => env('CRUD_TEST_SCREENSHOTS', true),

    /*
    |--------------------------------------------------------------------------
    | Wait Timeouts
    |--------------------------------------------------------------------------
    |
    | Default timeouts for various operations (in seconds).
    |
    */
    'timeouts' => [
        'page_load' => 10,
        'datatable_load' => 10,
        'ajax_request' => 5,
        'modal' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Coverage
    |--------------------------------------------------------------------------
    |
    | Which operations and features to test automatically.
    |
    */
    'coverage' => [
        'operations' => [
            'list' => true,
            'create' => true,
            'update' => true,
            'show' => true,
            'delete' => true,
            'reorder' => false,
            'bulk_delete' => false,
            'clone' => false,
        ],
        'features' => [
            'datatables' => true,
            'filters' => true,
            'search' => true,
            'pagination' => true,
            'sorting' => true,
            'validation' => true,
            'buttons' => true,
            'fields' => true,
            'columns' => true,
            'save_actions' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Field Testing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for field-specific testing.
    |
    */
    'fields' => [
        // Path to custom field testers
        'custom_testers_path' => app_path('CrudTesting/FieldTesters'),

        // Skip testing for these field types
        'skip_types' => [
            // 'custom_html',
            // 'view',
        ],

        // Test file upload paths
        'test_files' => [
            'text' => storage_path('app/test-files/test.txt'),
            'image' => storage_path('app/test-files/test.jpg'),
            'pdf' => storage_path('app/test-files/test.pdf'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Operation Testing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for operation-specific testing.
    |
    */
    'operations' => [
        // Path to custom operation testers
        'custom_testers_path' => app_path('CrudTesting/OperationTesters'),

        // Number of test entries to create for list operations
        'list_test_entries' => 10,

        // Whether to test with existing data or always create fresh data
        'use_existing_data' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Field Testers
    |--------------------------------------------------------------------------
    |
    | Register custom field testers for specific field types.
    | These will override the built-in convention-based resolution.
    |
    | Example:
    |   'my_custom_field' => \App\Tests\FieldTesters\MyCustomFieldTester::class,
    |   'wysiwyg' => \App\Tests\FieldTesters\MyWysiwygTester::class, // Override built-in
    |
    */
    'field_testers' => [
        // Add your custom field type => tester class mappings here
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Field Tester Namespaces
    |--------------------------------------------------------------------------
    |
    | Additional namespaces to search for field testers using convention-based naming.
    | The framework will automatically look for {FieldType}FieldTester classes
    | in these namespaces.
    |
    | Example: If you have a 'color' field and this namespace is registered,
    | the framework will look for \App\Tests\FieldTesters\ColorFieldTester
    |
    */
    'field_tester_namespaces' => [
        // 'App\\Tests\\FieldTesters',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Operation Testers
    |--------------------------------------------------------------------------
    |
    | Register custom operation testers for specific operations.
    | These will override the built-in operation testers.
    |
    | Example:
    |   'list' => \App\Tests\OperationTesters\MyListOperationTester::class,
    |
    */
    'operation_testers' => [
        // Add your custom operation => tester class mappings here
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Operation Tester Namespaces
    |--------------------------------------------------------------------------
    |
    | Additional namespaces to search for operation testers.
    | The framework will look for {Operation}OperationTester classes.
    |
    */
    'operation_tester_namespaces' => [
        // 'App\\Tests\\OperationTesters',
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Generation
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic test generation.
    |
    */
    'generation' => [
        // Where to generate test files
        'output_path' => base_path('tests/Browser/Crud'),

        // Test class namespace
        'namespace' => 'Tests\\Browser\\Crud',

        // Test class naming pattern (use {controller} as placeholder)
        'class_name_pattern' => '{controller}Test',

        // Whether to overwrite existing test files
        'overwrite_existing' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    |
    | Configuration for authenticating users during tests.
    |
    */
    'auth' => [
        // User model to use for authentication
        'model' => config('backpack.base.user_model_fqn', 'App\\Models\\User'),

        // User attributes for test user creation
        'test_user' => [
            'email' => 'admin@test.com',
            'password' => 'password',
            'name' => 'Test Admin',
        ],

        // Login route
        'login_route' => 'backpack.auth.login',
    ],

    /*
    |--------------------------------------------------------------------------
    | Selectors
    |--------------------------------------------------------------------------
    |
    | CSS selectors used by the testing framework.
    | You can customize these if your CRUD views use different selectors.
    |
    */
    'selectors' => [
        'datatable' => '#crudTable',
        'datatable_search' => '#datatable_search_stack input',
        'filters_button' => '.filters-dropdown-button',
        'filters_dropdown' => '.filters-dropdown',
        'form' => 'form',
        'save_button' => 'button[type="submit"]',
        'validation_error' => '.invalid-feedback',
        'success_alert' => '.alert-success',
        'error_alert' => '.alert-danger',
        'confirmation_popup' => '.swal2-popup',
        'confirmation_confirm' => '.swal2-confirm',
        'confirmation_cancel' => '.swal2-cancel',
    ],
];
