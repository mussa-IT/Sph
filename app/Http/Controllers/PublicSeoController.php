<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class PublicSeoController extends Controller
{
    public function sitemap(): Response
    {
        $entries = seo_sitemap_entries();
        $xml = view('seo.sitemap', compact('entries'))->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /dashboard',
            'Disallow: /settings',
            'Sitemap: '.route('seo.sitemap'),
        ]);

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
