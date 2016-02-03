from flask import Flask, render_template, url_for, redirect
from flask import session, escape, request
from peewee import *
#from datetime import date

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
		session['username'] = request.form['username']
		return url_for('index')
	return '''<form action="" method="post"><p><input type=text name=username><p><input type=submit value=Login></form>'''

@app.route('/logout')
def logout():
	session.pop('username', None)
	return redirect(url_for('index'))

if __name__ == '__main__':
	db.connect()
	app.run()
