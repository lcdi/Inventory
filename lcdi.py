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

isDebugMode = True

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

# ~~~~~~~~~~~~~~~~~~~~~ Index ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

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
			elif request.form['formID'] == 'openEntry':
				return renderEntry('view', request.form['serialNumber'])
			elif request.form['formID'] == 'openEditting':
				return renderEntry('openEditting', request.form['serialNumber'])
			elif request.form['formID'] == 'saveInformation':
				# TODO save information
				return renderEntry('view', request.form['serialNumber'])
		else:
			return renderMainPage()
	
	# Force user to login
	return redirect(url_for('login'))
	
def renderMainPage(serialNumber = '', itemType = 'ALL', state = 'ALL', status = 'All'):
	
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
				).order_by(models.Device.serialNumber)#idNumber)
	types = models.getDeviceTypes()
	return render_template('listings_page.html',
			indexURL=url_for('index'),
			logoutURL=url_for('logout'),
			hasEditAccess=True,
			totalItems=len(query),
			signedOutItems=0,
			
			name=escape(session['displayName']),
			query=query,
			types=types
		)

# ~~~~~~~~~~~~~~~~~~~~~ Log In/Out ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

@app.route('/login', methods=['GET', 'POST'])
def login():
	if 'username' in session:
		return redirect(url_for('index'))
	elif request.method == 'POST':
		try:
			user = request.form['username']
			pw = request.form['password']
			valid, hasEditAccess = adLDAP.checkCredentials(user, pw)
			if (app.debug == True or valid == True):
				# Set username and displayName in session
				session['username'] = user
				session['displayName'] = session['username']
				session['hasEditAccess'] = hasEditAccess
				
				# Send user back to index page
				# (if username wasnt set, it will redirect back to login screen)
				return redirect(url_for('index'))
				
		except Exception as e:
			return str(e)
	else
		# Was not a POST, which means index or some other source sent user to login
		return render_template('login.html')

@app.route('/logout')
def logout():
	session.pop('username', None)
	session.pop('displayName', None)
	session.pop('hasEditAccess', None)
	return redirect(url_for('login'))

# ~~~~~~~~~~~~~~~~ Entries ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

def renderEntry(function, serialNumber):
	hasEditAccess = session['hasEditAccess']
	
	formID = 'view'
	entryType = 'View'
	if function == 'view':
		entryType = 'View'
		if hasEditAccess:
			formID = 'openEditting'
	elif function == 'openEditting' and hasEditAccess:
		entryType = 'Edit'
		formID = 'saveInformation'
	elif function == 'add' and hasEditAccess:
		entryType = 'Edit'
		formID = 'saveInformation'
	
	return render_template('entry' + entryType + '.html',
			indexURL=url_for('index'),
			logoutURL=url_for('logout'),
			
			formID=formID,
			submitURL=url_for('index'),
			hasEditAccess=hasEditAccess,
			
			serialNumber=serialNumber,
			itemType='Type A',
			description='This is a desc',
			state='operational',
			notes='this is a note',
			photoName='IMG_9880.JPG'
		)

# ~~~~~~~~~~~~~~~~ Start page ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

init(isDebugMode)

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
