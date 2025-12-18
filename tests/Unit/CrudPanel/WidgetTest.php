<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\app\Library\Widget;
use Backpack\CRUD\Tests\BaseTestClass;

/**
 * @covers Backpack\CRUD\app\Library\Widget
 */
class WidgetTest extends BaseTestClass
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clear the widgets collection before each test
        Widget::collection()->forget(Widget::collection()->keys()->all());
    }

    public function testAfterMethodMovesWidgetAfterDestination()
    {
        // Create three widgets
        Widget::add(['name' => 'widget_1', 'type' => 'card']);
        Widget::add(['name' => 'widget_2', 'type' => 'card']);
        Widget::add(['name' => 'widget_3', 'type' => 'card']);

        // Move widget_3 after widget_1
        Widget::add('widget_3')->after('widget_1');

        $keys = Widget::collection()->keys()->toArray();

        // Expected order: widget_1, widget_3, widget_2
        $this->assertEquals(['widget_1', 'widget_3', 'widget_2'], $keys);
    }

    public function testBeforeMethodMovesWidgetBeforeDestination()
    {
        // Create three widgets
        Widget::add(['name' => 'widget_1', 'type' => 'card']);
        Widget::add(['name' => 'widget_2', 'type' => 'card']);
        Widget::add(['name' => 'widget_3', 'type' => 'card']);

        // Move widget_3 before widget_1
        Widget::add('widget_3')->before('widget_1');

        $keys = Widget::collection()->keys()->toArray();

        // Expected order: widget_3, widget_1, widget_2
        $this->assertEquals(['widget_3', 'widget_1', 'widget_2'], $keys);
    }

    public function testAfterMethodWithNonExistentDestinationDoesNothing()
    {
        // Create two widgets
        Widget::add(['name' => 'widget_1', 'type' => 'card']);
        Widget::add(['name' => 'widget_2', 'type' => 'card']);

        // Try to move widget_2 after a non-existent widget
        Widget::add('widget_2')->after('non_existent');

        $keys = Widget::collection()->keys()->toArray();

        // Order should remain unchanged
        $this->assertEquals(['widget_1', 'widget_2'], $keys);
    }

    public function testBeforeMethodWithNonExistentDestinationDoesNothing()
    {
        // Create two widgets
        Widget::add(['name' => 'widget_1', 'type' => 'card']);
        Widget::add(['name' => 'widget_2', 'type' => 'card']);

        // Try to move widget_2 before a non-existent widget
        Widget::add('widget_2')->before('non_existent');

        $keys = Widget::collection()->keys()->toArray();

        // Order should remain unchanged
        $this->assertEquals(['widget_1', 'widget_2'], $keys);
    }

    public function testAfterMethodWorksWithMultipleWidgets()
    {
        // Create five widgets
        Widget::add(['name' => 'widget_1', 'type' => 'card']);
        Widget::add(['name' => 'widget_2', 'type' => 'card']);
        Widget::add(['name' => 'widget_3', 'type' => 'card']);
        Widget::add(['name' => 'widget_4', 'type' => 'card']);
        Widget::add(['name' => 'widget_5', 'type' => 'card']);

        // Move widget_5 after widget_2
        Widget::add('widget_5')->after('widget_2');

        $keys = Widget::collection()->keys()->toArray();

        // Expected order: widget_1, widget_2, widget_5, widget_3, widget_4
        $this->assertEquals(['widget_1', 'widget_2', 'widget_5', 'widget_3', 'widget_4'], $keys);
    }

    public function testBeforeMethodWorksWithMultipleWidgets()
    {
        // Create five widgets
        Widget::add(['name' => 'widget_1', 'type' => 'card']);
        Widget::add(['name' => 'widget_2', 'type' => 'card']);
        Widget::add(['name' => 'widget_3', 'type' => 'card']);
        Widget::add(['name' => 'widget_4', 'type' => 'card']);
        Widget::add(['name' => 'widget_5', 'type' => 'card']);

        // Move widget_1 before widget_4
        Widget::add('widget_1')->before('widget_4');

        $keys = Widget::collection()->keys()->toArray();

        // Expected order: widget_2, widget_3, widget_1, widget_4, widget_5
        $this->assertEquals(['widget_2', 'widget_3', 'widget_1', 'widget_4', 'widget_5'], $keys);
    }

    public function testAfterMethodReturnsWidgetInstance()
    {
        Widget::add(['name' => 'widget_1', 'type' => 'card']);
        $widget = Widget::add(['name' => 'widget_2', 'type' => 'card']);

        $result = $widget->after('widget_1');

        $this->assertInstanceOf(Widget::class, $result);
    }

    public function testBeforeMethodReturnsWidgetInstance()
    {
        Widget::add(['name' => 'widget_1', 'type' => 'card']);
        $widget = Widget::add(['name' => 'widget_2', 'type' => 'card']);

        $result = $widget->before('widget_1');

        $this->assertInstanceOf(Widget::class, $result);
    }

    public function testAfterMethodCanBeChained()
    {
        Widget::add(['name' => 'widget_1', 'type' => 'card']);
        Widget::add(['name' => 'widget_2', 'type' => 'card']);

        Widget::add(['name' => 'widget_3', 'type' => 'card'])
            ->after('widget_1')
            ->type('div');

        $widget = Widget::collection()->get('widget_3');

        $this->assertEquals('div', $widget->attributes['type']);
    }

    public function testBeforeMethodCanBeChained()
    {
        Widget::add(['name' => 'widget_1', 'type' => 'card']);
        Widget::add(['name' => 'widget_2', 'type' => 'card']);

        Widget::add(['name' => 'widget_3', 'type' => 'card'])
            ->before('widget_2')
            ->type('div');

        $widget = Widget::collection()->get('widget_3');

        $this->assertEquals('div', $widget->attributes['type']);
    }
}
