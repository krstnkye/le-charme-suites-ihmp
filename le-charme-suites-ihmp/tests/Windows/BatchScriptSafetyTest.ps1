$ErrorActionPreference = 'Stop'

$repositoryRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..\..')).Path
$temporaryRoot = Join-Path ([System.IO.Path]::GetTempPath()) ('ihmp-batch2d-' + [guid]::NewGuid().ToString('N'))
$testLog = Join-Path $temporaryRoot 'commands.log'

function Assert-True {
    param(
        [Parameter(Mandatory)]
        [bool] $Condition,

        [Parameter(Mandatory)]
        [string] $Message
    )

    if (-not $Condition) {
        throw $Message
    }
}

function Invoke-BatchFile {
    param(
        [Parameter(Mandatory)]
        [string] $Path,

        [string] $Arguments = '',

        [string] $InputText
    )

    $quotedPath = '"' + $Path + '"'
    $command = if ($PSBoundParameters.ContainsKey('InputText')) {
        $inputFile = Join-Path $temporaryRoot ('stdin-' + [guid]::NewGuid().ToString('N') + '.txt')
        [System.IO.File]::WriteAllText($inputFile, $InputText + [Environment]::NewLine, [System.Text.Encoding]::ASCII)
        $quotedPath + ' ' + $Arguments + ' < "' + $inputFile + '"'
    } else {
        $quotedPath + ' ' + $Arguments + ' ^<nul'
    }

    $output = & $env:ComSpec /d /c $command 2>&1

    return [pscustomobject]@{
        ExitCode = $LASTEXITCODE
        Output = ($output -join [Environment]::NewLine)
    }
}

function Reset-TestLog {
    Set-Content -LiteralPath $testLog -Value ''
}

function Read-TestLog {
    return Get-Content -LiteralPath $testLog -Raw
}

