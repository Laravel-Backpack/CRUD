<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Arr;
use Backpack\CRUD\app\Library\CrudPanel\CrudField;
use Backpack\CRUD\Tests\config\CrudPanel\BaseCrudPanel;
use Backpack\CRUD\Tests\config\Models\Star;
use Backpack\CRUD\Tests\config\Models\User;
use Illuminate\Http\Request;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Fields
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\FieldsProtectedMethods
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\FieldsPrivateMethods
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Input
 * @covers Backpack\CRUD\app\Library\CrudPanel\CrudField
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Input
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Views
 */
class CrudPanelFieldsTest extends BaseCrudPanel
{
    private $oneTextFieldArray = [
        'name' => 'field1',
        'label' => 'Field1',
        'type' => 'text',
    ];

    private $expectedOneTextFieldArray = [
        'field1' => [
            'name' => 'field1',
            'label' => 'Field1',
            'type' => 'text',
            'entity' => false,
        ],
    ];

    private $unknownFieldTypeArray = [
        'name' => 'field1',
        'type' => 'unknownType',
    ];

    private $invalidTwoFieldsArray = [
        [
            'keyOne' => 'field1',
            'keyTwo' => 'Field1',
        ],
        [
            'otherKey' => 'field2',
        ],
    ];

    private $twoTextFieldsArray = [
        [
            'name' => 'field1',
            'label' => 'Field1',
            'type' => 'text',
        ],
        [
            'name' => 'field2',
            'label' => 'Field2',
        ],
    ];

    private $expectedTwoTextFieldsArray = [
        'field1' => [
            'name' => 'field1',
            'label' => 'Field1',
            'type' => 'text',
            'entity' => false,
        ],
        'field2' => [
            'name' => 'field2',
            'label' => 'Field2',
            'type' => 'text',
            'entity' => false,
        ],
    ];

    private $threeTextFieldsArray = [
        [
            'name' => 'field1',
            'label' => 'Field1',
            'type' => 'text',
        ],
        [
            'name' => 'field2',
            'label' => 'Field2',
        ],
        [
            'name' => 'field3',
        ],
    ];

    private $expectedThreeTextFieldsArray = [
        'field1' => [
            'name' => 'field1',
            'label' => 'Field1',
            'type' => 'text',
            'entity' => false,
        ],
        'field2' => [
            'name' => 'field2',
            'label' => 'Field2',
            'type' => 'text',
            'entity' => false,
        ],
        'field3' => [
            'name' => 'field3',
            'label' => 'Field3',
            'type' => 'text',
            'entity' => false,
        ],
    ];

    private $multipleFieldTypesArray = [
        [
            'name' => 'field1',
            'label' => 'Field1',
        ],
        [
            'name' => 'field2',
            'type' => 'address',
        ],
        [
            'name' => 'field3',
            'type' => 'address',
        ],
        [
            'name' => 'field4',
            'type' => 'checkbox',
        ],
        [
            'name' => 'field5',
            'type' => 'date',
        ],
        [
            'name' => 'field6',
            'type' => 'email',
        ],
        [
            'name' => 'field7',
            'type' => 'hidden',
        ],
        [
            'name' => 'field8',
            'type' => 'password',
        ],
        [
            'name' => 'field9',
            'type' => 'select2',
        ],
        [
            'name' => 'field10',
            'type' => 'select2_multiple',
        ],
        [
            'name' => 'field11',
            'type' => 'table',
        ],
        [
            'name' => 'field12',
            'type' => 'url',
        ],
    ];

