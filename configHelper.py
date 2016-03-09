
from lcdi import *

__all__ = ['getSQLDatabase', 'getSQLUsername', 'getSQLPassword']

def getSQLDatabase():
	return getConfig()['SQL_DATABASE']

def getSQLUsername():
	return getConfig()['SQL_USERNAME']

def getSQLPassword():
	return getConfig()['SQL_PASSWORD']
