<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

trait Tabs
{
    public function enableTabs()
    {
        $this->setOperationSetting('tabsEnabled', true);
        $this->setOperationSetting('tabsType', config('backpack.crud.' . $this->getCurrentOperation() . '.tabsType', 'horizontal'));

        return $this->tabsEnabled();
    }

    public function disableTabs()
    {
        $this->setOperationSetting('tabsEnabled', false);

        return $this->tabsEnabled();
    }

    /**
     * @return bool
     */
    public function tabsEnabled()
    {
        return $this->getOperationSetting('tabsEnabled');
    }

    /**
     * @return bool
     */
    public function tabsDisabled()
    {
        return !$this->tabsEnabled();
    }

    public function setTabsType($type)
    {
        $this->enableTabs();
        $this->setOperationSetting('tabsType', $type);

        return $this->getOperationSetting('tabsType');
    }

    /**
     * @return string
     */
    public function getTabsType()
    {
        return $this->getOperationSetting('tabsType');
    }

    public function enableVerticalTabs()
    {
        return $this->setTabsType('vertical');
    }

    public function disableVerticalTabs()
    {
        return $this->setTabsType('horizontal');
    }

    public function enableHorizontalTabs()
    {
        return $this->setTabsType('horizontal');
    }

    public function disableHorizontalTabs()
    {
        return $this->setTabsType('vertical');
    }

    /**
     * @param string $label
     *
     * @return bool
     */
    public function tabExists($label)
    {
        $tabs = $this->getTabs();

        return in_array($label, $tabs);
    }

    /**
     * @return bool|string
     */
    public function getLastTab()
    {
        $tabs = $this->getTabs();

        if (count($tabs)) {
            return last($tabs);
        }

        return false;
    }

    /**
     * @param $label
     *
     * @return bool
     */
    public function isLastTab($label)
    {
        return $this->getLastTab() == $label;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getFieldsWithoutATab()
    {
        $all_fields = $this->getOperationFields();

        $fields_without_a_tab = collect($all_fields)->filter(function ($value, $key) {
            return !isset($value['tab']);
        });

        return $fields_without_a_tab;
    }

    /**
     * @param $label
     *
     * @return array|\Illuminate\Support\Collection
     */
    public function getTabFields($label)
    {
        if ($this->tabExists($label)) {
            $all_fields = $this->getOperationFields();

            $fields_for_current_tab = collect($all_fields)->filter(function ($value, $key) use ($label) {
                return isset($value['tab']) && $value['tab'] == $label;
            });

            return $fields_for_current_tab;
        }

        return [];
    }

    /**
     * @return array
     */
    public function getTabs()
    {
        $tabs = [];
        $fields = $this->getOperationFields();

        $fields_with_tabs = collect($fields)
            ->filter(function ($value, $key) {
                return isset($value['tab']);
            })
            ->each(function ($value, $key) use (&$tabs) {
                if (!in_array($value['tab'], $tabs)) {
                    $tabs[] = $value['tab'];
                }
            });

        return $tabs;
    }

    /**
     * @return mixed
     */
    public function getOperationFields()
    {
        if ($this->getCurrentOperation() == "show") {
            return $this->columns();
        } else {
            return $this->getCurrentFields();
        }
    }
}