    private $expectedMultipleFieldTypesArray = [
        'field1' => [
            'name' => 'field1',
            'label' => 'Field1',
            'type' => 'text',
            'entity' => false,
        ],
        'field2' => [
            'name' => 'field2',
            'type' => 'address',
            'label' => 'Field2',
            'entity' => false,
        ],
        'field3' => [
            'name' => 'field3',
            'type' => 'address',
            'label' => 'Field3',
            'entity' => false,
        ],
        'field4' => [
            'name' => 'field4',
            'type' => 'checkbox',
            'label' => 'Field4',
            'entity' => false,
        ],
        'field5' => [
            'name' => 'field5',
            'type' => 'date',
            'label' => 'Field5',
            'entity' => false,
        ],
        'field6' => [
            'name' => 'field6',
            'type' => 'email',
            'label' => 'Field6',
            'entity' => false,
        ],
        'field7' => [
            'name' => 'field7',
            'type' => 'hidden',
            'label' => 'Field7',
            'entity' => false,
        ],
        'field8' => [
            'name' => 'field8',
            'type' => 'password',
            'label' => 'Field8',
            'entity' => false,
        ],
        'field9' => [
            'name' => 'field9',
            'type' => 'select2',
            'label' => 'Field9',
            'entity' => false,
        ],
        'field10' => [
            'name' => 'field10',
            'type' => 'select2_multiple',
            'label' => 'Field10',
            'entity' => false,
        ],
        'field11' => [
            'name' => 'field11',
            'type' => 'table',
            'label' => 'Field11',
            'entity' => false,
        ],
        'field12' => [
            'name' => 'field12',
            'type' => 'url',
            'label' => 'Field12',
            'entity' => false,
        ],
    ];

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->crudPanel->setOperation('create');
    }

    public function testAddFieldByName()
    {
        $this->crudPanel->addField('field1');

        $this->assertEquals(1, count($this->crudPanel->fields()));
        $this->assertEquals($this->expectedOneTextFieldArray, $this->crudPanel->fields());
    }

    public function testAddFieldsByName()
    {
        $this->crudPanel->addFields(['field1', 'field2', 'field3']);

        $this->assertEquals(3, count($this->crudPanel->fields()));
        $this->assertEquals($this->expectedThreeTextFieldsArray, $this->crudPanel->fields());
    }

    public function testAddFieldsAsArray()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);

        $this->assertEquals(3, count($this->crudPanel->fields()));
        $this->assertEquals($this->expectedThreeTextFieldsArray, $this->crudPanel->fields());
    }

    public function testAddFieldsDifferentTypes()
    {
        $this->crudPanel->addFields($this->multipleFieldTypesArray);

        $this->assertEquals(12, count($this->crudPanel->fields()));
        $this->assertEquals($this->expectedMultipleFieldTypesArray, $this->crudPanel->fields());
    }

    public function testAddFieldsInvalidArray()
    {
        $this->expectException(\Exception::class);

        $this->crudPanel->addFields($this->invalidTwoFieldsArray);
    }

    public function testAddFieldWithInvalidType()
    {
        $this->markTestIncomplete('Not correctly implemented');

        // TODO: should we validate field types and throw an error if they're not in the pre-defined list of fields or
        //       in the list of custom field?
        $this->crudPanel->addFields($this->unknownFieldTypeArray);
    }

    public function testAddFieldsForCreateForm()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray, 'create');

        $this->assertEquals(3, count($this->crudPanel->fields()));
        $this->assertEquals($this->expectedThreeTextFieldsArray, $this->crudPanel->getCreateFields());
    }

    public function testAddFieldsForUpdateForm()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray, 'update');

        $this->assertEquals(3, count($this->crudPanel->fields()));
        $this->assertEquals($this->expectedThreeTextFieldsArray, $this->crudPanel->fields());
    }

    public function testBeforeField()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);
        $this->crudPanel->beforeField('field2');

        $createKeys = array_keys($this->crudPanel->fields());
        $this->assertEquals($this->expectedThreeTextFieldsArray['field3'], $this->crudPanel->fields()[$createKeys[1]]);
        $this->assertEquals(['field1', 'field3', 'field2'], $createKeys);
    }

    public function testBeforeFieldFirstField()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);
        $this->crudPanel->beforeField('field1');

        $createKeys = array_keys($this->crudPanel->fields());
        $this->assertEquals($this->expectedThreeTextFieldsArray['field3'], $this->crudPanel->fields()[$createKeys[0]]);
        $this->assertEquals(['field3', 'field1', 'field2'], $createKeys);

        $updateKeys = array_keys($this->crudPanel->fields());
        $this->assertEquals($this->expectedThreeTextFieldsArray['field3'], $this->crudPanel->fields()[$updateKeys[0]]);
        $this->assertEquals(['field3', 'field1', 'field2'], $updateKeys);
    }

    public function testBeforeFieldLastField()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);
        $this->crudPanel->beforeField('field3');

        $createKeys = array_keys($this->crudPanel->fields());
        $this->assertEquals($this->expectedThreeTextFieldsArray['field3'], $this->crudPanel->fields()[$createKeys[2]]);
        $this->assertEquals(['field1', 'field2', 'field3'], $createKeys);
    }

    public function testBeforeFieldCreateForm()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);
        $this->crudPanel->beforeField('field1');

        $createKeys = array_keys($this->crudPanel->fields());
        $this->assertEquals($this->expectedThreeTextFieldsArray['field3'], $this->crudPanel->fields()[$createKeys[0]]);
        $this->assertEquals(['field3', 'field1', 'field2'], $createKeys);
    }

    public function testBeforeUnknownField()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);

        $this->crudPanel->beforeField('field4');

        $this->assertEquals(3, count($this->crudPanel->fields()));
        $this->assertEquals(array_keys($this->expectedThreeTextFieldsArray), array_keys($this->crudPanel->fields()));
    }

    public function testAfterField()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);

        $this->crudPanel->afterField('field1');

        $createKeys = array_keys($this->crudPanel->fields());
        $this->assertEquals($this->expectedThreeTextFieldsArray['field3'], $this->crudPanel->fields()[$createKeys[1]]);
        $this->assertEquals(['field1', 'field3', 'field2'], $createKeys);

        $updateKeys = array_keys($this->crudPanel->fields());
        $this->assertEquals($this->expectedThreeTextFieldsArray['field3'], $this->crudPanel->fields()[$updateKeys[1]]);
        $this->assertEquals(['field1', 'field3', 'field2'], $updateKeys);
    }

    public function testAfterFieldLastField()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);

        $this->crudPanel->afterField('field3');

        $createKeys = array_keys($this->crudPanel->fields());
        $this->assertEquals($this->expectedThreeTextFieldsArray['field3'], $this->crudPanel->fields()[$createKeys[2]]);
        $this->assertEquals(['field1', 'field2', 'field3'], $createKeys);

        $updateKeys = array_keys($this->crudPanel->fields());
        $this->assertEquals($this->expectedThreeTextFieldsArray['field3'], $this->crudPanel->fields()[$updateKeys[2]]);
        $this->assertEquals(['field1', 'field2', 'field3'], $updateKeys);
    }

    public function testAfterFieldOnCertainField()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);
        $this->crudPanel->addField('custom')->afterField('field1');

        $createKeys = array_keys($this->crudPanel->fields());
        $this->assertEquals(['field1', 'custom', 'field2', 'field3'], $createKeys);
    }

    public function testAfterUnknownField()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);

        $this->crudPanel->afterField('field4');

        $this->assertEquals(3, count($this->crudPanel->fields()));
        $this->assertEquals(array_keys($this->expectedThreeTextFieldsArray), array_keys($this->crudPanel->fields()));
    }

    public function testRemoveFieldsByName()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);

        $this->crudPanel->removeFields(['field1']);

        $this->assertEquals(2, count($this->crudPanel->fields()));
        $this->assertEquals(['field2', 'field3'], array_keys($this->crudPanel->fields()));
    }

    public function testRemoveFieldsByNameInvalidArray()
    {
        $this->markTestIncomplete('Not correctly implemented');

        $this->crudPanel->addFields($this->threeTextFieldsArray);

        // TODO: this should not work because the method specifically asks for an array of field keys, but it does
        //       because the removeField method will actually work with arrays instead of a string
        $this->crudPanel->removeFields($this->twoTextFieldsArray);

        $this->assertEquals(3, count($this->crudPanel->fields()));
        $this->assertEquals(array_keys($this->expectedThreeTextFieldsArray), array_keys($this->crudPanel->fields()));
    }

    public function testRemoveFieldsFromCreateForm()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);
        $this->crudPanel->removeFields(['field1']);

        $this->assertEquals(2, count($this->crudPanel->fields()));
        $this->assertEquals(['field2', 'field3'], array_keys($this->crudPanel->fields()));
    }

    public function testRemoveFieldsFromUpdateForm()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);
        $this->crudPanel->removeFields(['field1']);

        $this->assertEquals(2, count($this->crudPanel->fields()));
        $this->assertEquals(['field2', 'field3'], array_keys($this->crudPanel->fields()));
    }

    public function testRemoveUnknownFields()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);

        $this->crudPanel->removeFields(['field4']);

        $this->assertEquals(3, count($this->crudPanel->fields()));
        $this->assertEquals(3, count($this->crudPanel->fields()));
        $this->assertEquals(array_keys($this->expectedThreeTextFieldsArray), array_keys($this->crudPanel->fields()));
        $this->assertEquals(array_keys($this->expectedThreeTextFieldsArray), array_keys($this->crudPanel->fields()));
    }

    public function testOrderFields()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);

        $this->crudPanel->orderFields(['field2', 'field1', 'field3']);

        $this->assertEquals(['field2', 'field1', 'field3'], array_keys($this->crudPanel->fields()));
    }

    public function testOrderFieldsCreateForm()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);

        $this->crudPanel->orderFields(['field2', 'field1', 'field3'], 'create');

        $this->assertEquals(['field2', 'field1', 'field3'], array_keys($this->crudPanel->fields()));
        $this->assertEquals($this->expectedThreeTextFieldsArray, $this->crudPanel->fields());
    }

    public function testOrderFieldsUpdateForm()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);

        $this->crudPanel->orderFields(['field2', 'field1', 'field3'], 'update');

        $this->assertEquals($this->expectedThreeTextFieldsArray, $this->crudPanel->fields());
        $this->assertEquals(['field2', 'field1', 'field3'], array_keys($this->crudPanel->fields()));
    }

    public function testOrderFieldsIncompleteList()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);

        $this->crudPanel->orderFields(['field2', 'field3']);

        $this->assertEquals(['field2', 'field3', 'field1'], array_keys($this->crudPanel->fields()));
    }

    public function testOrderFieldsEmptyList()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);

        $this->crudPanel->orderFields([]);

        $this->assertEquals($this->expectedThreeTextFieldsArray, $this->crudPanel->fields());
    }

    public function testOrderFieldsUnknownList()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);

        $this->crudPanel->orderFields(['field4', 'field5', 'field6']);

        $this->assertEquals($this->expectedThreeTextFieldsArray, $this->crudPanel->fields());
    }

    public function testOrderColumnsMixedList()
    {
        $this->crudPanel->addFields($this->threeTextFieldsArray);

        $this->crudPanel->orderFields(['field2', 'field5', 'field6']);

        $this->assertEquals(['field2', 'field1', 'field3'], array_keys($this->crudPanel->fields()));
    }

    public function testCheckIfFieldIsFirstOfItsType()
    {
        $this->crudPanel->addFields($this->multipleFieldTypesArray);

        $isFirstAddressFieldFirst = $this->crudPanel->checkIfFieldIsFirstOfItsType($this->multipleFieldTypesArray[1]);
        $isSecondAddressFieldFirst = $this->crudPanel->checkIfFieldIsFirstOfItsType($this->multipleFieldTypesArray[2]);

        $this->assertTrue($isFirstAddressFieldFirst);
        $this->assertFalse($isSecondAddressFieldFirst);
    }

    public function testCheckIfUnknownFieldIsFirstOfItsType()
    {
        $isUnknownFieldFirst = $this->crudPanel->checkIfFieldIsFirstOfItsType($this->unknownFieldTypeArray, $this->expectedMultipleFieldTypesArray);

        $this->assertFalse($isUnknownFieldFirst);
    }

    public function testCheckIfInvalidFieldIsFirstOfItsType()
    {
        $this->expectException(\ErrorException::class);

        $this->crudPanel->checkIfFieldIsFirstOfItsType($this->invalidTwoFieldsArray[0], $this->expectedMultipleFieldTypesArray);
    }

    public function testDecodeJsonCastedAttributes()
    {
        $this->markTestIncomplete();

        // TODO: the decode JSON method should not be in fields trait and should not be exposed in the public API.
    }

    public function testFieldNameDotNotationIsRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addField('accountDetails.nickname');
        $fieldReadyForHtml = $this->crudPanel->fields()['accountDetails.nickname'];
        $fieldCleanState = $this->crudPanel->getCleanStateFields()['accountDetails.nickname'];
        $this->assertEquals(Arr::except($fieldReadyForHtml, ['name']), Arr::except($fieldCleanState, ['name']));
        $this->assertEquals($fieldCleanState['relation_type'], 'HasOne');
        $this->assertEquals($fieldReadyForHtml['name'], 'accountDetails[nickname]');
        $this->assertEquals($fieldCleanState['name'], 'accountDetails.nickname');
    }

    public function testFieldNameDotNotationIsRelationshipUsingFluentSynthax()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->field('accountDetails.nickname')->label('custom label');
        $fieldReadyForHtml = $this->crudPanel->fields()['accountDetails.nickname'];
        $fieldCleanState = $this->crudPanel->getCleanStateFields()['accountDetails.nickname'];
        $this->assertEquals(Arr::except($fieldReadyForHtml, ['name']), Arr::except($fieldCleanState, ['name']));
        $this->assertEquals($fieldCleanState['relation_type'], 'HasOne');
        $this->assertEquals($fieldReadyForHtml['name'], 'accountDetails[nickname]');
        $this->assertEquals($fieldCleanState['name'], 'accountDetails.nickname');
    }

    public function testFieldNameIsRelationInCrudModel()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addField('roles');
        $field = $this->crudPanel->fields()['roles'];
        $this->assertEquals($field['relation_type'], 'BelongsToMany');
    }

    public function testFieldNameIsPartialRelationInCrudModel()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addField('articles_id');
        $field = $this->crudPanel->fields()['articles_id'];
        $this->assertEquals($field['relation_type'], 'HasMany');
    }

    public function testGetStrippedSaveRequestWithClosure()
    {
        $this->crudPanel->setOperationSetting(
            'strippedRequest',
            static function (Request $request) {
                return $request->toArray();
            },
            'update'
        );
        $this->crudPanel->setOperation('update');
        $this->crudPanel->setModel(User::class);
        $request = request()->create('/users/1/edit', 'POST', ['name' => 'john']);
        $result = $this->crudPanel->getStrippedSaveRequest($request);
        $this->assertIsArray($result);
        $this->assertSame(['name' => 'john'], $result);
    }

    public function testGetStrippedSaveRequestWithClass()
    {
        $this->crudPanel->setOperationSetting(
            'strippedRequest',
            Invokable::class,
            'update'
        );
        $this->crudPanel->setOperation('update');
        $this->crudPanel->setModel(User::class);
        $request = request()->create('/users/1/edit', 'POST', ['name' => 'john']);
        $result = $this->crudPanel->getStrippedSaveRequest($request);
        $this->assertIsArray($result);
        $this->assertSame(['invokable' => 'invokable'], $result);
    }

    public function testItDoesNotUseProtectedMethodsAsRelationshipMethods()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addField('isNotRelation');
        $this->crudPanel->addField('isNotRelationPublic');

        $this->assertEquals(false, $this->crudPanel->fields()['isNotRelation']['entity']);
        $this->assertEquals(false, $this->crudPanel->fields()['isNotRelationPublic']['entity']);
    }

    public function testItAbortsOnUnexpectedEntity()
    {
        try {
            $this->crudPanel->getRelationInstance(['name' => 'doesNotExist', 'entity' => 'doesNotExist']);
        } catch (\Throwable $e) {
        }
        $this->assertEquals(
            new \Symfony\Component\HttpKernel\Exception\HttpException(500, 'Looks like field <code>doesNotExist</code> is not properly defined. The <code>doesNotExist()</code> relationship doesn\'t seem to exist on the <code>Backpack\CRUD\Tests\config\Models\TestModel</code> model.', null, ['developer-error-exception']),
            $e
        );
    }

    public function testItCanRemoveAllFields()
    {
        $this->crudPanel->addFields([
            ['name' => 'test1'],
            ['name' => 'test2'],
        ]);

        $this->assertCount(2, $this->crudPanel->fieldS());
        $this->crudPanel->removeAllFields();
        $this->assertCount(0, $this->crudPanel->fieldS());
    }

    public function testItCanRemoveAnAttributeFromAField()
    {
        $this->crudPanel->addField(['name' => 'test', 'tab' => 'test']);

        $this->assertEquals('test', $this->crudPanel->fieldS()['test']['tab']);
        $this->crudPanel->removeFieldAttribute('test', 'tab');
        $this->assertNull($this->crudPanel->fieldS()['test']['tab'] ?? null);
    }

    public function testItCanSetALabelForAField()
    {
        $this->crudPanel->addField(['name' => 'test', 'tab' => 'test']);
        $this->crudPanel->setFieldLabel('test', 'my-test-label');
        $this->assertEquals('my-test-label', $this->crudPanel->fieldS()['test']['label']);
    }

    public function testItCanMakeAFieldFirst()
    {
        $firstField = $this->crudPanel->addField(['name' => 'test1']);
        $secondField = $this->crudPanel->addField(['name' => 'test2']);
        $this->assertEquals(['test1', 'test2'], array_keys($this->crudPanel->fields()));
        $secondField->makeFirstField();
        $this->assertEquals(['test2', 'test1'], array_keys($this->crudPanel->fields()));
    }

    public function testItCanGetTheCurrentFields()
    {
        $this->crudPanel->addField(['name' => 'test1']);
        $this->crudPanel->addField(['name' => 'test2']);

        $this->assertCount(2, $this->crudPanel->getCurrentFields());
        $this->assertCount(2, $this->crudPanel->getFields());
    }

    public function testItCanGetUploadFields()
    {
        $this->crudPanel->addField(['name' => 'test1', 'upload' => true]);
        $this->crudPanel->addField(['name' => 'test2']);

        $this->assertCount(2, $this->crudPanel->getFields());
        $this->assertTrue($this->crudPanel->hasUploadFields());
    }

    public function testItCanGetTheFieldTypeWithViewNamespace()
    {
        $this->crudPanel->addField(['name' => 'test', 'view_namespace' => 'test_namespace']);
        $this->assertEquals('test_namespace.text', $this->crudPanel->getFieldTypeWithNamespace($this->crudPanel->fields()['test']));
        $this->assertEquals('test', $this->crudPanel->getFieldTypeWithNamespace('test'));
    }

    public function testItCanGetAllFieldNames()
    {
        $this->crudPanel->addField(['name' => 'test1']);
        $this->crudPanel->addField(['name' => 'test2']);
        $this->assertEquals(['test1', 'test2'], $this->crudPanel->getAllFieldNames());
    }

    public function testItCanAddAFluentField()
    {
        $this->crudPanel->setModel(User::class);
        $createdAtClosure = static function () {
        };
        $this->crudPanel->field('my_field')
                        ->type('my_custom_type')
                        ->label('my_label')
                        ->tab('custom_tab')
                        ->prefix('prefix')
                        ->suffix('suffix')
                        ->hint('hinter')
                        ->fake(false)
                        ->validationRules('required|min:2')
                        ->validationMessages(['required' => 'is_required', 'min' => 'min_2'])
                        ->store_in('some')
                        ->size(6)
                        ->on('created', $createdAtClosure)
                        ->subfields([['name' => 'sub_1']])
                        ->entity('bang');

        $this->assertCount(1, $this->crudPanel->fields());

        $this->assertEquals([
            'name' => 'my_field',
            'type' => 'my_custom_type',
            'entity' => 'bang',
            'relation_type' => 'BelongsTo',
            'attribute' => 'name',
            'model' => 'Backpack\CRUD\Tests\Config\Models\Bang',
            'multiple' => false,
            'pivot' => false,
            'label' => 'my_label',
            'tab' => 'custom_tab',
            'suffix' => 'suffix',
            'prefix' => 'prefix',
            'hint' => 'hinter',
            'fake' => false,
            'validationRules' => 'required|min:2',
            'validationMessages' => [
                'required' => 'is_required',
                'min' => 'min_2',
            ],
            'store_in' => 'some',
            'wrapper' => [
                'class' => 'form-group col-md-6 mb-3',
            ],
            'events' => [
                'created' => $createdAtClosure,
            ],
            'subfields' => [
                [
                    'name' => 'sub_1',
                    'parentFieldName' => 'my_field',
                    'type' => 'text',
                    'entity' => false,
                    'label' => 'Sub 1',
                    'baseFieldName' => 'sub_1',
                ],
            ],

        ], $this->crudPanel->fields()['my_field']);
    }

    public function testAddFieldFluentClassUsingArrayDefinition()
    {
        $this->crudPanel->field($this->oneTextFieldArray);

        $this->assertEquals(1, count($this->crudPanel->fields()));
        $this->assertEquals($this->expectedOneTextFieldArray, $this->crudPanel->fields());
    }

    public function testItCanFluentlyAddUploadAttribute()
    {
        $this->crudPanel->field('avatar')->upload();
        $this->assertEquals(true, $this->crudPanel->fields()['avatar']['upload']);
    }

    public function testItCanMakeAFieldFirstFluently()
    {
        $this->crudPanel->field('test1');
        $this->crudPanel->field('test2')->makeFirst();
        $crudFields = $this->crudPanel->fields();
        $firstField = reset($crudFields);
        $this->assertEquals($firstField['name'], 'test2');
    }

    public function testItCanMakeAFieldLastFluently()
    {
        $this->crudPanel->field('test1');
        $this->crudPanel->field('test2');
        $this->crudPanel->field('test1')->makeLast();
        $crudFields = $this->crudPanel->fields();
        $firstField = reset($crudFields);
        $this->assertEquals($firstField['name'], 'test2');
    }

    public function testItCanPlaceFieldsFluently()
    {
        $this->crudPanel->field('test1');
        $this->crudPanel->field('test2');
        $this->crudPanel->field('test3')->after('test1');

        $crudFieldsNames = array_column($this->crudPanel->fields(), 'name');
        $this->assertEquals($crudFieldsNames, ['test1', 'test3', 'test2']);

        $this->crudPanel->field('test4')->before('test1');
        $crudFieldsNames = array_column($this->crudPanel->fields(), 'name');
        $this->assertEquals($crudFieldsNames, ['test4', 'test1', 'test3', 'test2']);
    }

    public function testItCanRemoveFieldAttributesFluently()
    {
        $this->crudPanel->field('test1')->type('test');
        $this->assertEquals($this->crudPanel->fields()['test1']['type'], 'test');
        $this->crudPanel->field('test1')->forget('type');
        $this->assertNull($this->crudPanel->fields()['test1']['type'] ?? null);
    }

    public function testItCanRemoveFieldFluently()
    {
        $this->crudPanel->field('test1')->type('test');
        $this->assertCount(1, $this->crudPanel->fields());
        $this->crudPanel->field('test1')->remove();
        $this->assertCount(0, $this->crudPanel->fields());
    }

    public function testItCanAddMorphFieldsFluently()
    {
        $this->crudPanel->setModel(Star::class);
        $this->crudPanel->field('starable')
                        ->addMorphOption('Backpack\CRUD\Tests\config\Models\User', 'User')
                        ->morphTypeField(['attributes' => ['custom-attribute' => true]])
                        ->morphIdField(['attributes' => ['custom-attribute' => true]]);

        [$morphTypeField, $morphIdField] = $this->crudPanel->fields()['starable']['subfields'];

        $this->assertTrue($morphTypeField['attributes']['custom-attribute']);
        $this->assertTrue($morphIdField['attributes']['custom-attribute']);
    }

    public function testAbortsConfiguringNonMorphTypeField()
    {
        $this->crudPanel->setModel(Star::class);
        $this->expectException(\Exception::class);
        $this->crudPanel->field('some_field')
                        ->morphTypeField(['attributes' => ['custom-attribute' => true]]);
    }

    public function testAbortsConfiguringNonMorphIdField()
    {
        $this->crudPanel->setModel(Star::class);
        $this->expectException(\Exception::class);
        $this->crudPanel->field('some_field')
                        ->morphIdField(['attributes' => ['custom-attribute' => true]]);
    }

    public function testItCanGetTheFieldAttributes()
    {
        $this->crudPanel->field('some_field');

        $this->assertEquals($this->crudPanel->fields()['some_field'], $this->crudPanel->field('some_field')->getAttributes());
    }

    public function testItAbortsWithEmptyNamesFluently()
    {
        try {
            CrudField::name('');
        } catch (\Throwable $e) {
        }
        $this->assertEquals(
            new \Symfony\Component\HttpKernel\Exception\HttpException(500, 'Field name can\'t be empty.', null, ['developer-error-exception']),
            $e
        );
    }

    public function testCheckReturnTypesForWhenInferringRelation()
    {
        $this->crudPanel->setModel(\Backpack\CRUD\Tests\config\Models\UserWithReturnTypes::class);
        $this->crudPanel->addField('isAnAttribute');
        $this->crudPanel->addField('isARelation');

        $this->assertEquals(false, $this->crudPanel->fields()['isAnAttribute']['entity']);
        $this->assertEquals('isARelation', $this->crudPanel->fields()['isARelation']['entity']);
    }

    public function testItCanGetTheFirstFieldViewWithoutProvidingNamespace()
    {
        $this->assertEquals('backpack.theme-coreuiv2::fields.test', $this->crudPanel->getFirstFieldView('test'));
    }

    public function testItCanGetTheFirstFieldViewInProvidedNamespace()
    {
        $this->assertEquals('backpack.theme-coreuiv2::fields.custom_namespace.test', $this->crudPanel->getFirstFieldView('test', 'backpack.theme-coreuiv2::fields.custom_namespace'));
    }

    public function testItDoesntAttemptToMoveFieldsIfTheyDontExist()
    {
        $this->assertFalse($this->crudPanel->makeFirstField());
    }

    public function testItThrowExceptionWhenViewNotFound()
    {
        $this->expectException(\Exception::class);
        $this->crudPanel->getFirstFieldView('test2');
    }

    public function testItCanGetUploadFieldsFromSubfields()
    {
        $this->crudPanel->addField([
            'name' => 'test1',
            'subfields' => [
                ['name' => 'test1_1', 'upload' => true],
                ['name' => 'test1_2'],
            ],
        ]);
        $this->crudPanel->addField([
            'name' => 'test2',
            'subfields' => [
                ['name' => 'test2_1'],
                ['name' => 'test2_2'],
            ],
        ]);

        $this->assertCount(2, $this->crudPanel->getFields());
        $this->assertTrue($this->crudPanel->hasUploadFields());
    }

    public function testItCanMarkAFieldTypeAsLoaded()
    {
        $this->crudPanel->addField([
            'name' => 'test1',
            'type' => 'text',
        ]);
        $field = $this->crudPanel->fields()['test1'];
        $this->assertTrue($this->crudPanel->markFieldTypeAsLoaded($field));
        $this->assertFalse($this->crudPanel->markFieldTypeAsLoaded($field));
        $this->assertTrue($this->crudPanel->fieldTypeLoaded($field));
        $this->assertTrue($this->crudPanel->fieldTypeNotLoaded(['type' => 'test']));
        $this->assertEquals(['text'], $this->crudPanel->getLoadedFieldTypes());
    }

    public function testItCanGetFieldNamesFromNamesWithCommas()
    {
        $this->crudPanel->addField('test1, test2');
        $this->assertEquals(['test1', 'test2'], $this->crudPanel->getAllFieldNames());
    }

    public function testItCanInferFieldAttributesFromADynamicRelation()
    {
        User::resolveRelationUsing('dynamicRelation', function ($user) {
            return $user->hasOne(\Backpack\CRUD\Tests\config\Models\AccountDetails::class);
        });

        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addField('dynamicRelation.nickname');

        $this->assertEquals([
            'name' => 'dynamicRelation[nickname]',
            'type' => 'relationship',
            'entity' => 'dynamicRelation.nickname',
            'relation_type' => 'HasOne',
            'attribute' => 'nickname',
            'model' => 'Backpack\CRUD\Tests\Config\Models\AccountDetails',
            'multiple' => false,
            'pivot' => false,
            'label' => 'DynamicRelation.nickname',
        ], $this->crudPanel->fields()['dynamicRelation.nickname']);
    }
}

class Invokable
{
    public function __invoke(): array
    {
        return ['invokable' => 'invokable'];
    }
}
