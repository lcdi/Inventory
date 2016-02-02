import sys
sys.path.insert(0, '/var/www/inventory')
os.chdir("/var/www/inventory")
from inventory import app as application
