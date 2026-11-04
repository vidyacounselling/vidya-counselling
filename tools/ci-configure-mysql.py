"""Generate an isolated MySQL PHPUnit config on the remote CI runner only."""
from pathlib import Path
import xml.etree.ElementTree as ET

root = Path(__file__).resolve().parents[1]
tree = ET.parse(root / 'phpunit.xml')
config = tree.getroot()
# The generated config lives in build/; make paths absolute on this ephemeral runner.
config.set('bootstrap', str(root / 'vendor/autoload.php'))
config.set('{http://www.w3.org/2001/XMLSchema-instance}noNamespaceSchemaLocation', str(root / 'vendor/phpunit/phpunit/phpunit.xsd'))
for directory in config.findall('.//directory'):
    directory.text = str(root / directory.text)
values = {'DB_CONNECTION': 'mysql', 'DB_DATABASE': 'vidya_test', 'DB_HOST': '127.0.0.1',
          'DB_PORT': '3306', 'DB_USERNAME': 'root', 'DB_PASSWORD': 'ci-disposable-password', 'DB_URL': ''}
php = config.find('php')
for name, value in values.items():
    item = next((node for node in php.findall('env') if node.get('name') == name), None)
    if item is None:
        item = ET.SubElement(php, 'env', {'name': name})
    item.set('value', value)
    item.set('force', 'true')
(root / 'build').mkdir(exist_ok=True)
tree.write(root / 'build/phpunit.mysql.xml', encoding='utf-8', xml_declaration=True)
