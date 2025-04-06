<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    /**
     * 测试前台首页是否能正常访问
     */
    public function test_the_homepage_returns_a_successful_response(): void
    {
        if (! installed()) {
            $response = $this->get('/install');
            $response->assertStatus(200);
        } else {
            $response = $this->get('/');
            $response->assertStatus(200);
        }
    }
}
