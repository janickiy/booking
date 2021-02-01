<?php

namespace App\Http\Requests\AdminApi;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

class Request extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }

    public function response(array $errors)
    {
        return new JsonResponse([
            'success' => false,
            'errors' => $errors,
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function formatErrors(Validator $validator)
    {
        return $validator->getMessageBag()->all();
    }
}
