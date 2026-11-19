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
        Write-Host 'HirePilot stopped.'
    }

    'status' {
        if (Test-LivePidFile $backendPidFile) {
            Write-Host "Backend  RUNNING  http://127.0.0.1:8000  (PID $(Get-RecordedPid $backendPidFile))"
        }
        else {
            Write-Host 'Backend  STOPPED'
        }

        if (Test-LivePidFile $frontendPidFile) {
            Write-Host "Frontend RUNNING  http://localhost:5173  (PID $(Get-RecordedPid $frontendPidFile))"
        }
        else {
            Write-Host 'Frontend STOPPED'
        }
    }
}
