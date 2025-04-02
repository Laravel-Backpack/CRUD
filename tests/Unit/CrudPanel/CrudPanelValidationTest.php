<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\app\Library\Validation\Rules\ValidUpload;
use Backpack\CRUD\app\Library\Validation\Rules\ValidUploadMultiple;
use Backpack\CRUD\Tests\config\Http\Requests\UserRequest;
use Backpack\CRUD\Tests\config\Models\User;
use Illuminate\Http\UploadedFile;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Validation
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Read
 * @covers Backpack\CRUD\app\Library\Validation\Rules\BackpackCustomRule
 * @covers Backpack\CRUD\app\Library\Validation\Rules\ValidUpload
 * @covers Backpack\CRUD\app\Library\Validation\Rules\ValidUploadMultiple
 * @covers Backpack\CRUD\app\Library\Validation\Rules\ValidFileArray
 * @covers Backpack\CRUD\app\Library\Validation\Rules\Support\HasFiles
 */
class CrudPanelValidationTest extends \Backpack\CRUD\Tests\config\CrudPanel\BaseCrudPanel
{
    public function testItThrowsValidationExceptions()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setValidation(UserRequest::class);

        $request = request()->create('users/', 'POST', [
            'email' => 'test@test.com',
            'password' => 'test',
        ]);

