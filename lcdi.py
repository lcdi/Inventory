# Flask imports
from flask import Flask, render_template, session, redirect, url_for, escape, request, jsonify, abort
from flask_jsglue import JSGlue
from werkzeug import secure_filename
import flask.ext.whooshalchemy
from functools import wraps

# Peewee
from peewee import *

# Python
import sys
import os
import os.path
import time
import json
import logging

# Custom support files
import adLDAP

# Paramaters
pagePostKey = 'functionID'
APP_ROOT = os.path.dirname(os.path.abspath(__file__))
UPLOAD_FOLDER = os.path.join(APP_ROOT, 'static/item_photos')
ALLOWED_EXTENSIONS = set(['png', 'jpg', 'jpeg', 'gif',
						  'PNG', 'JPG', 'JPEG', 'GIF'])

# ~~~~~~~~~~~~~~~~ Start Execution ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
app = Flask(__name__)
jsglue = JSGlue(app)
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER
app.config.from_object('config')

#lcdiLog = logging.getLogger('lcdi_logger')

# TODO use a decorator for logins http://flask.pocoo.org/docs/0.10/patterns/viewdecorators/#login-required-decorator

# ~~~~~~~~~~~~~~~~ Decorators ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

def login_required(f):
	@wraps(f)
	def decorated_function(*args, **kwargs):
		if not 'username' in session:
			return getLoginURL()
		return f(*args, **kwargs)
	return decorated_function

# ~~~~~~~~~~~~~~~~ Startup Functions ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

__all__ = ['getConfig']

def init():
	#lcdiLog.addHandler(logging.FileHandler(os.getcwd() + '/lcdi.log'))
	#lcdiLog.setLevel(logging.DEBUG)
	#logging.basicConfig(filename='lcdi.log',level=logging.DEBUG)
	# Generate secret key for session
	app.secret_key = os.urandom(20)

def getIndexURL():
	return redirect(url_for('index'))

def getLoginURL():
	return redirect(url_for('login'))

def getName():
	return session['displayName']

def getConfig(key):
	return app.config[key]

import models

# ~~~~~~~~~~~~~~~~ Page Render Functions ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

def renderHomepage():
	return render_template("page/PageIndex_Homepage.html")

def renderInventoryListings(itemType = 'ALL', status = 'ALL', quality = 'ALL', searchSerial = None, searchModal = None):
	
	deviceList = models.getDevicesWithLog(itemType, status, quality)
	length = models.getDevices()
	
	error = None
	if 'error' in session:
		error = session['error']
		session.pop('error', None)
	
	return render_template("page/PageIndex_Inventory.html",
			filter_Type = itemType,
			filter_Status = status,
			filter_quality = quality,
			
			query = deviceList,
			types = models.getDeviceTypes(),
			states = models.getStates(),
			
			totalItems = len(length),
			totalSignedOut = len(models.getDevicesWithLog('ALL', 'out', 'ALL')),
			
			data_id = searchSerial,
			queueModal = searchModal,
			
			name = escape(getName()),
			error = error
		)

def renderPage_View(serial, error = None):
	device = models.Device.select().where(models.Device.SerialNumber == serial).get()
	log = models.getDeviceLog(serial)
	
	if len(log) > 0:
		device.statusIsOut = not log.get().DateIn
	else:
		device.statusIsOut = False
	
	return render_template("page/PageViewItem.html",
			device = device,
			types = models.getDeviceTypes(),
			states = models.getStates(),
			log = log,
			error = error
		)

# ~~~~~~~~~~~~~~~~ Routing Functions ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

@app.route('/items/all')
@login_required
def viewAll():
	session['redirectSource'] = 'all'
	return getIndexURL()

@app.route('/items/out')
@login_required
def viewOut():
	session['redirectSource'] = 'outItems'
	return getIndexURL()

