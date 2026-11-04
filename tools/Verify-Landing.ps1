$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$manifest = Get-Content -Raw -LiteralPath (Join-Path $projectRoot 'backups/manifest.json') | ConvertFrom-Json
$backupPath = Join-Path $projectRoot ('backups/' + $manifest.backup)
$original = [IO.File]::ReadAllText($backupPath)
$hash = (Get-FileHash -LiteralPath $backupPath -Algorithm SHA256).Hash.ToLowerInvariant()
if ($hash -ne $manifest.sha256) { throw 'Original backup checksum mismatch' }
if ((Get-Item -LiteralPath $backupPath).Length -ne $manifest.bytes) { throw 'Backup byte size mismatch' }
$blade = [IO.File]::ReadAllText((Join-Path $projectRoot 'resources/views/landing.blade.php'))
$style = [regex]::Match($original, '<style>([\s\S]*?)</style>')
$css = [IO.File]::ReadAllText((Join-Path $projectRoot 'public/css/landing.css'))
if ($css -cne $style.Groups[1].Value) { throw 'CSS changed' }
$cssReference = "<link rel=`"stylesheet`" href=`"{{ asset('css/landing.css') }}`">"
$restored = $blade.Replace($cssReference, $style.Value)
$scripts = [regex]::Matches($original, '<script>([\s\S]*?)</script>')
for ($i = 0; $i -lt $scripts.Count; $i++) {
    $scriptNumber = $i + 1
    $js = [IO.File]::ReadAllText((Join-Path $projectRoot "public/js/landing-$scriptNumber.js"))
    if ($js -cne $scripts[$i].Groups[1].Value) { throw "JavaScript $scriptNumber changed" }
    $reference = "<script src=`"{{ asset('js/landing-$scriptNumber.js') }}`"></script>"
    $restored = $restored.Replace($reference, $scripts[$i].Value)
}
$embedded = [regex]::Match($original, 'data:image/png;base64,([A-Za-z0-9+/=]+)')
$image = [IO.File]::ReadAllBytes((Join-Path $projectRoot 'public/images/vidya-logo.png'))
if ([Convert]::ToBase64String($image) -cne $embedded.Groups[1].Value) { throw 'Embedded logo changed' }
$restored = $restored.Replace("{{ asset('images/vidya-logo.png') }}", $embedded.Value).Replace('@@context', '@context').Replace('@@type', '@type')
$expected = $original
if ($restored -cne $expected) { throw 'HTML/content differs beyond the declared asset transformations' }
if ($blade -match '(?i)href\s*=\s*["''][^"'']*/(login|admin|register)') { throw 'Public account navigation found' }
$composer = Get-Content -Raw -LiteralPath (Join-Path $projectRoot 'composer.json') | ConvertFrom-Json
if (($composer.scripts | ConvertTo-Json -Depth 8) -match 'npm|vite|node') { throw 'Frontend build dependency found' }
Write-Output 'PASS: Backup hash/size, exact CSS, both exact scripts, exact logo, lossless HTML reconstruction, public navigation, no frontend build dependency.'
