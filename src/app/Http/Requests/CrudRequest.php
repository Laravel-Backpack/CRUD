<?php

namespace Backpack\CRUD\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @deprecated [3.4] Since authorization is already verified using a middleware, this special form request file is no longer needed. Please make sure all your form requests extend the original Illuminate\Foundation\Http\FormRequest instead. This file might be removed entirely as soon as CRUD version 3.4.
 */
class CrudRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow creates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'name' => 'required|min:3|max:255'
        ];
    }

    // OPTIONAL OVERRIDE
    // public function forbiddenResponse()
    // {
    // Optionally, send a custom response on authorize failure
    // (default is to just redirect to initial page with errors)
        //
    // Can return a response, a view, a redirect, or whatever else
    // return Response::make('Permission denied foo!', 403);
    // }

    // OPTIONAL OVERRIDE
    // public function response()
    // {
    // If you want to customize what happens on a failed validation,
    // override this method.
    // See what it does natively here:
    // https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Http/FormRequest.php
    // }
}