@app.route('/', methods=['GET', 'POST'])
@login_required
def index():
	# http://flask.pocoo.org/snippets/15/
	
	# Render main page
	if request.method == 'POST':
		function = request.form[pagePostKey]
		
		#logging.info('[INDEX] Executing function: ' + function)
		try:
			if function == 'addItem':
				return addItem(
						serialDevice = request.form['device_serial'],
						device_type = request.form['device_types'],
						device_other = request.form['other'],
						description = request.form['device_desc'],
						notes = request.form['device_notes'],
						quality = request.form['device_quality'],
						file = request.files['file']
					)
			elif function == 'deleteItem':
				try:
					serial = request.form['serial']
					item = models.Device.select().where(models.Device.SerialNumber == serial).get();
					if item.PhotoName:
						try:
							os.remove(UPLOAD_FOLDER + '/' + item.PhotoName)
						except OSError, e:
							print e.errno
							print e.filename
							print e.strerror
					item.delete_instance()
				except:
					print(sys.exc_info()[0])
				
				return getIndexURL()
			elif function == 'filter':
				return renderInventoryListings(itemType = request.form['type'], status = request.form['status'], quality = request.form['quality'])
		except:
			#logging.error(sys.exc_info()[0])
			#flash(sys.exc_info()[0])
			return renderInventoryListings()
		
	else:
		status = 'ALL'
		if 'redirectSource' in session:
			if session['redirectSource'] == 'outItems':
				status = 'out'
			session.pop('redirectSource', None)
		return renderInventoryListings(status = status)

@app.route('/login', methods=['GET', 'POST'])
def login():
	if 'username' in session:
		return getIndexURL()
	elif request.method == 'POST':
		try:
			user = request.form['username']
			pw = request.form['password']
			valid, hasEditAccess = adLDAP.checkCredentials(
				app.config['LDAP_CONTROLLER'],
				app.config['LDAP_DOMAIN_A'],
				app.config['LDAP_DOMAIN_B'],
				user, pw)
			if valid != True:
				session["error"] = valid
			if (app.debug == True or valid == True):
				if (app.debug == True or hasEditAccess == True):
					# Set username and displayName in session
					session['username'] = user
					session['displayName'] = session['username']
					session['hasEditAccess'] = hasEditAccess or app.debug == True
					session['redirectSource'] = 'outItems'
				else:
					session["error"] = "You do not have access"
			
			# Send user back to index page
			# (if username wasnt set, it will redirect back to login screen)
			return getIndexURL()
			
		except Exception as e:
			return str(e)
	else:
		# Was not a POST, which means index or some other source sent user to login
		if 'error' in session:
			error = session['error']
			session.pop('error', None)
			return render_template("page/PageLogin.html", error=error)
		else:
			return render_template("page/PageLogin.html", error=None)

@app.route('/logout')
def logout():
	session.pop('username', None)
	session.pop('displayName', None)
	session.pop('hasEditAccess', None)
	session.pop('redirectSource', None)
	return getIndexURL()

@app.route('/search', methods=['GET', 'POST'])
@login_required
def search():
	if not request.method == 'POST':
		return getIndexURL()
	
	searchPhrase = str(request.form['searchField'])
	
	if searchPhrase.startswith("scan:"):
		serial = searchPhrase[5:]
		query = models.getDeviceLog(serial)
		isOut, obj = models.getStatus(query)
		if isOut:
			modal = "signIn"
		else:
			modal = "signOut"
		return renderInventoryListings(searchSerial = serial, searchModal = modal)
		
	if models.isSearchUser(searchPhrase):
		logs = models.Log.select().where(
			models.Log.AuthorizerIn.contains(searchPhrase) | 
			models.Log.AuthorizerOut.contains(searchPhrase) | 
			models.Log.UserIn.contains(searchPhrase) | 
			models.Log.UserOut.contains(searchPhrase)
			).order_by(-models.Log.DateOut)
		
		return render_template("page/PageUserLogs.html", query = logs, searchPhrase = searchPhrase)
		
	
	if (len(models.Device.select().where(models.Device.SerialNumber == searchPhrase)) == 1):
		return renderPage_View(searchPhrase)
	else:
		query = models.getDevicesAndLogs().where(
			models.Device.SerialNumber.contains(searchPhrase) |
			models.Device.SerialDevice.contains(searchPhrase) |
			models.Device.Type.contains(searchPhrase) |
			models.Device.Description.contains(searchPhrase) |
			models.Log.Purpose.contains(searchPhrase)
		)
		deviceList = models.getDeviceAndLogListForQuery(query)
		
		return render_template("page/PageSearchResults.html",
				query = deviceList,
				types = models.getDeviceTypes(),
				params = searchPhrase,
				searchPhrase = searchPhrase)

