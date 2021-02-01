<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 06.03.2019
 * Time: 9:50
 */

namespace App\Models\Traits;


use App\Exceptions\NotValidJsonSchemeException;

trait ValidateJson
{
    protected static function prepareScheme(string $fieldName, array $data, $validateRequired=true)
    {
        if(!isset(static::$dataSchemes) || !isset(static::$dataSchemes[$fieldName])) return $data;
        $errors = [];

        foreach (static::$dataSchemes[$fieldName] as $dataField => $required){

            if(!is_array(json_decode(json_encode($data)))) {
                if ((!in_array($dataField,
                            array_keys($data)) || empty($data[$dataField])) && ($required && $validateRequired)) {
                    $errors[] = "Поле {$dataField} обязательно к заполнению";
                    continue;
                }

                if (!in_array($dataField, array_keys($data)) && !$required) {
                    $data[$dataField] = '';
                }

            }else{

                if(count($data)<1 && ($required && $validateRequired)) throw new NotValidJsonSchemeException(["Поле {$dataField} обязательно к заполнению"],400);

                foreach ($data as $n => $dataSet){
                    if ((!in_array($dataField,
                                array_keys($dataSet)) || empty($dataSet[$dataField])) && ($required && $validateRequired)) {
                        $errors[] = "Поле {$dataField} записи {$n} обязательно к заполнению";
                        continue;
                    }

                    if (!in_array($dataField, array_keys($dataSet)) && !$required) {
                        $data[$n][$dataField] = '';
                    }
                }
            }
        }

        if(count($errors)>0){
            throw new NotValidJsonSchemeException($errors,400);
        }

        return $data;
    }
}