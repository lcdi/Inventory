# Flask imports
from flask import Flask, render_template, session, redirect, url_for, escape, request
# Peewee
from peewee import *
# File manipulation
import sys
import os
import os.path

import models
# Other
#from datetime import date

# LDAP; http://www.python-ldap.org/doc/html/installing.html

# Create Flask app
app = Flask(__name__)
app.debug = True
app.secret_key = os.urandom(20)

@app.route('/')
def index():
	query = models.Device.select(models.Device.serialNumber, models.Device.typeCategory,
	 					  models.Device.description, models.Device.issues,
						  models.Device.state
						  ).order_by(models.Device.idNumber)
	types = returnTypes()
	# http://flask.pocoo.org/snippets/15/
	if 'username' in session:
		return render_template('listings_page.html', name=escape(session['username']), query=query, types=types)
	return redirect(url_for('login'))

@app.route('/login', methods=['GET', 'POST'])
def login():
	if request.method == 'POST':
		try:
			session['username'] = request.form['username']
		except Exception as e:
			return str(e)
		try:
			return redirect(url_for('index'))
		except Exception as e:
			return str(e)
	return render_template('login.html')

@app.route('/logout')
def logout():
	session.pop('username', None)
	return redirect(url_for('index'))
    
def returnTypes():
	types = [];
	
	query = models.Device.select(models.Device.typeCategory).order_by(models.Device.typeCategory)
	for q in query:
		if (typeExist(q.typeCategory, types) == True):
			pass
		else:
			types.append(q.typeCategory)
	
	return types
	
def typeExist(t, types):
	for _type in types:
		if t == _type:
			return True
	
	return False

if __name__ == '__main__':
	ctx = app.test_request_context()
	ctx.push()
	app.preprocess_request()
	port = int(os.getenv('PORT', 8080))
	host = os.getenv('IP', '0.0.0.0')
	app.run(port=port, host=host)

	models.db.connect()
	
	models.Device.create(
		idNumber = 2,
		serialNumber = 'LCDI-0001',
		typeCategory = 'Phone',
		description = 'This is a phone',
		issues = 'None of note',
		photo = 'IMG_002.jpg',
		state = 'decommissioned'
	)

	models.db.close()