@app.route('/signInOut', methods=['GET', 'POST'])
@login_required
def signInOut():
	if not request.method == 'POST':
		return getIndexURL()
	
	function = request.form[pagePostKey]
	serial = request.form['lcdi_serial']
	
	if function == 'out':
		models.Log.create(
			SerialNumber = serial,
			UserOut = request.form['userID'],
			Purpose = request.form['purpose'],
			DateOut = models.datetime.datetime.now(),
			AuthorizerOut = session['username']
		)
	elif function == 'in':
		deviceLog = models.getDeviceLog(serial).get()
		deviceLog.UserIn = request.form['userID']
		deviceLog.DateIn = models.datetime.datetime.now()
		deviceLog.AuthorizerIn = session['username']
		deviceLog.save()
		
	return getIndexURL()

@app.route('/users', methods=['GET', 'POST'])
@login_required
def userLogsAll():
	
	query = models.Log.select().order_by(-models.Log.DateOut)
	
	searchPhrase = ""
	if request.method == 'POST':
		searchPhrase = request.form['searchField']
		query = (query
			.where(
				models.Log.UserOut.contains(searchPhrase) |
				models.Log.UserIn.contains(searchPhrase) |
				models.Log.AuthorizerIn.contains(searchPhrase) |
				models.Log.AuthorizerOut.contains(searchPhrase)
			)
		)
		
		if not 'isFormSubmission' in request.form:
			table = render_template('page/PageUserLogs_Body.html', query = query)
			return jsonify(tableBody = table)
		
	return render_template("page/PageUserLogs.html", query = query, searchPhrase = searchPhrase)

@app.route('/view/<string:serial>', methods=['GET', 'POST'])
@login_required
def view(serial):
	
	error = None
	
	try:
		if request.method == 'POST' and request.form[pagePostKey] == 'updateItem':
			error = updateItem(
				oldSerial = serial,
				serialDevice = request.form['device_serial'],
				description = request.form['device_desc'],
				notes = request.form['device_notes'],
				quality = request.form['device_quality'],
				file = request.files['file']
			)
	except models.DoesNotExist:
		abort(404)
	except NameError, e:
		error = "[view] " + str(e)
	except TypeError, e:
		error = "[view] " + str(e)
	except:
		error = "[view] " + str(sys.exc_info()[0])
	return renderPage_View(serial, error = error)
		
@app.errorhandler(404)
def not_found(error):
	return render_template('page/404.html'), 404

# ~~~~~~~~~~~~~~~~ Utility ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

def addItem(serialDevice, device_type, device_other, description, notes, quality, file):
	
	serialNumber, error = models.getNextSerialNumber(device_type)
	
	if serialNumber == None:
		session['error'] = error
		return getIndexURL()
	
	if device_type == 'Other':
		device_type = device_other
	
	filename, error = uploadFile(serialNumber, file)
	if filename == None:
		return renderPage_View(serialNumber)
	
	models.Device.create(
			SerialNumber = serialNumber,
			SerialDevice = serialDevice,
			Type = device_type,
			Description = description,
			Issues = notes,
			PhotoName = filename,
			Quality = quality
		)
	return renderPage_View(serialNumber, error = error)

def updateItem(oldSerial, serialDevice, description, notes, quality, file):
	
	device = models.Device.select().where(models.Device.SerialNumber == oldSerial).get()
	
	device.SerialNumber = oldSerial
	device.SerialDevice = serialDevice
	device.Description = description
	device.Issues = notes
	device.Quality = quality
	
	filename, error = uploadFile(oldSerial, file)
	if filename != None:
		device.PhotoName = filename
	
	device.save()
	
	return error

def uploadFile(serialNumber, file):
	if file and allowed_file(file.filename):
		fileList = file.filename.split(".")
		filename = serialNumber + '.' + str(fileList[1])
		try:
			file.save(os.path.join(app.config['UPLOAD_FOLDER'], filename))
		except IOError, e:
			return (None, "[uploadFile] " + "[Errno " + str(e.errno) + "] " + str(e))
		except NameError, e:
			return (None, "[uploadFile] " + str(e))
		except:
			return (None, "[uploadFile] " + str(sys.exc_info()[0]))
	else:
		filename = None
	return (filename, None)

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
			UserOut = escape(session['username']),
			AuthorizerOut = sname,
			Purpose = use,
			DateOut = models.datetime.datetime.now()
		)
	
	return renderPage_View(serial)

# ~~~~~~~~~~~~~~~~ Start page ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

init()

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
