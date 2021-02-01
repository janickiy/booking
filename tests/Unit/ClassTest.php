<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\Validator;

class ClassTest extends TestCase
{
    /**
     * проверка общегражданский паспорта
     * @return void
     */
    public function testValidateRussianpassport()
    {
        $rules = [
            'field1' => 'required|russianpassport',
        ];

        $data = [
            'field1' => 9999999999,
        ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());
    }

    /**
     *  роверка Общегражданский заграничный паспорт
     */
    public function testValidateRussianforeignpassport()
    {
        $rules = [
            'field1' => 'required|russianforeignpassport',
        ];

        $data = [
            'field1' => 999999999,
        ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());
    }

    /**
     * проверка национальный паспорт
     */
    public function testValidateRoreignpassport()
    {
        $rules = [
            'field1' => 'required|foreignpassport',
        ];

        $data = [
            'field1' => 5646546546546546,
        ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());
    }

    /**
     * проверка Свидетельство о рождении
     */
    public function testValidatebirthcertificate()
    {
        $rules = [
            'field1' => 'required|birthcertificate',
        ];

        $data = [
            'field1' => 'III-АМ 234567',
        ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());
    }

    /**
     * проверка военный билет военнослужащего срочной службы
     */
    public function testValidatemilitarycard()
    {
        $rules = [
            'field1' => 'required|militarycard',
        ];

        $data = [
            'field1' => 'АМ9999999',
        ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());
    }

    /**
     *  поверка удостоверение личности для военнослужащих
     */
    public function testValidatemilitaryofficercard()
    {
        $rules = [
            'field1' => 'required|militaryofficercard',
        ];

        $data = [
            'field1' => 'АМ9999999',
        ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());
    }

    /**
     * проверка Свидетельство на возвращение
     */
    public function testValidatetociscertificate()
    {
        $rules = [
            'field1' => 'required|returntociscertificate',
        ];

        $data = [
            'field1' => 9999999,
        ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());
    }

    /**
     * проверка дипломатический паспорт
     */
    public function testValidatediplomaticpassport()
    {
        $rules = [
            'field1' => 'required|diplomaticpassport',
        ];

        $data = [
            'field1' => 123456789,
        ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());
    }

    /**
     * проверка Служебный паспорт
     */
    public function testValidateservicepassport()
    {
        $rules = [
            'field1' => 'required|servicepassport',
        ];

        $data = [
            'field1' => 123456789,
        ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());
    }

    /**
     * проверка паспорт моряка
     */
    public function testValidatesailorpassport()
    {
        $rules = [
            'field1' => 'required|sailorpassport',
        ];

        $data = [
            'field1' => 'III9999999',
        ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());
    }

    /**
     * проверка удостоверение личности лица без гражданства
     */
    public function testValidatestatelesspersonidentitycard()
    {
        $rules = [
            'field1' => 'required|statelesspersonidentitycard',
        ];

        $data = [
            'field1' => 54665788679780898,
        ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());
    }

    /**
     * проверка паспорт ссср
     */
    public function testValidateussrpassport()
    {
        $rules = [
            'field1' => 'required|ussrpassport',
        ];

        $data = [
            'field1' => 'IАБ999999',
        ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());
    }

}
