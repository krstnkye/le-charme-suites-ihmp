<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_home_page_redirects_to_sign_in(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
