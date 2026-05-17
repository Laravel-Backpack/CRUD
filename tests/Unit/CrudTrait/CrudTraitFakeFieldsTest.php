<?php

namespace Backpack\CRUD\Tests\Unit\CrudTrait;

use Unit\CrudPanel\Models\FakeColumnsModel;

/**
 * Class CrudTraitFakeFieldsTest.
 *
 * @group CrudTraitFakeFields
 *
 * @covers Backpack\CRUD\app\Models\Traits\HasFakeFields
 */
class CrudTraitFakeFieldsTest extends BaseCrudTraitTest
{
    private $locale;
    /**
     * @var FakeColumnsModel
     */
    private $model;

    // DEFINE THE DATA

    private $extras = [
        'extras_first' => 'Extras first',
        'extras_second' => 'Extras second',
    ];

    private $extras_translatable = [
        'en' => [
            'extras_translatable_first' => 'extras_translatable first en',
            'extras_translatable_second' => 'extras_translatable second en',
        ],
        'ro' => [
            'extras_translatable_first' => 'extras_translatable first ro',
            'extras_translatable_second' => 'extras_translatable second ro',
        ],

    ];

    private $fake_object = [
        'fake_object_first' => 'fake_object first',
        'fake_object_second' => 'fake_object second',
    ];

    private $fake_assoc_array = [
        'fake_assoc_array_first' => 'fake_assoc_array first',
        'fake_assoc_array_second' => 'fake_assoc_array second',
    ];

    /**
     * Setup function for each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->locale = \App::getLocale();

        $this->fake_object = (object) $this->fake_object;

        $this->model = new FakeColumnsModel();

        $this->model->extras = json_encode($this->extras);

        $this->model->setTranslation('extras_translatable', 'en', json_encode($this->extras_translatable['en']));
        $this->model->setTranslation('extras_translatable', 'ro', json_encode($this->extras_translatable['ro']));

        $this->model->fake_object = $this->fake_object;
        $this->model->fake_assoc_array = $this->fake_assoc_array;

        $this->model = $this->model->withFakes();
    }

    public function testExtrasGetFaked()
    {
        $this->assertEquals($this->extras, json_decode($this->model->extras, true));

        $this->assertEquals($this->extras['extras_first'], $this->model->extras_first);
        $this->assertEquals($this->extras['extras_second'], $this->model->extras_second);
    }

    public function testExtrasTranslatableGetFaked()
    {
        $this->assertEquals($this->extras_translatable[$this->locale], json_decode($this->model->extras_translatable, true));

        $this->assertEquals($this->extras_translatable[$this->locale]['extras_translatable_first'], $this->model->extras_translatable_first);
        $this->assertEquals($this->extras_translatable[$this->locale]['extras_translatable_second'], $this->model->extras_translatable_second);
    }

    public function testFakeObjectGetsFaked()
    {
        $this->assertEquals($this->fake_object, $this->model->fake_object);

        $this->assertEquals($this->fake_object->fake_object_first, $this->model->fake_object->fake_object_first);
        $this->assertEquals($this->fake_object->fake_object_first, $this->model->fake_object_first);

        $this->assertEquals($this->fake_object->fake_object_second, $this->model->fake_object->fake_object_second);
        $this->assertEquals($this->fake_object->fake_object_second, $this->model->fake_object_second);
    }

    public function testFakeAssocArrayGetsFaked()
    {
        $this->assertEquals($this->fake_assoc_array, $this->model->fake_assoc_array);

        $this->assertEquals($this->fake_assoc_array['fake_assoc_array_first'], $this->model->fake_assoc_array['fake_assoc_array_first']);
        $this->assertEquals($this->fake_assoc_array['fake_assoc_array_first'], $this->model->fake_assoc_array_first);

        $this->assertEquals($this->fake_assoc_array['fake_assoc_array_second'], $this->model->fake_assoc_array['fake_assoc_array_second']);
        $this->assertEquals($this->fake_assoc_array['fake_assoc_array_second'], $this->model->fake_assoc_array_second);
    }
}