try {
    $binDirectory = New-Item -ItemType Directory -Path (Join-Path $temporaryRoot 'bin') -Force
    $applicationDirectory = New-Item -ItemType Directory -Path (Join-Path $temporaryRoot 'le-charme-suites-ihmp') -Force
    New-Item -ItemType Directory -Path (Join-Path $applicationDirectory 'database') -Force | Out-Null

    Copy-Item (Join-Path $repositoryRoot 'update-and-run.bat') $temporaryRoot
    Copy-Item (Join-Path $repositoryRoot 'setup-and-run.bat') $temporaryRoot
    Set-Content -LiteralPath (Join-Path $applicationDirectory '.env') -Value @'
APP_ENV=local
APP_KEY=base64:synthetic-test-key
APP_DEBUG=false
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
'@

    $fakeCommandSource = @'
using System;
using System.Diagnostics;
using System.IO;

public static class FakeCommand
{
    public static int Main(string[] args)
    {
        var executable = Path.GetFileNameWithoutExtension(Process.GetCurrentProcess().MainModule.FileName).ToLowerInvariant();
        var arguments = string.Join(" ", args);
        File.AppendAllText(Environment.GetEnvironmentVariable("TEST_LOG"), executable + " " + arguments + Environment.NewLine);

        if (executable == "git" && args.Length > 0 && args[0].Equals("rev-parse", StringComparison.OrdinalIgnoreCase))
        {
            var branch = Environment.GetEnvironmentVariable("FAKE_GIT_BRANCH");
            Console.WriteLine(branch == "DETACHED" ? "HEAD" : branch);
        }

        if (executable == "php" && arguments.Contains("bootstrap/app.php"))
        {
            var mode = Environment.GetEnvironmentVariable("FAKE_DB_MODE");
            if (string.Equals(mode, "SQLITE", StringComparison.OrdinalIgnoreCase)) return 0;
            if (string.Equals(mode, "SHARED", StringComparison.OrdinalIgnoreCase)) return 2;
            return 3;
        }

        return 0;
    }
}
'@

    $fakeCommand = Join-Path $binDirectory 'fake-command.exe'
    Add-Type -TypeDefinition $fakeCommandSource -OutputAssembly $fakeCommand -OutputType ConsoleApplication
    Copy-Item $fakeCommand (Join-Path $binDirectory 'git.exe')
    Copy-Item $fakeCommand (Join-Path $binDirectory 'composer.exe')
    Copy-Item $fakeCommand (Join-Path $binDirectory 'php.exe')

    $originalPath = $env:PATH
    $env:PATH = $binDirectory.FullName + ';' + (Join-Path $env:SystemRoot 'System32')
    $env:TEST_LOG = $testLog

    Reset-TestLog
    $env:FAKE_GIT_BRANCH = 'feature/compliance-checklist'
    $env:FAKE_DB_MODE = 'SQLITE'
    $updateResult = Invoke-BatchFile -Path (Join-Path $temporaryRoot 'update-and-run.bat')
    $updateLog = Read-TestLog
    Assert-True ($updateResult.ExitCode -eq 0) 'The update script should succeed on a normal branch.'
    Assert-True ($updateLog.Contains('git pull --ff-only origin feature/compliance-checklist')) 'The update script did not pull the checked-out branch with --ff-only.'
    Assert-True (-not $updateLog.Contains('origin main')) 'The update script still pulls main.'

    Reset-TestLog
    $env:FAKE_GIT_BRANCH = 'DETACHED'
    $detachedResult = Invoke-BatchFile -Path (Join-Path $temporaryRoot 'update-and-run.bat')
    $detachedLog = Read-TestLog
    Assert-True ($detachedResult.ExitCode -ne 0) 'The update script should refuse a detached HEAD.'
    Assert-True (-not $detachedLog.Contains('git pull')) 'The update script attempted a pull from detached HEAD.'

    Reset-TestLog
    $env:FAKE_DB_MODE = 'SQLITE'
    $sqliteResult = Invoke-BatchFile -Path (Join-Path $temporaryRoot 'setup-and-run.bat') -Arguments '--no-serve'
    $sqliteLog = Read-TestLog
    Assert-True ($sqliteResult.ExitCode -eq 0) 'Safe local SQLite setup should succeed.'
    Assert-True ($sqliteLog.Contains('php artisan migrate --seed --force')) 'Safe local SQLite setup did not migrate and seed.'

    Reset-TestLog
    $env:FAKE_DB_MODE = 'SHARED'
    $sharedSkipResult = Invoke-BatchFile -Path (Join-Path $temporaryRoot 'setup-and-run.bat') -Arguments '--no-serve'
    $sharedSkipLog = Read-TestLog
    Assert-True ($sharedSkipResult.ExitCode -eq 0) 'Skipping shared database changes should keep setup usable.'
    Assert-True ($sharedSkipLog.Contains('php artisan migrate:status')) 'Shared setup did not perform the read-only migration status check.'
    Assert-True (-not $sharedSkipLog.Contains('php artisan migrate --force')) 'Shared setup migrated without typed confirmation.'
    Assert-True (-not $sharedSkipLog.Contains('--seed')) 'Shared setup attempted to run a seeder.'
    Assert-True (-not $sharedSkipLog.Contains('db:seed')) 'Shared setup attempted to seed data.'

    Reset-TestLog
    $sharedApplyResult = Invoke-BatchFile -Path (Join-Path $temporaryRoot 'setup-and-run.bat') -Arguments '--no-serve' -InputText 'APPLY MIGRATIONS'
    $sharedApplyLog = Read-TestLog
    Assert-True ($sharedApplyResult.ExitCode -eq 0) 'Confirmed shared migration should succeed.'
    Assert-True ($sharedApplyLog.Contains('php artisan migrate --force')) 'Typed confirmation did not run migrations.'
    Assert-True (-not $sharedApplyLog.Contains('--seed')) 'Confirmed shared migration must not run a seeder.'
    Assert-True (-not $sharedApplyLog.Contains('db:seed')) 'Confirmed shared migration must not seed data.'

    Reset-TestLog
    $sharedNearMissResult = Invoke-BatchFile -Path (Join-Path $temporaryRoot 'setup-and-run.bat') -Arguments '--no-serve' -InputText 'apply migrations'
    $sharedNearMissLog = Read-TestLog
    Assert-True ($sharedNearMissResult.ExitCode -eq 0) 'Declining shared database changes should keep setup usable.'
    Assert-True (-not $sharedNearMissLog.Contains('php artisan migrate --force')) 'A non-exact confirmation unexpectedly ran migrations.'
    Assert-True (-not $sharedNearMissLog.Contains('--seed')) 'A non-exact confirmation unexpectedly ran a seeder.'

    Reset-TestLog
    $env:FAKE_DB_MODE = 'AMBIGUOUS'
    $ambiguousResult = Invoke-BatchFile -Path (Join-Path $temporaryRoot 'setup-and-run.bat') -Arguments '--no-serve'
    $ambiguousLog = Read-TestLog
    Assert-True ($ambiguousResult.ExitCode -ne 0) 'Ambiguous database configuration should fail safely.'
    Assert-True (-not $ambiguousLog.Contains('artisan migrate')) 'Ambiguous database configuration attempted a database operation.'

    Write-Output 'PASS: Windows batch script safety regression checks completed.'
} finally {
    $env:PATH = $originalPath
    Remove-Item -LiteralPath $temporaryRoot -Recurse -Force -ErrorAction SilentlyContinue
}
