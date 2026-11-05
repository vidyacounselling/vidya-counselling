"""Create separate private/public candidates on remote CI. Never deploy anything."""
import hashlib
import json
import os
from pathlib import Path
import zipfile
import subprocess

root = Path(__file__).resolve().parents[1]
if os.environ.get('GITHUB_ACTIONS') != 'true':
    raise SystemExit('Remote GitHub Actions build only; no local runtime installation required.')
if not (root / 'composer.lock').is_file() or not (root / 'vendor/autoload.php').is_file():
    raise SystemExit('Locked production dependencies are required.')
out = root / 'build/release'
out.mkdir(parents=True, exist_ok=True)
source_commit = subprocess.check_output(['git','rev-parse','HEAD'], cwd=str(root)).decode().strip()
(root / 'bootstrap/release.json').write_text(json.dumps({'commit': source_commit}))
private_roots = ['app', 'bootstrap', 'config', 'database', 'resources', 'routes', 'vendor']
files = [root / 'artisan', root / 'composer.json', root / 'composer.lock']
for folder in private_roots:
    files.extend(p for p in (root / folder).rglob('*') if p.is_file())
def allowed(p):
    rel = p.relative_to(root)
    if p.is_symlink():
        raise RuntimeError(f'Review unexpected symlink: {rel}')
    if any(part == '.git' or part == 'backups' or part.startswith('.env') for part in rel.parts):
        return False
    if rel.parts[:2] == ('bootstrap', 'cache'):
        return p.name == '.gitignore'
    return True
with zipfile.ZipFile(out / 'private-app.zip', 'w', zipfile.ZIP_DEFLATED) as archive:
    for p in sorted(set(files)):
        if allowed(p):
            archive.write(p, p.relative_to(root).as_posix())
    # Fresh runtime storage, never CI sessions/logs/keys/test records.
    for folder in ['storage/app/private', 'storage/app/public', 'storage/framework/cache/data',
                   'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'bootstrap/cache']:
        archive.writestr(folder + '/', '')
with zipfile.ZipFile(out / 'public-only.zip', 'w', zipfile.ZIP_DEFLATED) as archive:
    for p in sorted((root / 'public').rglob('*')):
        if p.is_file() and allowed(p):
            archive.write(p, p.relative_to(root / 'public').as_posix())
lock = json.loads((root / 'composer.lock').read_text())
framework = next(p['version'] for p in lock['packages'] if p['name'] == 'laravel/framework')
manifest = {'commit': source_commit, 'laravel': framework,
            'targetWebPhp': '8.3.36 (must recheck before deploy)',
            'status': 'candidate only; Hostinger placement/migrations not verified',
            'publicEntryPoint': 'Standard adjacent layout; must adapt only after private/public mapping is verified.',
            'files': {p.name: hashlib.sha256(p.read_bytes()).hexdigest() for p in out.glob('*.zip')}}
(out / 'manifest.json').write_text(json.dumps(manifest, indent=2))
