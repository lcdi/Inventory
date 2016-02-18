from peewee import *
import flask.ext.whooshalchemy
import datetime

db = MySQLDatabase('inventory', user="root", password="")

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
	
	Identifier = IntegerField()
	SerialNumber = ForeignKeyField(Device)
	UserIdentifier = CharField()
	Purpose = TextField()
	DateOut = DateTimeField(default=datetime.datetime.now)
	DateIn = DateTimeField(default=datetime.datetime.now)
	AuthorizerIdentifier = CharField()
	
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


