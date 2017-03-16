<?php

namespace Backpack\CRUD\PanelTraits;

trait Tabs
{
    public $tabsEnabled = false;
    public $tabsType = 'horizontal';

    public function enableTabs()
    {
        $this->tabsEnabled = true;
        $this->setTabsType(config('backpack.crud.tabs_type', 'horizontal'));

        return $this->tabsEnabled;
    }

    public function disableTabs()
    {
        $this->tabsEnabled = false;

        return $this->tabsEnabled;
    }

    public function tabsEnabled()
    {
        return $this->tabsEnabled;
    }

    public function tabsDisabled()
    {
        return ! $this->tabsEnabled;
    }

    public function setTabsType($type)
    {
        $this->tabsType = $type;

        return $this->tabsType;
    }

    public function getTabsType()
    {
        return $this->tabsType;
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

    public function boxHasTabs($boxLabel)
    {
        return $this->tabsEnabled() && count($this->getTabs($boxLabel)) > 0;
    }

    public function tabExists($boxLabel, $tabLabel)
    {
        $tabs = $this->getTabs($boxLabel);

        return in_array($tabLabel, $tabs);
    }

    public function getLastTab($boxLabel)
    {
        $tabs = $this->getTabs($boxLabel);

        if (count($tabs)) {
            return last($tabs);
        }

        return false;
    }

    public function isLastTab($boxLabel, $tabLabel)
    {
        return $this->getLastTab($boxLabel) == $tabLabel;
    }

    public function getTabFields($boxLabel, $tabLabel)
    {
        if ($this->boxExists($boxLabel) && $this->tabExists($boxLabel, $tabLabel)) {
            $boxFields = $this->getBoxFields($boxLabel);

            $fields_for_current_tab = collect($boxFields)->filter(function ($value, $key) use ($tabLabel) {
                return isset($value['tab']) && $value['tab'] == $tabLabel;
            });

            if ($this->isLastTab($boxLabel, $tabLabel)) {
                $fields_without_a_tab = collect($boxFields)->filter(function ($value, $key) {
                    return ! isset($value['tab']);
                });

                $fields_for_current_tab = $fields_for_current_tab->merge($fields_without_a_tab);
            }

            return $fields_for_current_tab;
        }

        return [];
    }

    public function getTabs($boxLabel)
    {
        $tabs = [];
        $fields = $this->getBoxFields($boxLabel);

        $fields_with_tabs = collect($fields)
            ->filter(function ($value, $key) {
                return isset($value['tab']);
            })
            ->each(function ($value, $key) use (&$tabs) {
                if (! in_array($value['tab'], $tabs)) {
                    $tabs[] = $value['tab'];
                }
            });

        return $tabs;
    }
}
