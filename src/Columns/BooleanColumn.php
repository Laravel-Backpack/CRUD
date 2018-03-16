<?php

namespace Backpack\CRUD\Columns;

class BooleanColumn extends Column
{
    protected $type = 'boolean';

    /**
     * @param $yesText
     * @param $noText
     * @return $this
     */
    public function texts($yesText, $noText)
    {
        $this->data['options'] = [
            0 => $yesText,
            1 => $noText
        ];
        return $this;
    }
}