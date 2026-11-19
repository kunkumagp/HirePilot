param(
    [Parameter(Position = 0)]
    [ValidateSet('start', 'stop', 'status')]
    [string]$Action = 'start'
)

$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$runtime = Join-Path $root '.runtime'
$backendPidFile = Join-Path $runtime 'backend.pid'
$frontendPidFile = Join-Path $runtime 'frontend.pid'

function Get-RecordedPid {
    param([string]$File)

    if (-not (Test-Path $File)) {
        return $null
    }

    $value = (Get-Content $File -Raw).Trim()

    if (-not $value) {
        return $null
    }

    return $value
}

function Test-LivePidFile {
    param([string]$File)

    $id = Get-RecordedPid $File

    if ($null -eq $id) {
        return $false
    }

    return [bool](Get-Process -Id $id -ErrorAction SilentlyContinue)
}

function Remove-StalePidFile {
    param([string]$File, [string]$Label)

    if ((Test-Path $File) -and -not (Test-LivePidFile $File)) {
        Write-Host "$Label PID file was stale; removing it."
        Remove-Item $File -Force
    }
}

function Get-PortOwner {
    param([int]$Port)

    $connection = Get-NetTCPConnection -State Listen -LocalPort $Port -ErrorAction SilentlyContinue |
        Select-Object -First 1

    if ($null -eq $connection) {
        return $null
    }

    return $connection.OwningProcess
}

function Stop-TrackedProcess {
    param([string]$File, [string]$Label)

    $id = Get-RecordedPid $File

    if ($null -eq $id) {
        Write-Host "$Label was not running."
        return
    }

    if (Get-Process -Id $id -ErrorAction SilentlyContinue) {
        & taskkill /PID $id /T /F | Out-Null
        Write-Host "Stopped $Label (PID $id)."
    }
    else {
        Write-Host "$Label was not running."
    }

    Remove-Item $File -Force -ErrorAction SilentlyContinue
}

switch ($Action) {
    'start' {
        if (-not (Test-Path $runtime)) {
            New-Item -ItemType Directory -Path $runtime | Out-Null
        }

        Remove-StalePidFile $backendPidFile 'Backend'
        Remove-StalePidFile $frontendPidFile 'Frontend'

        if (Test-LivePidFile $backendPidFile) {
            Write-Host 'HirePilot backend is already running. Run stop first.'
            exit 1
        }

        if (Test-LivePidFile $frontendPidFile) {
            Write-Host 'HirePilot frontend is already running. Run stop first.'
            exit 1
        }

        $backendOwner = Get-PortOwner 8000

        if ($null -ne $backendOwner) {
            Write-Host "Port 8000 is already in use by PID $backendOwner. Run stop.cmd first."
            exit 1
        }

        $frontendOwner = Get-PortOwner 5173

        if ($null -ne $frontendOwner) {
            Write-Host "Port 5173 is already in use by PID $frontendOwner. Run stop.cmd first."
            exit 1
        }

        $backend = Start-Process `
            -FilePath 'cmd.exe' `
            -ArgumentList '/c', 'php artisan serve --host=127.0.0.1 --port=8000' `
            -WorkingDirectory (Join-Path $root 'backend') `
            -PassThru

        Set-Content -Path $backendPidFile -Value $backend.Id
        Write-Host "Backend started  -> http://127.0.0.1:8000  (PID $($backend.Id))"

        $frontend = Start-Process `
            -FilePath 'cmd.exe' `
            -ArgumentList '/c', 'npm.cmd run dev' `
            -WorkingDirectory (Join-Path $root 'frontend') `
            -PassThru

        Set-Content -Path $frontendPidFile -Value $frontend.Id
        Write-Host "Frontend started -> http://localhost:5173  (PID $($frontend.Id))"
    }

    'stop' {
        Stop-TrackedProcess $backendPidFile 'Backend'
        Stop-TrackedProcess $frontendPidFile 'Frontend'

        # Catch orphaned children (for example the PHP dev server or Vite)
        # whose parent shell was closed but which still hold the ports.
        foreach ($port in 8000, 5173) {
            $owner = Get-PortOwner $port

            if ($null -ne $owner) {
                & taskkill /PID $owner /T /F | Out-Null
                Write-Host "Stopped leftover process on port $port (PID $owner)."
            }
        }

        Write-Host 'HirePilot stopped.'
    }

    'status' {
        $backendOwner = Get-PortOwner 8000

        if (Test-LivePidFile $backendPidFile) {
            Write-Host "Backend  RUNNING  http://127.0.0.1:8000  (PID $(Get-RecordedPid $backendPidFile))"
        }
        elseif ($null -ne $backendOwner) {
            Write-Host "Backend  PORT BUSY by PID $backendOwner - run stop.cmd"
        }
        else {
            Write-Host 'Backend  STOPPED'
        }

        $frontendOwner = Get-PortOwner 5173

        if (Test-LivePidFile $frontendPidFile) {
            Write-Host "Frontend RUNNING  http://localhost:5173  (PID $(Get-RecordedPid $frontendPidFile))"
        }
        elseif ($null -ne $frontendOwner) {
            Write-Host "Frontend PORT BUSY by PID $frontendOwner - run stop.cmd"
        }
        else {
            Write-Host 'Frontend STOPPED'
        }
    }
}
