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

function Apply-Diff($text) {
  # Format attendu:
  # === path: relative/path/to/file.ext
  # <<<
  # ... contenu ...
  # >>>
  $lines = $text -split "`r?`n"
  $i = 0
  $written = @()

  while ($i -lt $lines.Count) {
    $line = $lines[$i]

    if ($line -match '^\s*===\s*path\s*:\s*(.+)\s*$') {
      # Capture propre du chemin après '=== path:'
      $path = $Matches[1]
      # Nettoyage (enlève deux-points/espaces parasites en tête)
      $path = ($path -replace '^[\s:]+','').Trim()

      $i++

      # On attend '<<<'
      if ($i -ge $lines.Count -or $lines[$i].Trim() -ne '<<<') {
        Write-Host "[X] Bloc '<<<' manquant pour $path" -ForegroundColor Red
        return $false
      }
      $i++

      # Accumule le contenu jusqu'à '>>>'
      $sb = New-Object System.Text.StringBuilder
      while ($i -lt $lines.Count -and $lines[$i].Trim() -ne '>>>') {
        [void]$sb.AppendLine($lines[$i])
        $i++
      }

      if ($i -ge $lines.Count -or $lines[$i].Trim() -ne '>>>') {
        Write-Host "[X] Bloc '>>>' manquant pour $path" -ForegroundColor Red
        return $false
      }

      # Crée le dossier si besoin
      $dir = Split-Path -Path $path -Parent
      if ($dir -and -not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
      }

      # Écrit le fichier en UTF-8 (sans BOM)
      Set-Content -Path $path -Value $sb.ToString() -Encoding UTF8

      $written += $path
    }

    $i++
  }

  if ($written.Count -eq 0) {
    Write-Host "[!] Aucun bloc '=== path:' detecte" -ForegroundColor Yellow
    return $false
  }

  git add -A
  $msg = "Apply filepack: " + (Get-Date).ToString("yyyy-MM-dd HH:mm:ss")
  git commit -m $msg | Out-Null

  Write-Host "[OK] Fichiers ecrits: $($written -join ', ')" -ForegroundColor Green

  # Optionnel: on vide patch.txt pour éviter de ré-appliquer au prochain tour
  try { Clear-Content patch.txt -ErrorAction SilentlyContinue } catch {}
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