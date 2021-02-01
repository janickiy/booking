<?php

namespace App\Http\Requests\AdminApi\TrainsCar;
use App\Http\Requests\AdminApi\Request;

class TrainCarStoreUpdateRequest extends Request
{

    public function rules()
    {
        return [
            'typeRu' => 'required|max:255',
            'typeEn' => 'required|max:255',
            'typeScheme' => 'required',
            'schemes' => 'array|nullable',
            'schemes.*.key' => 'required|string|distinct',
            'schemes.*.file' => 'required_without:schemes.*.file_path|mimes:svg',
            'train_id' => 'numeric|nullable',
        ];
    }

    public function messages()
    {
        return [
            'typeRu.required' => 'Укажите тип вагона!',
            'typeEn.required' => 'Укажите тип вагона для отображения пассажиру!',
            'typeScheme.required' => 'Укажите тип схемы!',
            'train_id.required' => 'Укажите поезд!',
            'schemes.*.key.required' => 'Ключ схемы не может быть пустым',
            'schemes.*.key.string' => 'Ключ схемы жолжен быть строкой',
            'schemes.*.key.distinct' => 'Ключи схем не могут повторяться',
            'schemes.*.file.mimes' => 'Схема должна быть в формате SVG.',
            'schemes.*.file.required_without' => 'Загрузите схему!',
        ];
    }
}
