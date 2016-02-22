# Flask imports
from flask import Flask, render_template, session, redirect, url_for, escape, request
from werkzeug import secure_filename
import flask.ext.whooshalchemy

# Peewee
from peewee import *

# File manipulation
import sys
import os
import os.path

# Custom support files
import models
import adLDAP

# Paramaters
isDebugMode = True
pagePostKey = 'functionID'
UPLOAD_FOLDER = 'static/item_photos'
ALLOWED_EXTENSIONS = set(['png', 'jpg', 'jpeg', 'gif',
						  'PNG', 'JPG', 'JPEG', 'GIF'])

# ~~~~~~~~~~~~~~~~ Start Execution ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
app = Flask(__name__)
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER

# ~~~~~~~~~~~~~~~~ Startup Functions ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

def init(isDebug):
	app.debug = isDebug
	# Generate secret key for session
	app.secret_key = os.urandom(20)
	
def getIndexURL():
	return redirect(url_for('index'))
	
# ~~~~~~~~~~~~~~~~ Page Render Functions ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

def renderMainPage(serialNumber = '', itemType = 'ALL', state = 'ALL', status = 'All'):
	# TODO filter results, set page form filters, and remove prints
	#print(serialNumber)
	#print(itemType)
	#print(state)
	#print(status)
	
	query = models.Device.select().order_by(models.Device.SerialNumber)
	types = models.getDeviceTypes()
	states = models.getStates()
	return render_template('index.html',
			query=query,
			types=types,
			states=states,
			totalItems=len(query),
			
			name=escape(session['displayName']),
			logoutURL=url_for('logout'),
			indexURL=url_for('index'),
			hasEditAccess=True
		)

def renderPage_Search(search, page):
	
	query = models.Device.select(
		models.Device.SerialNumber,
		models.Device.SerialDevice,
		models.Device.Type,
		models.Device.Description,
		models.Device.Issues,
		models.Device.Quality
	).where(
		models.Device.SerialNumber.contains(search) |
		models.Device.SerialDevice.contains(search) |
		models.Device.Type.contains(search) |
		models.Device.Description.contains(search)
	).order_by(models.Device.SerialNumber)
	
	types = models.getDeviceTypes()
	
	return render_template('searchResults.html',
			query=query,
			types=types,
			page=page,
			logoutURL=url_for('logout')
		)

def renderPage_View(serial):
	item = models.Device.select().where(models.Device.SerialNumber == serial)
	types = models.getDeviceTypes()
	states = models.getStates()
	return render_template('viewItem.html',
			item=item,
			types=types,
			states=states,
			logoutURL=url_for('logout')
		)

def renderEntry(function, serialNumber):
	# TODO remake entry files
	return getIndexURL()
	hasEditAccess = app.debug == True or session['hasEditAccess']
	
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
		
def renderFilter(device_type, status, page):
	query = models.Device.select(
	).where(
		models.Device.Type == device_type
	).order_by(models.Device.SerialNumber)
	
	types = models.getDeviceTypes()
	
	return render_template('searchResults.html',
			query=query,
			types=types,
			page=page,
			logoutURL=url_for('logout')
		)
	

# ~~~~~~~~~~~~~~~~ Routing Functions ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

@app.route('/', methods=['GET', 'POST'])
def index():
	# http://flask.pocoo.org/snippets/15/
	
	# If user logged in
	if 'username' in session:
		# Render main page
		if request.method == 'POST':
			function = request.form[pagePostKey]
			if function == 'search':
				return renderPage_Search(request.form['searchField'], page="Search")
			elif function == 'viewSerial':
				return renderPage_View(request.form['serial'])
			elif function == 'addItem':
				return addItem(
						serialNumber = request.form['lcdi_serial'],
						serialDevice = request.form['device_serial'],
						device_type = request.form['device_types'],
						device_other = request.form['other'],
						description = request.form['device_desc'],
						notes = request.form['device_notes'],
						state = request.form['device_state'],
						file = request.files['file']
					)
			elif function == 'deleteItem':
				item = models.Device.select().where(
						models.Device.SerialNumber == request.form['serial']
					).get();
				item.delete_instance();
				return getIndexURL()
			elif function == 'updateItem':
				return updateItem(
						oldSerial = request.form['serial'],
						serialNumber = request.form['lcdi_serial'],
						serialDevice = request.form['device_serial'],
						device_type = request.form['device_types'],
						device_other = request.form['other'],
						description = request.form['device_desc'],
						notes = request.form['device_notes'],
						state = request.form['device_state'],
						file = request.files['file']
					)
			elif function == 'filter':
				return renderFilter(request.form['filter_types'], request.form['filter_status'], page="Filter")
				
		else:
			return renderMainPage()
	
	# Force user to login
	return redirect(url_for('login'))

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
				return getIndexURL()
				
		except Exception as e:
			return str(e)
	else:
		# Was not a POST, which means index or some other source sent user to login
		return render_template('login.html')

@app.route('/logout')
def logout():
	session.pop('username', None)
	session.pop('displayName', None)
	session.pop('hasEditAccess', None)
	return redirect(url_for('login'))

# ~~~~~~~~~~~~~~~~ Utility ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

def addItem(serialNumber, serialDevice, device_type, device_other, description, notes, state, file):
	if device_type == 'Other':
		device_type = device_other
	if file and allowed_file(file.filename):
		filename = secure_filename(file.filename)
		file.save(os.path.join(app.config['UPLOAD_FOLDER'], filename))
	models.Device.create(
			SerialNumber = serialNumber,
			SerialDevice = serialDevice,
			Type = device_type,
			Description = description,
			Issues = notes,
			PhotoName = file.filename,
			Quality = state
		)
	return renderPage_View(serialNumber)

def updateItem(oldSerial, serialNumber, serialDevice, device_type, device_other, description, notes, state, file):
	
	item = models.Device.select().where(models.Device.SerialNumber == oldSerial).get()
	
	if device_type == 'Other':
		device_type = device_other
	if file and allowed_file(file.filename):
		filename = secure_filename(file.filename)
		file.save(os.path.join(app.config['UPLOAD_FOLDER'], filename))
	
	item.SerialNumber = serialNumber
	item.SerialDevice = serialDevice
	item.Type = device_type
	item.Description = description
	item.Issues = notes
	if file:
		item.PhotoName = file.filename
	item.quality = state
	
	item.save()
	
	return renderPage_View(serialNumber)

def allowed_file(filename):
    return '.' in filename and \
           filename.rsplit('.', 1)[1] in ALLOWED_EXTENSIONS
	
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
	
	#"""
	models.Device.create_table()
	models.Log.create_table()
	#"""
	
	"""
	models.Device.create(
		idNumber = 2,
		serialNumber = 'LCDI-1111',
		typeCategory = 'Phone',
		description = 'iPhone 6 Plus',
		issues = 'None of note',
		photo = 'IMG_001.png',
		state = 'Operational'
	)
	
	models.InOut.create(
		idNumber = 2,
		studentName = 'Matthew Fortier',
		use = 'iOS Forensics',
		userIn = 'N/A',
		userOut = 'mfortier',
		issues = 'None of note'
	)
	"""
	
	models.db.close()
