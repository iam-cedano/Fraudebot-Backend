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
        $response->assertSee('Fraudebot API', false);
        $response->assertSee('Fraudebot API Reference', false);
        $response->assertSee('Scalar.createApiReference', false);
        $response->assertSee('#\/components\/schemas\/Scammer', false);
        $response->assertSee('#\/components\/schemas\/Organization', false);
        $response->assertSee('#\/components\/schemas\/PaginatedContacts', false);
        $response->assertSee('#\/components\/schemas\/MonthlyReportCalendar', false);
        $response->assertSee('listScammerContacts', false);
        $response->assertSee('\\"Contact\\":', false);
    }
}
