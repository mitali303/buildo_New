<?php

namespace Tests\Feature;

use Tests\TestCase;

class MaterialRequestTest extends TestCase
{
    public function test_material_request_page_loads(): void
    {
        $this->actingAs(
            \App\Models\User::factory()->create(),
            'web'
        );

        $response = $this->get(route('material_request.list'));

        $response->assertOk();
    }
}