        $this->crudPanel->setRequest($request);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $validatedRequest = $this->crudPanel->validateRequest();
    }

    public function testItMergesFieldValidationWithRequestValidation()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setValidation(UserRequest::class);

        $request = request()->create('users/', 'POST', [
            'name' => 'test name',
            'email' => 'test@test.com',
            'password' => 'test',
        ]);

        $request->setRouteResolver(function () use ($request) {
            return (new Route('POST', 'users', ['Backpack\CRUD\Tests\Config\Http\Controllers\UserCrudController', 'create']))->bind($request);
        });

        $this->crudPanel->addFields([
            [
                'name' => 'email',
                'validationRules' => 'required',
            ],
            [
                'name' => 'name',
            ],
            [
                'name' => 'password',
            ],
        ]);

        $this->crudPanel->setRequest($request);

        $this->crudPanel->validateRequest();

        $this->assertEquals(['email'], array_keys($this->crudPanel->getOperationSetting('validationRules')));
    }

    public function testItCanGetTheValidationAttributesFromFields()
    {
        $this->crudPanel->addField([
            'name' => 'email',
            'validationAttribute' => 'emailed',
        ]);

        $this->crudPanel->setValidation();

        $this->assertEquals(['email' => 'emailed'], $this->crudPanel->getOperationSetting('validationAttributes'));
    }

    public function testItCanGetTheValidationAttributesFromSubfields()
    {
        $this->crudPanel->addField([
            'name' => 'email',
            'subfields' => [
                [
                    'name' => 'test',
                    'validationAttribute' => 'emailed',
                ],
            ],
        ]);

        $this->crudPanel->setValidation();

        $this->assertEquals(['email.*.test' => 'emailed'], $this->crudPanel->getOperationSetting('validationAttributes'));
    }

    public function testItMergesAllKindsOfValidation()
    {
        $this->crudPanel->setModel(User::class);

        $this->crudPanel->setOperation('create');
        $this->crudPanel->setValidation([
            'password' => 'required',
        ]);
        $this->crudPanel->setValidation(UserRequest::class);

        $request = request()->create('users/', 'POST', [
            'name' => '',
            'password' => '',
            'email' => '',
        ]);

        $request->setRouteResolver(function () use ($request) {
            return (new Route('POST', 'users', ['Backpack\CRUD\Tests\Config\Http\Controllers\UserCrudController', 'create']))->bind($request);
        });

        $this->crudPanel->addFields([
            [
                'name' => 'email',
                'validationRules' => 'required',
            ],
            [
                'name' => 'name',
            ],
            [
                'name' => 'password',
            ],
        ]);

        $this->crudPanel->setRequest($request);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        try {
            $this->crudPanel->validateRequest();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertEquals(['password', 'email', 'name'], array_keys($e->errors()));
            throw $e;
        }
    }

    public function testItCanGetTheValidationFromFields()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');

        $request = request()->create('users/', 'POST', [
            'name' => 'test name',
            'email' => 'test@test.com',
            'password' => 'test',
        ]);

        $request->setRouteResolver(function () use ($request) {
            return (new Route('POST', 'users', ['Backpack\CRUD\Tests\Config\Http\Controllers\UserCrudController', 'create']))->bind($request);
        });

        $this->crudPanel->addField([
            'name' => 'email',
            'validationRules' => 'required',
        ]);

        $this->crudPanel->addField([
            'name' => 'name',
            'validationRules' => 'required',
            'validationMessages' => [
                'required' => 'required ma friend',
            ],
        ]);

        $this->crudPanel->addField([
            'name' => 'password',
            'subfields' => [
                [
                    'name' => 'test',
                    'validationRules' => 'required',
                    'validationMessages' => [
                        'required' => 'required ma friend',
                    ],
                ],
            ],
        ]);

        $this->crudPanel->setRequest($request);

        $this->crudPanel->setValidation();

        $validatedRequest = $this->crudPanel->validateRequest();

        $this->assertEquals(['email', 'name', 'password.*.test'], array_keys($this->crudPanel->getOperationSetting('validationRules')));
    }

    public function testItThrowsExceptionWithInvalidValidationClass()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');

        try {
            $this->crudPanel->setValidation('\Backpack\CRUD\Tests\config\Models\User');
        } catch (\Throwable $e) {
        }
        $this->assertEquals(
            new \Symfony\Component\HttpKernel\Exception\HttpException(500, 'Please pass setValidation() nothing, a rules array or a FormRequest class.', null, ['developer-error-exception']),
            $e
        );
    }

    public function testItCanDisableTheValidation()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');
        $this->crudPanel->setValidation([
            'name' => 'required',
            'password' => 'required',
        ]);
        $this->crudPanel->setValidation(UserRequest::class);
        $this->assertEquals(['name', 'password'], array_keys($this->crudPanel->getOperationSetting('validationRules')));

        $this->crudPanel->disableValidation();
        $this->assertEquals([], $this->crudPanel->getOperationSetting('validationRules'));
        $this->assertEquals([], $this->crudPanel->getOperationSetting('validationMessages'));
        $this->assertEquals([], $this->crudPanel->getOperationSetting('requiredFields'));
        $this->assertEquals(false, $this->crudPanel->getFormRequest());
    }

    public function testItCanGetTheRequiredFields()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');
        $this->assertFalse($this->crudPanel->isRequired('test'));
        $this->crudPanel->setValidation([
            'email' => 'required',
            'password.*.test' => 'required',
            'not_required' => 'present',
        ]);

        $this->crudPanel->setValidation(UserRequest::class);
        $this->assertEquals(['email', 'password[test]', 'name'], array_values($this->crudPanel->getOperationSetting('requiredFields')));
        $this->assertTrue($this->crudPanel->isRequired('email'));
        $this->assertTrue($this->crudPanel->isRequired('password.test'));
        $this->assertTrue($this->crudPanel->isRequired('name'));
    }

    public function testItCanGetTheRequiredFieldsFromCustomRules()
    {
        $this->crudPanel->setModel(User::class);

        $this->crudPanel->setValidation([
            'email' => ValidUpload::field('required'),
            'password' => ValidUploadMultiple::field('required'),
            'not_required' => ValidUpload::field('present'),
        ]);

        $this->assertEquals(['email', 'password'], array_values($this->crudPanel->getOperationSetting('requiredFields')));
        $this->assertTrue($this->crudPanel->isRequired('email'));
        $this->assertTrue($this->crudPanel->isRequired('password'));
    }

    public function testItCanGetTheRequiredFieldsFromRulesArray()
    {
        $this->crudPanel->setModel(User::class);

        $this->crudPanel->setValidation([
            'email' => ['required', 'string'],
            'password' => ['string', 'required'],
            'password_confirm' => ['same:password', ValidUploadMultiple::field('required')],
            'not_required' => ['string', 'present', 'foobar'],
        ]);

        $this->assertEquals(['email', 'password', 'password_confirm'], array_values($this->crudPanel->getOperationSetting('requiredFields')));
        $this->assertTrue($this->crudPanel->isRequired('email'));
        $this->assertTrue($this->crudPanel->isRequired('password'));
        $this->assertTrue($this->crudPanel->isRequired('password_confirm'));
    }

    public function testItCanValidateCustomRules()
    {
        $this->crudPanel->setModel(User::class);

        $pdf1 = UploadedFile::fake()->create('test1.pdf', 1000);
        $pdf2 = UploadedFile::fake()->create('test2.pdf', 1000);

        $request = request()->create('users/', 'POST', [
            'email' => $pdf1,
            'password' => [$pdf1, $pdf2],
            'name' => 'test',
        ]);

        $request->setRouteResolver(function () use ($request) {
            return (new Route('POST', 'users', ['Backpack\CRUD\Tests\Config\Http\Controllers\UserCrudController', 'create']))->bind($request);
        });

        $this->crudPanel->addFields([
            [
                'name' => 'password',
                'validationRules' => ValidUploadMultiple::field('required')->file('file|mimes:pdf|max:500'),
            ],
            [
                'name' => 'email',
                'validationRules' => ValidUpload::field('required')->file('file|mimes:jpg'),
            ],
        ]);

        $this->crudPanel->setRequest($request);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        try {
            $this->crudPanel->validateRequest();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertEquals([
                'password' => ['The password field must not be greater than 500 kilobytes.'],
                'email' => ['The email field must be a file of type: jpg.'],
            ], $e->errors());
            throw $e;
        }
    }
}
