<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ClientApiRequest extends FormRequest
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
            'messages' => $errors,
            'errorCode' => 1000
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function formatErrors(Validator $validator)
    {
        return $validator->getMessageBag()->all();
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, $this->response(
            $this->formatErrors($validator)
        ));
    }
}
