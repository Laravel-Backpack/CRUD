<?php

namespace Backpack\CRUD\app\Library\CrudTesting\FieldTesters;

/**
 * Tester for repeatable fields (dynamic field groups).
 */
class RepeatableFieldTester extends FieldTester
{
    /**
     * {@inheritdoc}
     */
    public function getSelector(): string
    {
        return "[data-repeatable-holder='{$this->getName()}']";
    }

    /**
     * {@inheritdoc}
     */
    public function fill($browser, $value)
    {
        // $value should be an array of row data
        if (! is_array($value)) {
            return $browser;
        }

        foreach ($value as $index => $rowData) {
            // Click add button if not first row
            if ($index > 0) {
                $this->addRow($browser);
            }

            // Fill each subfield in this row
            $this->fillRow($browser, $index, $rowData);
        }

        return $browser;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFakeValue()
    {
        $subfields = $this->getSubfields();
        $row = [];

        foreach ($subfields as $subfield) {
            $subfieldTester = FieldTester::make($subfield);
            $row[$subfield['name']] = $subfieldTester->generateFakeValue();
        }

        return [$row];
    }

    /**
     * Add a new row to the repeatable field.
     *
     * @param  mixed  $browser
     * @return mixed
     */
    protected function addRow($browser)
    {
        $addButtonSelector = "{$this->getSelector()} .repeatable-add-button";
        $browser->click($addButtonSelector);

        // Wait for new row to be added
        $browser->pause(500);

        return $browser;
    }

    /**
     * Fill a specific row with data.
     *
     * @param  mixed  $browser
     * @param  int  $rowIndex
     * @param  array  $rowData
     * @return mixed
     */
    protected function fillRow($browser, int $rowIndex, array $rowData)
    {
        foreach ($rowData as $subfieldName => $subfieldValue) {
            $selector = $this->getSubfieldSelector($subfieldName, $rowIndex);
            $browser->type($selector, $subfieldValue);
        }

        return $browser;
    }

    /**
     * Get the selector for a subfield within a row.
     *
     * @param  string  $subfieldName
     * @param  int  $rowIndex
     * @return string
     */
    protected function getSubfieldSelector(string $subfieldName, int $rowIndex = 0): string
    {
        return "{$this->getSelector()} [data-repeatable-input-name='{$subfieldName}']";
    }

    /**
     * Get subfields configuration.
     *
     * @return array
     */
    protected function getSubfields(): array
    {
        return $this->field['subfields'] ?? [];
    }

    /**
     * Delete a row from the repeatable field.
     *
     * @param  mixed  $browser
     * @param  int  $rowIndex
     * @return mixed
     */
    protected function deleteRow($browser, int $rowIndex)
    {
        $deleteButtonSelector = "{$this->getSelector()} .repeatable-delete-button:nth-of-type(".($rowIndex + 1).')';
        $browser->click($deleteButtonSelector);
        $browser->pause(300);

        return $browser;
    }
}
