# Flask imports
from flask import Flask, render_template, session, redirect, url_for, escape, request
# Peewee
from peewee import *
# File manipulation
import os
from settings import APP_STATIC
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

@app.route('/')
def index():
	# http://flask.pocoo.org/snippets/15/
	if 'username' in session:
		return render_template('inventory.html', inventoryData="", deviceLogData="")
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

if __name__ == '__main__':
	db.connect()
	app.run(debug = True)

# Load secret key
with open('file.dat') as f:
    app.secret_key = f.read()
