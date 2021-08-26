<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\Unit\Models\User;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Fields
 */
class CrudPanelFieldsTest extends BaseDBCrudPanelTest
{
    private $oneTextFieldArray = [
        'name'  => 'field1',
        'label' => 'Field1',
        'type'  => 'text',
    ];

    private $expectedOneTextFieldArray = [
        'field1' => [
            'name'  => 'field1',
            'label' => 'Field1',
            'type'  => 'text',
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
            'name'  => 'field1',
            'label' => 'Field1',
            'type'  => 'text',
        ],
        [
            'name'  => 'field2',
            'label' => 'Field2',
        ],
    ];

    private $expectedTwoTextFieldsArray = [
        'field1' => [
            'name'  => 'field1',
            'label' => 'Field1',
            'type'  => 'text',
        ],
        'field2' => [
            'name'  => 'field2',
            'label' => 'Field2',
            'type'  => 'text',
        ],
    ];

    private $threeTextFieldsArray = [
        [
            'name'  => 'field1',
            'label' => 'Field1',
            'type'  => 'text',
        ],
        [
            'name'  => 'field2',
            'label' => 'Field2',
        ],
        [
            'name' => 'field3',
        ],
    ];

    private $expectedThreeTextFieldsArray = [
        'field1' => [
            'name'  => 'field1',
            'label' => 'Field1',
            'type'  => 'text',
        ],
        'field2' => [
            'name'  => 'field2',
            'label' => 'Field2',
            'type'  => 'text',
        ],
        'field3' => [
            'name'  => 'field3',
            'label' => 'Field3',
            'type'  => 'text',
        ],
    ];

    private $multipleFieldTypesArray = [
        [
            'name'  => 'field1',
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
            'name'  => 'field1',
            'label' => 'Field1',
            'type'  => 'text',
        ],
        'field2' => [
            'name'  => 'field2',
            'type'  => 'address',
            'label' => 'Field2',
        ],
        'field3' => [
            'name'  => 'field3',
            'type'  => 'address',
            'label' => 'Field3',
        ],
        'field4' => [
            'name'  => 'field4',
            'type'  => 'checkbox',
            'label' => 'Field4',
        ],
        'field5' => [
            'name'  => 'field5',
            'type'  => 'date',
            'label' => 'Field5',
        ],
        'field6' => [
            'name'  => 'field6',
            'type'  => 'email',
            'label' => 'Field6',
        ],
        'field7' => [
            'name'  => 'field7',
            'type'  => 'hidden',
            'label' => 'Field7',
        ],
        'field8' => [
            'name'  => 'field8',
            'type'  => 'password',
            'label' => 'Field8',
        ],
        'field9' => [
            'name'  => 'field9',
            'type'  => 'select2',
            'label' => 'Field9',
        ],
        'field10' => [
            'name'  => 'field10',
            'type'  => 'select2_multiple',
            'label' => 'Field10',
        ],
        'field11' => [
            'name'  => 'field11',
            'type'  => 'table',
            'label' => 'Field11',
        ],
        'field12' => [
            'name'  => 'field12',
            'type'  => 'url',
            'label' => 'Field12',
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
        $this->assertEquals($this->expectedThreeTextFieldsArray, $this->crudPanel->fields());
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
        $field = $this->crudPanel->fields()['accountDetails[nickname]'];
        $this->assertEquals($field['relation_type'], 'HasOne');
    }

    public function testCanInferRelationTypeFromRelationName()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addField('roles');
        $field = $this->crudPanel->fields()['roles'];
        $this->assertEquals($field['relation_type'], 'BelongsToMany');
    }

    public function testFieldNamePartiallyMatchesRelationName()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addField('articles_id');
        $field = $this->crudPanel->fields()['articles_id'];
        $this->assertEquals($field['relation_type'], 'HasMany');
    }

    public function testCanGetRelationFieldsBySingleRelation()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addField('roles');
        $this->crudPanel->addField('accountDetails.nickname');
        $relationField = $this->crudPanel->getFieldsWithRelationType('BelongsToMany');
        $this->assertCount(1, $relationField);
    }

    public function testCanGetRelationFieldsByArrayOfRelations()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addField('roles');
        $this->crudPanel->addField('accountDetails.nickname');
        // this next field should not be returned because is not a direct relation
        $this->crudPanel->addField('accountDetails.article');
        $relationField = $this->crudPanel->getFieldsWithRelationType(['BelongsToMany', 'HasOne']);

        $this->assertCount(2, $relationField);
    }
}
