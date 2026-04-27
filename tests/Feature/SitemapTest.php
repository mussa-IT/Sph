<?php

namespace Tests\Feature;

use Tests\TestCase;

class SitemapTest extends TestCase
{
    public function test_sitemap_xml_is_available(): void
    {
        $response = $this->get(route('seo.sitemap'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false)
            ->assertSee('<urlset', false);
    }
}
