<?php

namespace Tests\Feature;

use Tests\TestCase;

class BatchScriptSafetyTest extends TestCase
{
    public function test_update_script_fast_forwards_the_checked_out_branch_instead_of_main(): void
    {
        $script = $this->rootScript('update-and-run.bat');

        $this->assertStringContainsString('git rev-parse --abbrev-ref HEAD', $script);
        $this->assertStringContainsString('git pull --ff-only origin "%CURRENT_BRANCH%"', $script);
        $this->assertStringContainsString('if /I "%CURRENT_BRANCH%"=="HEAD"', $script);
        $this->assertStringNotContainsString('git pull --ff-only origin main', $script);
    }

    public function test_setup_script_separates_local_sqlite_from_shared_database_changes(): void
    {
        $script = $this->rootScript('setup-and-run.bat');

        $this->assertStringContainsString(':local_sqlite_database', $script);
        $this->assertStringContainsString(':shared_database', $script);
        $this->assertStringContainsString(':ambiguous_database', $script);
        $this->assertStringContainsString('APPLY MIGRATIONS', $script);
        $this->assertStringContainsString('php artisan migrate --seed --force', $script);
        $this->assertStringContainsString('php artisan migrate --force', $script);
        $this->assertStringContainsString('php artisan migrate:status', $script);
    }

    public function test_every_batch_goto_has_one_matching_label(): void
    {
        foreach (['update-and-run.bat', 'setup-and-run.bat', 'run-shared-lan-server.bat'] as $filename) {
            $script = $this->rootScript($filename);
            preg_match_all('/^:([a-z0-9_]+)\s*$/mi', $script, $labelMatches);
            preg_match_all('/\bgoto\s+:?([a-z0-9_]+)/i', $script, $gotoMatches);

            $labels = array_map('strtolower', $labelMatches[1]);

            $this->assertSame(
                array_values(array_unique($labels)),
                $labels,
                "{$filename} contains a duplicate control-flow label.",
            );

            foreach (array_unique(array_map('strtolower', $gotoMatches[1])) as $target) {
                if ($target === 'eof') {
                    continue;
                }

                $this->assertContains($target, $labels, "{$filename} jumps to a missing :{$target} label.");
            }
        }
    }

    private function rootScript(string $filename): string
    {
        $contents = file_get_contents(dirname(base_path()).DIRECTORY_SEPARATOR.$filename);

        $this->assertIsString($contents, "Unable to read {$filename}.");

        return $contents;
    }
}
