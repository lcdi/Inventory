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

def getLoginURL():
	return redirect(url_for('login'))

def getName():
	return session['displayName']

# ~~~~~~~~~~~~~~~~ Page Render Functions ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

def renderMainPage(itemType = 'ALL', status = 'in'):
	
	print(itemType)
	
	query = models.Device.select(
			models.Device.SerialNumber,
			models.Device.Type,
			models.Device.Description,
			models.Device.Issues
		).where(
			(models.Device.Type == itemType if itemType != 'ALL' else models.Device.Type != '')
			#,(models.Device.Quality == quality if itemType != 'ALL' else models.Device.Quality != '')
		).order_by(models.Device.SerialNumber)
	for device in query:
		device.log = models.Log.select(
			models.Log.UserIdentifier,
			models.Log.Purpose,
			models.Log.DateOut,
			models.Log.AuthorizerOut,
			models.Log.DateIn,
			models.Log.AuthorizerIn
		).where(
			models.Log.SerialNumber == device.SerialNumber
		).order_by(models.Log.Identifier)
		
		if len(device.log) > 0:
			device.log = device.log.get()
			if status == 'in':
				# remove device entry
				device;
			elif status == 'out':
				# remove device entry
				device;
	
	return render_template('index.html',
			query=query,
			types=models.getDeviceTypes(),
			states=models.getStates(),
			totalItems=len(query),
			
			name=escape(getName()),
			hasEditAccess=True
		)

def renderPage_View(serial):
	item = models.Device.select().where(models.Device.SerialNumber == serial).get()
	log = models.Log.select().where(models.Log.SerialNumber == serial)
	
	return render_template('viewItem.html',
			item=item,
			types=models.getDeviceTypes(),
			states=models.getStates(),
			log=log
		)
	
def renderFilter(device_type, status, page):
	
	if device_type == 'Select Type' and status == 'Select Status':
		return redirect(url_for('index'))
	elif device_type == 'Select Type' and status != 'Select Status':
		query = models.Device.select().order_by(models.Device.SerialNumber)
	elif device_type != 'Select Type' and status == 'Select Status':
		query = models.Device.select(
		).where(
			models.Device.Type == device_type
		).order_by(models.Device.SerialNumber)
	elif device_type != 'Select Type' and status != 'Select Status':
		query = models.Device.select(
		).where(
			models.Device.Type == device_type
		).order_by(models.Device.SerialNumber)
	
	types = models.getDeviceTypes()
	
	filters= [device_type, status]
	
	return render_template('searchResults.html',
			query=query,
			types=types,
			page=page,
			params=filters,
			states=models.getStates()
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
			
			if function == 'addItem':
				return addItem(
						#serialNumber = request.form['lcdi_serial'],
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
			
			elif function == 'filter':
				return renderMainPage(itemType = request.form['device_type'], status = request.form['device_status'])
			
		else:
			return renderMainPage()
	
	# Force user to login
	return getLoginURL()

@app.route('/search', methods=['GET', 'POST'])
def search():
	if not 'username' in session:
		return getLoginURL()
	if not request.method == 'POST':
		return getIndexURL()
	
	searchPhrase = request.form['searchField']
	
	if (len(models.Device.select().where(models.Device.SerialNumber == searchPhrase)) == 1):
		return renderPage_View(searchPhrase)
	else:
		query = models.Device.select(
			models.Device.SerialNumber,
			models.Device.SerialDevice,
			models.Device.Type,
			models.Device.Description,
			models.Device.Issues,
			models.Device.Quality
		).where(
			models.Device.SerialNumber.contains(search) |
			models.Device.SerialDevice.contains(searchPhrase) |
			models.Device.Type.contains(searchPhrase) |
			models.Device.Description.contains(searchPhrase)
		).order_by(models.Device.SerialNumber)
		
		types = models.getDeviceTypes()
		
		return render_template('searchResults.html',
				query=query,
				types=types,
				params=searchPhrase
			)

@app.route('/view/<string:serial>', methods=['GET', 'POST'])
def view(serial):
	if not 'username' in session:
		return getLoginURL()
	
	if request.method == 'POST' and request.form[pagePostKey] == 'updateItem':
		return updateItem(
				oldSerial = request.form['serial'],
				#serialNumber = request.form['lcdi_serial'],
				serialDevice = request.form['device_serial'],
				device_type = request.form['device_types'],
				device_other = request.form['other'],
				description = request.form['device_desc'],
				notes = request.form['device_notes'],
				state = request.form['device_state'],
				file = request.files['file']
			)
	
	return renderPage_View(serial)

@app.route('/signInOut', methods=['GET', 'POST'])
def signInOut():
	if not 'username' in session:
		return getLoginURL()
	if not request.method == 'POST':
		return getIndexURL()
	
	if request.form[pagePostKey] == 'out':
		serial = request.form['serial']
		
		models.Log.create(
				SerialNumber = serial,
				UserIdentifier = request.form['studentName'],
				Purpose = request.form['use'],
				DateOut = models.datetime.datetime.now(),
				AuthorizerOut = escape(getName())
			)
		
		return renderPage_View(serial)

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

def addItem(serialDevice, device_type, device_other, description, notes, state, file):
	
	if device_other != '':
		device_type = device_other
	if file and allowed_file(file.filename):
		filename = secure_filename(file.filename)
		file.save(os.path.join(app.config['UPLOAD_FOLDER'], filename))
	
	serialNumber = models.getNextSerialNumber(device_type)

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

def updateItem(oldSerial, serialDevice, device_type, device_other, description, notes, state, file):
	
	item = models.Device.select().where(models.Device.SerialNumber == oldSerial).get()
	
	if device_type == 'Other':
		device_type = device_other
	if file and allowed_file(file.filename):
		filename = secure_filename(file.filename)
		file.save(os.path.join(app.config['UPLOAD_FOLDER'], filename))
	
	item.SerialNumber = oldSerial
	item.SerialDevice = serialDevice
	item.Type = device_type
	item.Description = description
	item.Issues = notes
	if file:
		item.PhotoName = file.filename
	item.quality = state
	
	item.save()
	
	return renderPage_View(oldSerial)

def allowed_file(filename):
    return '.' in filename and \
           filename.rsplit('.', 1)[1] in ALLOWED_EXTENSIONS
         
def signOutItem(serial, sname, use):
	identifierItem = models.Log.select().order_by(models.Log.Identifier.desc())
	if len(identifierItem) == 0:
		identifier = 1
	else:
		identifier = identifierItem.Identifier + 1
	
	models.Log.create(
			Identifier = identifier,
			SerialNumber = serial,
			UserIdentifier = sname,
			Purpose = use,
			DateOut = models.datetime.datetime.now(),
			AuthorizerOut = escape(session['displayName'])
		)
	
	return renderPage_View(serial)

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
	#models.Device.create_table()
	#models.Log.create_table()
	#"""
	
	models.db.close()
