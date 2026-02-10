<?php

namespace Backpack\CRUD\app\Library\CrudTesting\Helpers;

/**
 * Helper class for interacting with CRUD buttons in browser tests.
 */
class ButtonHelper
{
    protected $browser;

    public function __construct($browser)
    {
        $this->browser = $browser;
    }

    /**
     * Click a top stack button.
     *
     * @param  string  $buttonName
     * @return self
     */
    public function clickTopButton(string $buttonName): self
    {
        $selector = "[data-button-name='{$buttonName}']";
        $this->browser->click($selector);

        return $this;
    }

    /**
     * Click a line stack button in a specific row.
     *
     * @param  string  $buttonName
     * @param  int  $rowIndex
     * @return self
     */
    public function clickLineButton(string $buttonName, int $rowIndex = 0): self
    {
        $selector = '#crudTable tbody tr:nth-child('.($rowIndex + 1).") [data-button-name='{$buttonName}']";
        $this->browser->click($selector);

        return $this;
    }

    /**
     * Click a bottom stack button.
     *
     * @param  string  $buttonName
     * @return self
     */
    public function clickBottomButton(string $buttonName): self
    {
        $selector = "[data-button-name='{$buttonName}']";
        $this->browser->click($selector);

        return $this;
    }

    /**
     * Assert that a button is visible.
     *
     * @param  string  $buttonName
     * @param  string  $stack
     * @return self
     */
    public function assertVisible(string $buttonName, string $stack = 'line'): self
    {
        $selector = "[data-button-name='{$buttonName}']";
        $this->browser->assertVisible($selector);

        return $this;
    }

    /**
     * Assert that a button is not visible.
     *
     * @param  string  $buttonName
     * @return self
     */
    public function assertNotVisible(string $buttonName): self
    {
        $selector = "[data-button-name='{$buttonName}']";
        $this->browser->assertMissing($selector);

        return $this;
    }

    /**
     * Click the create button.
     *
     * @return self
     */
    public function clickCreate(): self
    {
        $this->browser->click('.create-button, [href*="/create"]');

        return $this;
    }

    /**
     * Click the edit button for a row.
     *
     * @param  int  $rowIndex
     * @return self
     */
    public function clickEdit(int $rowIndex = 0): self
    {
        $selector = '#crudTable tbody tr:nth-child('.($rowIndex + 1).") [data-button-type='edit'], #crudTable tbody tr:nth-child(".($rowIndex + 1).') .edit-button';
        $this->browser->click($selector);

        return $this;
    }

    /**
     * Click the delete button for a row.
     *
     * @param  int  $rowIndex
     * @return self
     */
    public function clickDelete(int $rowIndex = 0): self
    {
        $selector = '#crudTable tbody tr:nth-child('.($rowIndex + 1).") [data-button-type='delete'], #crudTable tbody tr:nth-child(".($rowIndex + 1).') .delete-button';
        $this->browser->click($selector);

        return $this;
    }

    /**
     * Click the show/preview button for a row.
     *
     * @param  int  $rowIndex
     * @return self
     */
    public function clickShow(int $rowIndex = 0): self
    {
        $selector = '#crudTable tbody tr:nth-child('.($rowIndex + 1).") [data-button-type='show'], #crudTable tbody tr:nth-child(".($rowIndex + 1).') .show-button';
        $this->browser->click($selector);

        return $this;
    }

    /**
     * Confirm delete in SweetAlert popup.
     *
     * @return self
     */
    public function confirmDelete(): self
    {
        $this->browser->waitFor('.swal2-confirm', 2);
        $this->browser->click('.swal2-confirm');
        $this->browser->pause(1000);

        return $this;
    }

    /**
     * Cancel delete in SweetAlert popup.
     *
     * @return self
     */
    public function cancelDelete(): self
    {
        $this->browser->waitFor('.swal2-cancel', 2);
        $this->browser->click('.swal2-cancel');

        return $this;
    }

    /**
     * Assert that a confirmation dialog is visible.
     *
     * @return self
     */
    public function assertConfirmationVisible(): self
    {
        $this->browser->waitFor('.swal2-popup', 3);
        $this->browser->assertVisible('.swal2-popup');

        return $this;
    }

    /**
     * Wait for a button to be clickable.
     *
     * @param  string  $buttonName
     * @param  int  $seconds
     * @return self
     */
    public function waitForClickable(string $buttonName, int $seconds = 5): self
    {
        $selector = "[data-button-name='{$buttonName}']";
        $this->browser->waitFor($selector, $seconds);

        return $this;
    }
}
