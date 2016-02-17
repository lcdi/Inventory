from peewee import *
import flask.ext.whooshalchemy
import datetime

db = SqliteDatabase('Inventory.db')

class BaseModel(Model):

	class Meta:
		database = db

class Device(Model):

	__searchable__ = ['serialNumber', 'typeCategory', 'description', 'issues', 'photo', 'state']

	idNumber = IntegerField()
	serialNumber = CharField()
	typeCategory = CharField()
	description = TextField()
	issues = TextField()
	photo = CharField()
	state = CharField()
	
	class Meta:
		database = db
	
class InOut(Model):
	
	idNumber = ForeignKeyField(Device)
	studentName = CharField()
	use = CharField()
	dateIn = DateTimeField(default=datetime.datetime.now)
	dateOut = DateTimeField(default=datetime.datetime.now)
	userIn = CharField()
	userOut = CharField()
	issues = CharField()
	
	class Meta:
		database = db
	

def getDeviceTypes():
	types = [];

	query = Device.select(Device.typeCategory).order_by(Device.typeCategory)
	for q in query:
		if (doesEntryExist(q.typeCategory, types) == True):
			pass
		else:
			types.append(q.typeCategory)

	return types
	
def getStates():
	states = [];

	query = Device.select(Device.state).order_by(Device.state)
	for q in query:
		if (doesEntryExist(q.state, states) == True):
			pass
		else:
			states.append(q.state)

	return states

def doesEntryExist(x, arr):
	for a in arr:
		if x == a:
			return True
	return False


