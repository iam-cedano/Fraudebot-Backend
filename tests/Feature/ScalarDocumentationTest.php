<?php

namespace Tests\Feature;

use Tests\TestCase;

class ScalarDocumentationTest extends TestCase
{
    public function test_scalar_page_embeds_the_openapi_contract(): void
    {
        $response = $this->get('/scalar');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertSee('Fraudebot API Description', false);
        $response->assertSee('Fraudebot API Reference', false);
        $response->assertSee('Scalar.createApiReference', false);
        $response->assertSee('#\/components\/schemas\/Scammer', false);
        $response->assertSee('#\/components\/schemas\/Organization', false);
        $response->assertSee('#\/components\/schemas\/ScammerCalendar', false);
        $response->assertSee('#\/components\/schemas\/OrganizationCalendar', false);
        $response->assertSee('#\/components\/schemas\/ScammerContacts', false);
        $response->assertSee('#\/components\/schemas\/OrganizationContacts', false);
    }
}
