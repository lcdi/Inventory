# Flask imports
from flask import Flask, render_template, session, redirect, url_for, escape, request

# Peewee
from peewee import *

# File manipulation
import sys
import os
import os.path

# Custom support files
import models
import adLDAP

# Other
#from datetime import date

# ~~~~~~~~~~~~~~~~ Start Execution ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

# Create Flask app
app = Flask(__name__)

# ~~~~~~~~~~~~~~~~ Create Functions ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

# LDAP http://www.python-ldap.org/doc/html/installing.html

# ~~~~~~~~~~~~~~~~ Startup Functions ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

def init(isDebug):
	app.debug = isDebug
	#app._static_folder = '/static'
	
	# Generate secret key for session
	app.secret_key = os.urandom(20)

# ~~~~~~~~~~~~~~~~ Page Render Functions ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

def renderMainPage(
		serialNumber = '', itemType = 'ALL', state = 'ALL', status = 'All'):
	
	# TODO filter results, set page form filters, and remove prints
	print(serialNumber)
	print(itemType)
	print(state)
	print(status)
	
	query = models.Device.select(models.Device.serialNumber,
								 models.Device.typeCategory,
								 models.Device.description,
								 models.Device.issues,
								 models.Device.state
				).order_by(models.Device.idNumber)
	types = models.getDeviceTypes()
	return render_template('listings_page.html',
			name=escape(session['displayName']),
			query=query,
			types=types,
			logoutURL=url_for('logout')
		)

def renderEntry(serialNumber):
	
	return render_template('entryView.html',
		serialNumber='LCDI-4358679',
		photoName='IMG_9880.jpg'
		)

@app.route('/', methods=['GET', 'POST'])
def index():
	# http://flask.pocoo.org/snippets/15/
	
	# If user logged in
	if 'username' in session:
		# Render main page
		if request.method == 'POST':
			print(request.values)
			if request.form['formID'] == 'filter':
				return renderMainPage(
								serialNumber = request.form['serialNumber'],
								itemType = request.form['itemtype'],
								state = request.form['state'],
								status = request.form['status'])
			elif request.form['formID'] == 'entry':
				return renderEntry(request.form['serialNumber'])
		else:
			return renderMainPage()
	
	# Force user to login
	return redirect(url_for('login'))

@app.route('/login', methods=['GET', 'POST'])
def login():
	# If form has been submitted
	if request.method == 'POST':
		try:
			if (app.debug == True or
				adLDAP.areCredentialsValid(
					request.form['username'],
					request.form['password']
					)):
				
				# Set username and displayName in session
				session['username'] = request.form['username']
				session['displayName'] = session['username']
		except Exception as e:
			return str(e)
			
		try:
			# Send user back to index page
			# (if username wasnt set, it will redirect back to login screen)
			return redirect(url_for('index'))
		except Exception as e:
			return str(e)
			
	# Was not a POST, which means index or some other source sent user to login
	return render_template('login.html')

@app.route('/logout')
def logout():
	session.pop('username', None)
	session.pop('displayName', None)
	return redirect(url_for('index'))

# ~~~~~~~~~~~~~~~~ Start page ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

init(True)

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
