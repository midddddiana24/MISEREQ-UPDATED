$ErrorActionPreference = 'Stop'

$projectPath = Split-Path -Parent $PSScriptRoot
$phpPath = 'C:\xampp\php\php.exe'
$logPath = Join-Path $projectPath 'storage\logs\scheduler.log'

if (-not (Test-Path -LiteralPath $phpPath)) {
    throw "PHP executable was not found at $phpPath"
}

Set-Location -LiteralPath $projectPath
$output = & $phpPath artisan schedule:run --no-interaction 2>&1
$exitCode = $LASTEXITCODE

$timestamp = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
Add-Content -LiteralPath $logPath -Value "[$timestamp] exit=$exitCode"
if ($output) {
    Add-Content -LiteralPath $logPath -Value ($output | Out-String).TrimEnd()
}

exit $exitCode
