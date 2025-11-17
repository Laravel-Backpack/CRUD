<?php

namespace Backpack\CRUD\app\Library\CrudTesting\FieldTesters;

/**
 * Tester for relationship fields (select, select2, select_multiple, etc.).
 * Most relationship fields use Select2 for enhanced UX.
 */
class RelationshipFieldTester extends Select2FieldTester
{
    /**
     * {@inheritdoc}
     */
    public function generateFakeValue()
    {
        // Try to get a random model ID from the related model
        $model = $this->getRelatedModel();

        if ($model && method_exists($model, 'inRandomOrder')) {
            $record = $model::inRandomOrder()->first();

            if ($record) {
                return $record->getKey();
            }
        }

        // Fallback to parent implementation
        return parent::generateFakeValue();
    }

    /**
     * Get the related model class from field configuration.
     *
     * @return string|null
     */
    protected function getRelatedModel(): ?string
    {
        return $this->field['model'] ?? null;
    }

    /**
     * Get the display attribute for the related model.
     *
     * @return string
     */
    protected function getDisplayAttribute(): string
    {
        return $this->field['attribute'] ?? 'name';
    }
}
