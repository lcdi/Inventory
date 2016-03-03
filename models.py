from peewee import *
import flask.ext.whooshalchemy
import datetime

db = MySQLDatabase('inventory', user="root", password="root")

class BaseModel(Model):
	class Meta:
		database = db

class Device(Model):

	__searchable__ = [
		'SerialNumber',
		'SerialDevice'
		'Type',
		'Description',
		'Issues',
		'PhotoName',
		'Quality'
	]

	SerialNumber = CharField(primary_key=True)
	SerialDevice = CharField()
	Type = CharField()
	Description = TextField()
	Issues = TextField()
	PhotoName = CharField()
	Quality = CharField()
	
	class Meta:
		database = db
	
class Log(Model):
	
	Identifier = PrimaryKeyField()
	SerialNumber = ForeignKeyField(Device, db_column='SerialNumber')
	Purpose = TextField()
	UserOut = CharField()
	DateOut = DateTimeField()
	AuthorizerOut = CharField()
	UserIn = CharField()
	DateIn = DateTimeField()
	AuthorizerIn = CharField()

	class Meta:
		database = db

def getDeviceTypes():
	types = [];

	query = Device.select(Device.Type).order_by(Device.Type)
	for q in query:
		if (doesEntryExist(q.Type, types) == True):
			pass
		else:
			types.append(q.Type)

	return types
	
def getStates():
	states = ["Operational"];

	query = Device.select(Device.Quality).order_by(Device.Quality)
	for q in query:
		if (doesEntryExist(q.Quality, states) == True):
			pass
		else:
			states.append(q.Quality)

	return states

def doesEntryExist(x, arr):
	for a in arr:
		if x == a:
			return True
	return False

def getDevices():
	return Device.select(
		Device.SerialNumber,
		Device.Type,
		Device.Description,
		Device.Issues
	).order_by(Device.SerialNumber)

def getDevicesWithLog(itemType, status, quality):
	query = getDevices().where(
		(Device.Type == itemType if itemType != 'ALL' else Device.Type != ''),
		(Device.Quality == quality if quality != 'ALL' else Device.Quality != '')
	)
	return getDeviceAndLogListForQuery(query, status)

def getDeviceAndLogListForQuery(query, status = 'ALL'):
	deviceList = []
	
	for device in query:
		device.log = getDeviceLog(device.SerialNumber)
		
		device.statusIsOut, device.log = getStatus(device.log)
		
		if status == 'ALL':
			deviceList.append(device)
		elif status == 'in' and not device.statusIsOut:
			deviceList.append(device)
		elif status == 'out' and device.statusIsOut:
			deviceList.append(device)
	
	return deviceList

def getStatus(log):
	hasLog = len(log) > 0
	if hasLog:
		log = log.get()
		return (not log.DateIn, log)
	else:
		return (False, log)

def getNextSerialNumber(device_type):
	prefixStr = "LCDI"
	
	querySerial = Device.select(Device.SerialNumber).where(Device.Type == device_type).order_by(-Device.SerialNumber)
	nextSerial = prefixStr + "-"
	
	numberOfEntries = len(querySerial)
	prefix = 0
	
	if numberOfEntries <= 0:
		prefix = len(getDeviceTypes())
	else:
		prefix = querySerial.get().SerialNumber[len(nextSerial)]
	
	prefix = str(prefix).zfill(2)
	
	if numberOfEntries < 99:
		lastSerial = querySerial.get().SerialNumber
		lastSerialOfType = int(lastSerial[len(lastSerial)-2:]) + 1
		nextSerial += prefix + str(lastSerialOfType).zfill(2)
	else:
		return "OVERFLOW ERROR (MORE THAN 100 ITEMS PER TYPE)"
	
	return nextSerial

def getDeviceLog(serial):
	return Log.select().where(Log.SerialNumber == serial).order_by(-Log.Identifier)
