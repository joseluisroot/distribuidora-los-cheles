<?php

use CodeIgniter\Test\CIUnitTestCase;

final class SeoMetadataTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'seo']);
    }

    private function tags(array $seo = []): DOMXPath
    {
        $html = view('partials/seo', ['title' => 'Los Cheles', 'seo' => $seo], ['saveData' => false]);
        $document = new DOMDocument();
        $document->loadHTML('<?xml encoding="UTF-8"><html><head>' . $html . '</head><body></body></html>');

        return new DOMXPath($document);
    }

    private function product(): array
    {
        return ['nombre' => 'Cuchara "Especial"', 'slug' => 'cuchara-especial', 'sku' => 'C-01',
            'descripcion' => '<p>Acero &amp; calidad.</p>', 'imagen_url' => 'https://example.com/fallback.jpg'];
    }

    public function testProductUsesFullPrimaryImageAndCanonicalSlug(): void
    {
        $tags = $this->tags(seo_product($this->product(), [
            'path' => 'uploads/products/1/foto.jpg', 'thumb_path' => 'uploads/products/1/thumb.jpg',
            'alt' => 'Cuchara de acero',
        ]));

        $this->assertSame(base_url('uploads/products/1/foto.jpg'), $tags->evaluate('string(//meta[@property="og:image"]/@content)'));
        $this->assertSame(site_url('catalogo/cuchara-especial'), $tags->evaluate('string(//link[@rel="canonical"]/@href)'));
        $this->assertSame(site_url('catalogo/cuchara-especial'), $tags->evaluate('string(//meta[@property="og:url"]/@content)'));
        $this->assertSame('Acero & calidad.', $tags->evaluate('string(//meta[@name="description"]/@content)'));
        $this->assertSame('summary_large_image', $tags->evaluate('string(//meta[@name="twitter:card"]/@content)'));
    }

    public function testProductFallsBackToExternalImageWithoutPrefixingDomain(): void
    {
        $tags = $this->tags(seo_product($this->product()));
        $this->assertSame('https://example.com/fallback.jpg', $tags->evaluate('string(//meta[@property="og:image"]/@content)'));
    }

    public function testMissingImageAndDescriptionHaveDefaults(): void
    {
        $product = $this->product();
        $product['imagen_url'] = null;
        $product['descripcion'] = '';
        $tags = $this->tags(seo_product($product));
        $this->assertSame(base_url('assets/Logo_LosCheles.PNG'), $tags->evaluate('string(//meta[@property="og:image"]/@content)'));
        $this->assertStringContainsString('Cuchara', $tags->evaluate('string(//meta[@property="og:description"]/@content)'));
        $this->assertFileExists(FCPATH . 'assets/Logo_LosCheles.PNG');
    }

    public function testMetadataCannotInjectHtmlAttributes(): void
    {
        $tags = $this->tags(['public' => true, 'title' => 'Oferta " onclick="alert(1)',
            'description' => '<b>Oferta</b> &amp; calidad', 'image' => 'javascript:alert(1)']);
        $this->assertSame(0, $tags->query('//*[@onclick]')->length);
        $this->assertSame('Oferta " onclick="alert(1)', $tags->evaluate('string(//meta[@property="og:title"]/@content)'));
        $this->assertSame('Oferta & calidad', $tags->evaluate('string(//meta[@property="og:description"]/@content)'));
        $this->assertSame(base_url('assets/Logo_LosCheles.PNG'), $tags->evaluate('string(//meta[@property="og:image"]/@content)'));
    }

    public function testPrivatePagesDoNotEmitSharingMetadata(): void
    {
        $tags = $this->tags();
        $this->assertSame('noindex, nofollow', $tags->evaluate('string(//meta[@name="robots"]/@content)'));
        $this->assertSame(0, $tags->query('//meta[starts-with(@property,"og:")] | //link[@rel="canonical"]')->length);
    }

    public function testPublicSearchCanBeExcludedFromIndex(): void
    {
        $tags = $this->tags(['public' => true, 'index' => false]);
        $this->assertSame('noindex, nofollow', $tags->evaluate('string(//meta[@name="robots"]/@content)'));
    }

    public function testLayoutRendersMetadataInsideHead(): void
    {
        $html = view('auth/login', ['title' => 'Ingresar', 'seo' => [],
            'showNavbar' => false, 'showFooter' => false], ['saveData' => false]);
        $head = substr($html, strpos($html, '<head>'), strpos($html, '</head>') - strpos($html, '<head>'));
        $this->assertSame(1, substr_count($html, '<title>'));
        $this->assertStringContainsString('name="robots" content="noindex, nofollow"', html_entity_decode($head, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $this->assertStringNotContainsString('property="og:image"', $html);
    }
}
