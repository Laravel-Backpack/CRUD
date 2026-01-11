<?php

namespace Backpack\CRUD\app\Http\Controllers;

if (backpack_pro()) {
    class ChartController extends \Backpack\Pro\Http\Controllers\ChartController
    {
    }
} else {
    class ChartController
    {
    }
}
