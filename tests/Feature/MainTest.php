<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MainTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testLoadAppMain()
    {
        // Тест загрузки главной страницы
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
