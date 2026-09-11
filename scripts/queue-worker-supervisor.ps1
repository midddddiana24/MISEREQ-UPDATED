# Queue Worker Supervisor for Windows
# This script runs Laravel queue workers with automatic restart on failure
# Usage: powershell -ExecutionPolicy Bypass -File scripts\queue-worker-supervisor.ps1

param(
    [int]$WorkerCount = 1,
    [int]$RestartDelaySeconds = 5,
    [string]$BasePath = "c:\xampp_new\htdocs\mis-service-request\mis-service-request",
    [string]$PhpPath = "c:\xampp_new\php\php.exe",
    [string]$LogPath = "$BasePath\storage\logs\queue-worker.log"
)

Set-Location $BasePath

# Ensure log directory exists
$LogDir = Split-Path -Parent $LogPath
if (-not (Test-Path $LogDir)) {
    New-Item -ItemType Directory -Path $LogDir -Force | Out-Null
}

function Write-LogMessage {
    param([string]$Message)
    $Timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $LogEntry = "[$Timestamp] $Message"
    Write-Host $LogEntry
    Add-Content -Path $LogPath -Value $LogEntry
}

function Start-QueueWorker {
    param([int]$WorkerId)
    
    Write-LogMessage "Starting queue worker #${WorkerId}..."
    
    $Process = New-Object System.Diagnostics.ProcessStartInfo
    $Process.FileName = $PhpPath
    $Process.Arguments = "artisan queue:work database --sleep=3 --tries=3 --timeout=90 --max-time=3600"
    $Process.WorkingDirectory = $BasePath
    $Process.UseShellExecute = $false
    $Process.RedirectStandardOutput = $true
    $Process.RedirectStandardError = $true
    $Process.CreateNoWindow = $true
    
    $Worker = [System.Diagnostics.Process]::Start($Process)
    Write-LogMessage "Queue worker #${WorkerId} started with PID $($Worker.Id)"
    
    return $Worker
}

Write-LogMessage "Queue Worker Supervisor starting with $WorkerCount worker(s)"
Write-LogMessage "Configuration: BasePath=$BasePath, RestartDelay=${RestartDelaySeconds}s"

$Workers = @()

# Start initial workers
for ($i = 1; $i -le $WorkerCount; $i++) {
    $Worker = Start-QueueWorker -WorkerId $i
    $Workers += @{
        Id      = $i
        Process = $Worker
    }
    Start-Sleep -Seconds 2
}

# Supervisor loop: Monitor workers and restart if they die
while ($true) {
    for ($i = 0; $i -lt $Workers.Count; $i++) {
        $Worker = $Workers[$i]
        
        if ($Worker.Process.HasExited) {
            Write-LogMessage "Worker #$($Worker.Id) exited with code $($Worker.Process.ExitCode)"
            Write-LogMessage "Restarting worker #$($Worker.Id) in $RestartDelaySeconds seconds..."
            Start-Sleep -Seconds $RestartDelaySeconds
            
            $NewWorker = Start-QueueWorker -WorkerId $Worker.Id
            $Workers[$i].Process = $NewWorker
        }
    }
    
    Start-Sleep -Seconds 10
}
