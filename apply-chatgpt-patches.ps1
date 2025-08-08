param(
  [string]$BranchName = "ai/patches",
  [int]$PollMs = 1200,
  [string]$PatchFile = "patch.txt"
)

function Ensure-GitRepo {
  if (-not (Test-Path ".git")) {
    Write-Host "[!] Pas de depot Git ici. Lance: git init" -ForegroundColor Yellow
    exit 1
  }
}

function Ensure-Branch {
  $current = (git rev-parse --abbrev-ref HEAD) 2>$null
  if ($LASTEXITCODE -ne 0) { Write-Host "[X] Git introuvable ?" -ForegroundColor Red; exit 1 }
  if ($current -eq $BranchName) { return }
  git rev-parse --verify $BranchName 2>$null | Out-Null
  if ($LASTEXITCODE -eq 0) {
    git switch $BranchName | Out-Null
  } else {
    git switch -c $BranchName | Out-Null
  }
}

function Extract-DiffFromText($text) {
  return $text.Trim()
}

function Apply-Diff($diffText) {
    $guid = [guid]::NewGuid().ToString()
    $tmp = "$($env:TEMP)\\chatpatch_$guid.diff"

    # Sauvegarde du diff temporaire
    Set-Content -Path $tmp -Value $diffText -Encoding UTF8

    # Création automatique des dossiers manquants détectés dans le patch
    $lines = $diffText -split "`n"
    foreach ($line in $lines) {
        if ($line -match '^\+\+\+ b/(.+)$' -or $line -match '^\-\-\- a/(.+)$' -or $line -match '^\-\-\- b/(.+)$' -or $line -match '^\+\+\+ a/(.+)$') {
            $filePath = $Matches[1]
            $dirPath = Split-Path $filePath -Parent
            if (-not [string]::IsNullOrWhiteSpace($dirPath) -and -not (Test-Path $dirPath)) {
                Write-Host "[*] Création du dossier : $dirPath"
                New-Item -ItemType Directory -Path $dirPath -Force | Out-Null
            }
        }
    }

    # Application du patch avec `patch` au lieu de `git apply`
    Write-Host "[*] Application du patch avec 'patch -p1'..."
    & patch -p1 -i $tmp
    if ($LASTEXITCODE -ne 0) {
        Write-Host "[X] Echec de l'application avec patch." -ForegroundColor Red
        Remove-Item $tmp -Force
        return $false
    }

    # Ajout et commit Git après application
    git add -A
    $msg = "Apply chat patch: " + (Get-Date).ToString("yyyy-MM-dd HH:mm:ss")
    git commit -m $msg | Out-Null

    # Ouverture des fichiers modifiés dans VS Code
    $files = git diff --name-only --diff-filter=AM HEAD~1 HEAD
    foreach ($f in $files) {
        if (Test-Path $f) { code --goto "$f:1:1" }
    }

    Remove-Item $tmp -Force
    Write-Host "[OK] Patch appliqué et commité." -ForegroundColor Green
    return $true
}

Ensure-GitRepo
Ensure-Branch

if (-not (Test-Path $PatchFile)) {
  New-Item -ItemType File -Path $PatchFile | Out-Null
  Write-Host "[*] Fichier $PatchFile cree. Colle ton bloc diff dedans et sauvegarde."
}

Write-Host "[*] Surveillance du fichier $PatchFile..."
$lastHash = ""

while ($true) {
  Start-Sleep -Milliseconds $PollMs
  if (-not (Test-Path $PatchFile)) { continue }

  $txt = Get-Content $PatchFile -Raw
  if ([string]::IsNullOrWhiteSpace($txt)) { continue }

  $hash = [System.BitConverter]::ToString((New-Object System.Security.Cryptography.SHA256Managed).ComputeHash([Text.Encoding]::UTF8.GetBytes($txt)))
  if ($hash -eq $lastHash) { continue }
  $lastHash = $hash

  $diff = Extract-DiffFromText $txt
  if ($null -ne $diff) {
    Write-Host "[*] Patch detecte dans $PatchFile -> application..." -ForegroundColor Magenta
    Apply-Diff $diff | Out-Null
    Clear-Content $PatchFile
  }
}