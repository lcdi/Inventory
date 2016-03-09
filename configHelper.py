
from lcdi import *

__all__ = ['getSQLDatabase', 'getSQLUsername', 'getSQLPassword']

def getSQLDatabase():
	return app.config['SQL_DATABASE']

def getSQLUsername():
	return app.config['SQL_USERNAME']

def getSQLPassword():
	return app.config['SQL_PASSWORD']
