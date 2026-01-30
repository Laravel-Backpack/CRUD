<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationStrategies;

/**
 * Strategy for 'ajax_upload' operation testing.
 */
class AjaxUploadOperationStrategy extends AbstractOperationStrategy
{
    /**
     * Get operation-specific configuration.
     *
     * @return array
     */
    public function getOperationConfiguration(): array
    {
        return [
            // No specific configuration needed for now
        ];
    }

    /**
     * Generate test methods for ajax_upload operation.
     *
     * @return array
     */
    public function generateTestMethods(): array
    {
        // The ajax_upload operation is an internal API endpoint used by upload fields.
        // It is typically tested as part of Create/Update operations via browser tests.
        // Isolated testing requires mocking file uploads which is specific to the implementation.
        // For now, we omit generating a direct test to avoid incorrect GET requests (it's POST only).
        return [];
    }
}
