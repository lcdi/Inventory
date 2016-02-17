from peewee import *

db = SqliteDatabase('developmentData.db')

class BaseModel(Model):
	class Meta:
		database = db

class Device(Model):
	idNumber = IntegerField()
	serialNumber = CharField()
	typeCategory = CharField()
	description = TextField()
	issues = TextField()
	photo = CharField()
	state = CharField()
	
def getDeviceTypes():
	types = ['ALL'];
	
	query = Device.select(Device.typeCategory).order_by(Device.typeCategory)
	for q in query:
		if (doesTypeExistIn(q.typeCategory, types) == True):
			pass
		else:
			types.append(q.typeCategory)
	
	return types
	
def doesTypeExistIn(t, types):
	for _type in types:
		if t == _type:
			return True
	return False