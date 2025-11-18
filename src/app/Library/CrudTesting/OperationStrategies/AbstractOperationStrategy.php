<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationStrategies;

use Illuminate\Support\Str;

/**
 * Base class for operation strategies with common functionality.
 */
abstract class AbstractOperationStrategy implements OperationStrategyInterface
{
    protected object $crudPanel;
    protected array $controllerInfo;

    public function __construct(object $crudPanel, array $controllerInfo)
    {
        $this->crudPanel = $crudPanel;
        $this->controllerInfo = $controllerInfo;
    }

    /**
     * Get columns configuration for list/show operations.
     *
     * @return array
     */
    protected function getColumnsConfiguration(): array
    {
        return collect($this->crudPanel->columns())
            ->map(function ($column) {
                return [
                    'name' => $column['name'],
                    'label' => $column['label'] ?? Str::title(str_replace('_', ' ', $column['name'])),
                    'type' => $column['type'] ?? 'text',
                    'attributes' => $column,
                ];
            })
            ->toArray();
    }

    /**
     * Get fields configuration for create/update operations.
     *
     * @return array
     */
    protected function getFieldsConfiguration(): array
    {
        return collect($this->crudPanel->fields())
            ->map(function ($field) {
                return [
                    'name' => $field['name'],
                    'label' => $field['label'] ?? Str::title(str_replace('_', ' ', $field['name'])),
                    'type' => $field['type'] ?? 'text',
                    'required' => $this->isFieldRequired($field),
                    'attributes' => $field,
                ];
            })
            ->toArray();
    }

    /**
     * Get filters configuration for list operation.
     *
     * @return array
     */
    protected function getFiltersConfiguration(): array
    {
        $filters = $this->crudPanel->filters();

        return collect($filters)
            ->map(function ($filter) {
                return [
                    'name' => $filter->name,
                    'label' => $filter->label,
                    'type' => $filter->type,
                ];
            })
            ->toArray();
    }

    /**
     * Get buttons configuration.
     *
     * @return array
     */
    protected function getButtonsConfiguration(): array
    {
        $buttons = $this->crudPanel->buttons();

        return $buttons->groupBy('stack')
            ->map(function ($stackButtons, $stack) {
                return $stackButtons->map(function ($button) {
                    return [
                        'name' => $button->name,
                        'type' => $button->type,
                        'stack' => $button->stack,
                    ];
                })->toArray();
            })
            ->toArray();
    }

    /**
     * Get save actions configuration.
     *
     * @return array
     */
    protected function getSaveActionsConfiguration(): array
    {
        $saveActions = $this->crudPanel->getOperationSetting('save_actions') ?? [];

        return collect($saveActions)
            ->map(function ($action) {
                return [
                    'name' => $action['name'],
                    'label' => $action['button_text'] ?? Str::title(str_replace('_', ' ', $action['name'])),
                ];
            })
            ->toArray();
    }

    /**
     * Check if a field is required based on validation rules.
     *
     * @param  array  $field
     * @return bool
     */
    protected function isFieldRequired(array $field): bool
    {
        // Check if field has validation rules
        if (! isset($field['validationRules'])) {
            return false;
        }

        $rules = $field['validationRules'];

        if (is_string($rules)) {
            return str_contains($rules, 'required');
        }

        if (is_array($rules)) {
            return in_array('required', $rules);
        }

        return false;
    }

    /**
     * Build the descriptor structure returned by test generators.
     */
    protected function makeTestDescriptor(string $name, string $description, string $testerMethod, array $extras = []): array
    {
        return array_merge([
            'name' => $name,
            'description' => $description,
            'tester_method' => $testerMethod,
        ], $extras);
    }
}
