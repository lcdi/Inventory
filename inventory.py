from flask import Flask, render_template, url_for, redirect
from peewee import *
#from datetime import date

app = Flask(__name__)
# http://docs.peewee-orm.com/en/latest/peewee/quickstart.html
database = SqliteDatabase('developmentData.db')

#class Device(Model):

@app.route('/')
def index():
	# http://flask.pocoo.org/snippets/15/

	return render_template('inventory.html', inventoryData="", deviceLogData="")

if __name__ == '__main__':
	db.connect()
	app.run()
