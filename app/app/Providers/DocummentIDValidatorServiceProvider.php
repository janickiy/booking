<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Добавление произвольного правила валидации Validator::extend() через ServiceProvider
 * Class DocummentIDValidatorServiceProvider
 * @package App\Providers
 */
class DocummentIDValidatorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app['validator']->extend('russianpassport', function ($attribute, $value, $parameters) {

            /**
             * Общегражданский паспорта (формат: 99 99 999999 или 9999999999)
             * @property string $parameters
             * @property string $value
             * @property string $attribute
             * @return bool
             */
            if (!preg_match("/^\d{2}\s*\d{2}\s*\d{6}$/", $value)) {
                return false;
            }

            return true;
        });

        $this->app['validator']->extend('russianforeignpassport', function ($attribute, $value, $parameters) {

            /**
             * Общегражданский заграничный паспорт (формат: 99 9999999 или 999999999)
             * @property string $parameters
             * @property string $value
             * @property string $attribute
             * @return bool
             */
            if (!preg_match("/^\d{2}\s*\d{7}$/", $value)) {
                return false;
            }

            return true;
        });

        $this->app['validator']->extend('foreignpassport', function ($attribute, $value, $parameters) {

            /**
             * Национальный паспорт
             * @property string $parameters
             * @property string $value
             * @property string $attribute
             * @return bool
             */
            if (!preg_match("/^\w{6,20}$/", $value)) {
                return false;
            }

            return true;
        });

        $this->app['validator']->extend('birthcertificate', function ($attribute, $value, $parameters) {

            /**
             * Свидетельство о рождении (формат: III-АМ 234567 или IIIАМ234567)
             * @property string $parameters
             * @property string $value
             * @property string $attribute
             * @return bool
             */
            if (!preg_match("/^M{0,3}(D?C{0,3}|C[DM])(L?X{0,3}|X[LC])(V?I{0,3}|I[VX])((?<!^)-)?[а-яА-Я]{2}\s*\d{6}$/u", $value)) {
                return false;
            }

            return true;
        });

        $this->app['validator']->extend('militarycard', function ($attribute, $value, $parameters) {

            /**
             * Военный билет военнослужащего срочной службы (формат: АМ АМ 9999999 или АМ9999999)
             * @property string $parameters
             * @property string $value
             * @property string $attribute
             * @return bool
             */
            if (!preg_match("/^[а-я]{2}\s*\d{7}$/iu", $value)) {
                return false;
            }

            return true;
        });

        $this->app['validator']->extend('militaryofficercard', function ($attribute, $value, $parameters) {
            /**
             * Удостоверение личности для военнослужащих (формат: АМ 9999999 или АМ9999999)
             * @property string $parameters
             * @property string $value
             * @property string $attribute
             * @return bool
             */
            if (!preg_match("/^[а-я]{2}\s*\d{7}$/iu", $value)) {
                return false;
            }

            return true;
        });

        $this->app['validator']->extend('returntociscertificate', function ($attribute, $value, $parameters) {

            /**
             * Свидетельство на возвращение (формат: 9999999)
             * @property string $parameters
             * @property string $value
             * @property string $attribute
             * @return bool
             */

            if (!preg_match("/^\d{7}$/", $value)) {
                return false;
            }

            return true;
        });

        $this->app['validator']->extend('diplomaticpassport', function ($attribute, $value, $parameters) {

            /**
             * Дипломатический паспорт
             * @property string $parameters
             * @property string $value
             * @property string $attribute
             * @return bool
             */
            if (!preg_match("/^\d{9}$/", $value)) {
                return false;
            }

            return true;
        });

        $this->app['validator']->extend('servicepassport', function ($attribute, $value, $parameters) {

            /**
             * Служебный паспорт
             * @property string $parameters
             * @property string $value
             * @property string $attribute
             * @return bool
             */
            if (!preg_match("/^\d{9}$/", $value)) {
                return false;
            }

            return true;
        });

        $this->app['validator']->extend('sailorpassport', function ($attribute, $value, $parameters) {

            /**
             * Паспорт моряка (формат: III 9999999 или III9999999)
             * @property string $parameters
             * @property string $value
             * @property string $attribute
             * @return bool
             */
            if (!preg_match("/^M{0,3}(D?C{0,3}|C[DM])(L?X{0,3}|X[LC])(V?I{0,3}|I[VX])\s*\d{7}$/", $value)) {
                return false;
            }

            return true;
        });

        $this->app['validator']->extend('statelesspersonidentitycard', function ($attribute, $value, $parameters) {

            /**
             * Удостоверение личности лица без гражданства
             * @property string $parameters
             * @property string $value
             * @property string $attribute
             * @return bool
             */
            if (!preg_match("/^\w{6,20}$/", $value)) {
                return false;
            }

            return true;
        });

        $this->app['validator']->extend('residencepermit', function ($attribute, $value, $parameters) {

            /**
             * Вид на жительство (формат: 9999999)
             * @property string $parameters
             * @property string $value
             * @property string $attribute
             * @return bool
             */
            if (!preg_match("/^\d{7}$/", $value)) {
                return false;
            }

            return true;
        });

        $this->app['validator']->extend('ussrpassport', function ($attribute, $value, $parameters) {

            /**
             * Паспорт СССР (формат: I-АБ 999999 или IАБ999999)
             * @property string $parameters
             * @property string $value
             * @property string $attribute
             * @return bool
             */
            if (!preg_match("/^M{0,3}(D?C{0,3}|C[DM])(L?X{0,3}|X[LC])(V?I{0,3}|I[VX])((?<!^)-)?[а-яА-Я]{2}\s*[0-9]{6}$/u", $value)) {
                return false;
            }

            return true;
        });
    }

    public function register()
    {
        //
    }
}