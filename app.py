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
UPLOAD_FOLDER = 'static/item_photos'
ALLOWED_EXTENSIONS = set(['png', 'jpg', 'jpeg', 'gif', 'PNG', 'JPG', 'JPEG', 'GIF'])

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
	print(serialNumber)
	print(itemType)
	print(state)
	print(status)
	
	query = models.Device.select().order_by(models.Device.serialNumber)
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

def renderEntry(function, serialNumber):
	# TODO remake entry files
	return getIndexURL()
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

# ~~~~~~~~~~~~~~~~ Routing Functions ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

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
	

# ~~~~~~~~~~~~~~~~ New Functions: TODO sort ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

@app.route('/search', methods=['POST'])
def search():
	if request.method == 'POST':
		search = request.form['searchField']
	if search != "":
		return redirect(url_for('search_results', search=search))
	else:
		return redirect(url_for('index'))



@app.route('/search_results/<search>')
def search_results(search):
	#query = models.Device.query.whoosh_search(search)

	query = models.Device.select(models.Device.serialNumber,
								 models.Device.typeCategory,
								 models.Device.description,
								 models.Device.issues,
								 models.Device.state
								 ).where(models.Device.serialNumber.contains(search) | 
								 		 models.Device.typeCategory.contains(search) |
								 		 models.Device.description.contains(search)).order_by(models.Device.serialNumber)

	types = models.getDeviceTypes()
	return render_template('searchResults.html',
			query=query,
			types=types,
			logoutURL=url_for('logout')
		)
		
@app.route('/viewItem/<serial>')
def viewItem(serial):
	item = models.Device.select().where(models.Device.serialNumber == serial)
	types = models.getDeviceTypes()
	states = models.getStates()
	return render_template('viewItem.html',
			item=item,
			types=types,
			states=states,
			logoutURL=url_for('logout')
		)
		
@app.route('/', methods=['POST'])
def addItem():
	if request.method == 'POST':
		
		idNum = models.Device.select().order_by(models.Device.idNumber.desc()).get()
		
		if request.form['device_types'] == 'Other':
			device_type = request.form['other']
		else:
			device_type = request.form['device_types']
			
		file = request.files['file']
        if file and allowed_file(file.filename):
            filename = secure_filename(file.filename)
            file.save(os.path.join(app.config['UPLOAD_FOLDER'], filename))
			
	models.Device.create(
			idNumber = idNum.idNumber + 1,
			serialNumber = request.form['lcdi_serial'],
			typeCategory = device_type,
			description = request.form['device_desc'],
			issues = request.form['device_notes'],
			photo = file.filename,
			state = request.form['device_state']
		)
		
	return redirect(url_for('viewItem', serial=request.form['lcdi_serial']))
	
@app.route('/deleteItem/<serial>')
def deleteItem(serial):
	
	item = models.Device.select().where(models.Device.serialNumber == serial).get();
	item.delete_instance();
	return redirect(url_for('index'))
	
@app.route('/updateItem/<serial>', methods=['POST'])
def updateItem(serial):
	
	if request.method == 'POST':
		
		item = models.Device.select().where(models.Device.serialNumber == serial).get()
		
		if request.form['device_types'] == 'Other':
			device_type = request.form['other']
		else:
			device_type = request.form['device_types']
			
			
		file = request.files['file']
        if file and allowed_file(file.filename):
            filename = secure_filename(file.filename)
            file.save(os.path.join(app.config['UPLOAD_FOLDER'], filename))
			
			
	item.serialNumber = request.form['lcdi_serial']
	item.typeCategory = device_type
	item.description = request.form['device_desc']
	item.issues = request.form['device_notes']
	if file:
		item.photo = file.filename
	item.state = request.form['device_state']
	
	item.save()
	
	return redirect(url_for('viewItem', serial=item.serialNumber))
	

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
	
	"""models.Device.create_table()
	models.InOut.create_table()"""
	
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
	
	models.db.close()
