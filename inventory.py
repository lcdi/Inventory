# Flask imports
from flask import Flask, render_template, session, redirect, url_for, escape, request
# Peewee
from peewee import *
# File manipulation
import sys
import os.path
# Other
#from datetime import date

# Create Flask app
app = Flask(__name__)

# http://docs.peewee-orm.com/en/latest/peewee/quickstart.html
database = SqliteDatabase('developmentData.db')

#class Device(Model):
#	idNumber = IntField()
#	serialNumber = CharField()
#	typeCategory = CharField()
#	description = TextField()
#	issues = TextField()
#	photo = CharField()
#	quality = CharField()

def install_secret_key(app, filename='secret_key'):
    filename = os.path.join(app.instance_path, filename)
    try:
        app.config['SECRET_KEY'] = open(filename, 'rb').read()
    except IOError:
        print 'Error: No secret key. Create it with:'
        if not os.path.isdir(os.path.dirname(filename)):
            print 'mkdir -p', os.path.dirname(filename)
        print 'head -c 24 /dev/urandom >', filename
        sys.exit(1)

@app.route('/')
def index():
	# http://flask.pocoo.org/snippets/15/
	if 'username' in session:
		return render_template('inventory.html', name=escape(session['username']), inventoryData="", deviceLogData="")
	return redirect(url_for('login'))

@app.route('/login', methods=['GET', 'POST'])
def login():
	if request.method == 'POST':
		try:
			session['username'] = request.form['username']
		except Exception as e:
			return str(e)
		return 'finished'#redirect(url_for('index'))
	return '''<form action="" method="post"><p><input type=text name=username><p><input type=submit value=Login></form>'''

@app.route('/logout')
def logout():
	session.pop('username', None)
	return redirect(url_for('index'))

app.secret_key = 'A0Zr98j/3yX R~XHH!jmN]LW/,?RT'

if __name__ == '__main__':
	db.connect()
	app.run(debug = True)

# Load secret key
#with open('file.dat') as f:
#    app.secret_key = f.read()
