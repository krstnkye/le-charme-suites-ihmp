$ErrorActionPreference = 'Stop'

$repositoryRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..\..')).Path
$temporaryRoot = Join-Path ([System.IO.Path]::GetTempPath()) ('ihmp-local-demo-' + [guid]::NewGuid().ToString('N'))
$testLog = Join-Path $temporaryRoot 'commands.log'

function Assert-True {
    param([bool] $Condition, [string] $Message)
    if (-not $Condition) { throw $Message }
}
function Invoke-BatchFile {
    param([string] $Path)
    $output = & $env:ComSpec /d /c ('"' + $Path + '" ^<nul') 2>&1
    [pscustomobject]@{ ExitCode = $LASTEXITCODE; Output = ($output -join [Environment]::NewLine) }
}

try {
    $binDirectory = New-Item -ItemType Directory -Path (Join-Path $temporaryRoot 'bin') -Force
    $applicationDirectory = New-Item -ItemType Directory -Path (Join-Path $temporaryRoot 'le-charme-suites-ihmp') -Force
    New-Item -ItemType Directory -Path (Join-Path $applicationDirectory 'database') -Force | Out-Null
    New-Item -ItemType Directory -Path (Join-Path $applicationDirectory 'vendor') -Force | Out-Null
    New-Item -ItemType File -Path (Join-Path $applicationDirectory 'vendor\autoload.php') -Force | Out-Null
    Copy-Item (Join-Path $repositoryRoot 'prepare-local-demo.bat') $temporaryRoot
    Copy-Item (Join-Path $repositoryRoot 'run-local-demo.bat') $temporaryRoot
    Copy-Item (Join-Path $repositoryRoot 'le-charme-suites-ihmp\.env.local-demo.example') $applicationDirectory

    $fakePhpSource = @'
using System;
using System.IO;

public static class FakePhp
{
    public static int Main(string[] args)
    {
        var arguments = string.Join(" ", args);
        File.AppendAllText(Environment.GetEnvironmentVariable("TEST_LOG"), "php " + arguments + Environment.NewLine);
        if (arguments.Contains("file_get_contents('.env.local-demo')")) return 0;
        if (arguments.Contains("demo:prepare --check-only")) {
            return string.Equals(Environment.GetEnvironmentVariable("SAFE_DEMO_DB"), "true", StringComparison.OrdinalIgnoreCase) ? 0 : 1;
        }
        return 0;
    }
}
'@
    $fakePhp = Join-Path $binDirectory 'php.exe'
    Add-Type -TypeDefinition $fakePhpSource -OutputAssembly $fakePhp -OutputType ConsoleApplication

    $originalPath = $env:PATH
    $env:PATH = $binDirectory.FullName + ';' + (Join-Path $env:SystemRoot 'System32')
    $env:TEST_LOG = $testLog

    $env:SAFE_DEMO_DB = 'false'
    [System.IO.File]::WriteAllText($testLog, '')
    $unsafe = Invoke-BatchFile (Join-Path $temporaryRoot 'prepare-local-demo.bat')
    $unsafeLog = Get-Content $testLog -Raw
    Assert-True ($unsafe.ExitCode -ne 0) 'Unsafe configuration should fail.'
    Assert-True (-not $unsafeLog.Contains('artisan --env=local-demo migrate')) 'Unsafe configuration reached migration.'
    Assert-True (-not $unsafeLog.Contains('artisan --env=local-demo demo:prepare' + [Environment]::NewLine)) 'Unsafe configuration reached demo data creation.'

    $env:SAFE_DEMO_DB = 'true'
    [System.IO.File]::WriteAllText($testLog, '')
    $safe = Invoke-BatchFile (Join-Path $temporaryRoot 'prepare-local-demo.bat')
    $safeLog = Get-Content $testLog -Raw
    Assert-True ($safe.ExitCode -eq 0) 'Safe local SQLite preparation should succeed.'
    Assert-True ($safeLog.Contains('artisan --env=local-demo migrate --force')) 'Safe preparation did not migrate its local database.'
    Assert-True ($safeLog.Contains('artisan --env=local-demo demo:prepare')) 'Safe preparation did not create demo records.'
    Assert-True (-not $safeLog.Contains('--seed')) 'Local demo setup must not use the general seeder.'

    $run = Invoke-BatchFile (Join-Path $temporaryRoot 'run-local-demo.bat')
    $runLog = Get-Content $testLog -Raw
    Assert-True ($run.ExitCode -eq 0) 'Prepared local demo should start through cmd.exe.'
    Assert-True ($runLog.Contains('artisan --env=local-demo serve --host=127.0.0.1 --port=8000')) 'Run script did not start the isolated environment.'

    Write-Output 'PASS: Local demo Windows script safety checks completed.'
} finally {
    $env:PATH = $originalPath
    Remove-Item -LiteralPath $temporaryRoot -Recurse -Force -ErrorAction SilentlyContinue
}
