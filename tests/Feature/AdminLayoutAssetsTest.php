<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminLayoutAssetsTest extends TestCase
{
    public function test_admin_head_references_existing_local_assets(): void
    {
        $head = file_get_contents(resource_path('views/admin/partials/head.blade.php'));

        preg_match_all("/asset\('([^']+)'\)/", $head, $matches);

        $this->assertNotEmpty($matches[1]);

        foreach ($matches[1] as $assetPath) {
            $this->assertFileExists(
                public_path($assetPath),
                "Admin head references missing asset: {$assetPath}"
            );
        }
    }
}
