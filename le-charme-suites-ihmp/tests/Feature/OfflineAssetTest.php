<?php

namespace Tests\Feature;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

class OfflineAssetTest extends TestCase
{
    public function test_required_frontend_assets_are_stored_locally(): void
    {
        $this->assertFileExists(public_path('vendor/tailwindcss-3.4.17.js'));
        $this->assertFileExists(public_path('vendor/fonts/google-fonts.css'));
        $this->assertFileExists(public_path('vendor/fonts/material-symbols.css'));
    }

    public function test_blade_views_do_not_depend_on_frontend_cdns(): void
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views')));
        $blockedHosts = ['cdn.tailwindcss.com', 'fonts.googleapis.com', 'fonts.gstatic.com'];

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo || ! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            foreach ($blockedHosts as $host) {
                $this->assertStringNotContainsString($host, $contents, "{$file->getPathname()} still references {$host}.");
            }
        }
    }
}
