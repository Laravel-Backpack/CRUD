<?php

namespace Backpack\CRUD\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Restrict the fields that the user can change.
     *
     * @return array
     */
    public function validationData()
    {
        return $this->only(backpack_authentication_column(), 'name', 'current_password');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = backpack_auth()->user();
        $authCol = backpack_authentication_column();

        $rules = [
            $authCol => [
                'required',
                $authCol == 'email' ? 'email' : '',
                Rule::unique($user->getConnectionName().'.'.$user->getTable())
                    ->ignore($user->getKey(), $user->getKeyName()),
            ],
            'name' => 'required',
        ];

        if ($this->input($authCol) !== $user->{$authCol}) {
            $rules['current_password'] = 'required';
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $user = backpack_auth()->user();
        $authCol = backpack_authentication_column();

        $validator->after(function ($validator) use ($user, $authCol) {
            if ($this->input($authCol) !== $user->{$authCol}
                && ! Hash::check($this->input('current_password'), $user->password)) {
                $validator->errors()->add('current_password', trans('backpack::base.old_password_incorrect'));
            }
        });
    }
}
