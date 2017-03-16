<?php

namespace Backpack\CRUD\PanelTraits;

trait Boxes
{
    public $sideBoxesEnabled = false;
    public $boxesOptions = [];

    public function enableSideBoxes()
    {
        $this->sideBoxesEnabled = true;

        return $this->sideBoxesEnabled;
    }

    public function disableSideBoxes()
    {
        $this->sideBoxesEnabled = false;

        return $this->sideBoxesEnabled;
    }

    public function sideBoxesEnabled()
    {
        return $this->sideBoxesEnabled;
    }

    public function sideBoxesDisabled()
    {
        return ! $this->sideBoxesEnabled;
    }

    public function boxExists($label)
    {
        $boxes = $this->getBoxes();

        return in_array($label, $boxes);
    }

    public function getLastBox()
    {
        $boxes = $this->getBoxes();

        if (count($boxes)) {
            return last($boxes);
        }

        return false;
    }

    public function isLastBox($label)
    {
        return $this->getLastBox() == $label;
    }

    public function getBoxFields($label)
    {
        if ($this->boxExists($label)) {
            $all_fields = $this->getCurrentFields();

            $fields_for_current_box = collect($all_fields)->filter(function ($value, $key) use ($label) {
                return isset($value['box']) && $value['box'] == $label;
            });

            if ($this->isLastBox($label)) {
                $fields_without_a_box = collect($all_fields)->filter(function ($value, $key) {
                    return ! isset($value['box']);
                });

                $fields_for_current_box = $fields_for_current_box->merge($fields_without_a_box);
            }

            return $fields_for_current_box;
        }

        return [];
    }

    public function setBoxOptions($label, array $options)
    {
        $this->boxesOptions[$label] = $options;

        // if a "side" box was mentioned, we should enable it
        if (! empty($options['side'])) {
            $this->enableSideBoxes();
        }
    }

    public function getBoxOptions($label)
    {
        $boxOptions = [
            'side' => false,
            'class' => '',
            'collapsed' => false,
        ];

        if (isset($this->boxesOptions[$label])) {
            $boxOptions = array_merge($boxOptions, $this->boxesOptions[$label]);
        }

        if ($boxOptions['collapsed']) {
            $boxOptions['class'] = trim($boxOptions['class'].' collapsed-box');
        }

        return $boxOptions;
    }

    public function getBoxes($columnFilter = null)
    {
        $boxes = [];
        $fields = $this->getCurrentFields();

        $fields_with_boxes = collect($fields)
            ->filter(function ($value, $key) {
                return isset($value['box']);
            })
            ->each(function ($value, $key) use (&$boxes, $columnFilter) {
                if (! isset($columnFilter) || ($columnFilter === 'side' xor ! $this->getBoxOptions($value['box'])['side'])) {
                    if (! in_array($value['box'], $boxes)) {
                        $boxes[] = $value['box'];
                    }
                }
            });

        // Default Box
        if (empty($boxes) && $columnFilter !== 'side') {
            $boxes[] = ucfirst($this->entity_name);
        }

        return $boxes;
    }
}
