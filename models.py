from peewee import *
import flask.ext.whooshalchemy
import datetime
from configHelper import *

databaseName = getSQLDatabase()
dbUser = getSQLUsername()
dbPass = getSQLPassword()
db = MySQLDatabase(databaseName, user=dbUser, password=dbPass)

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
	
def getDevicesAndLogs():
	return Device.select(Device, Log).join(Log).order_by(Device.SerialNumber)

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
	numberOfEntries = len(querySerial)
	
	lcdiPrefix = prefixStr + "-"
	lcdiPrefixLength = len(lcdiPrefix)
	typeNumber = 0
	typeNumberLength = 2
	typeNumberQuantityMAX = pow(10, typeNumberLength)
	typeNumberMAX = typeNumberQuantityMAX - 1
	itemNumber = 0
	itemNumberLength = 2
	itemNumberQuantityMAX = pow(10, itemNumberLength)
	itemNumberMAX = itemNumberQuantityMAX - 1
	
	# No items of type
	if numberOfEntries <= 0:
		typeNumber = len(getDeviceTypes())
		if typeNumber > typeNumberLength:
			return (None, "OVERFLOW ERROR: Too many types")
	# Items of type found
	else:
		typeNumber = querySerial.get().SerialNumber[lcdiPrefixLength : lcdiPrefixLength + typeNumberLength]
		
		if numberOfEntries >= itemNumberQuantityMAX:
			return (None, "OVERFLOW ERROR: Too many items for type " + device_type)
		# Less than maximum quantity of items for type
		else:
			lastSerial = querySerial.get().SerialNumber
			lastSerial_itemNumber = int(lastSerial[len(lastSerial)-2:])
			
			if lastSerial_itemNumber == numberOfEntries:
				i = 0
				for device in querySerial.order_by(Device.SerialNumber):
					i_itemNumber = int(device.SerialNumber[lcdiPrefixLength + typeNumberLength:])
					if i_itemNumber != i:
						itemNumber = i
						break
					i += 1
			else:
				itemNumber = int(lastSerial[len(lastSerial)-2:]) + 1
			
			if itemNumber > itemNumberMAX:
				return (None, "OVERFLOW ERROR: All serials used up")

	return (lcdiPrefix + str(typeNumber).zfill(typeNumberLength) + str(itemNumber).zfill(itemNumberLength), None)

def getDeviceLog(serial):
	return Log.select().where(Log.SerialNumber == serial).order_by(-Log.Identifier)
	
def isSearchUser(user):
	
	query = Log.select().where(Log.AuthorizerIn == user | Log.AuthorizerOut == user | Log.UserIn == user | Log.UserOut == user)
	
	for log in query:
		if log.AuthorizerIn == user:
			return True
		elif log.AuthorizerOut == user:
			return True
		elif log.UserIn == user:
			return True
		elif log.UserOut == user:
			return True
	
	return False