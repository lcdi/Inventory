from flask import Flask, render_template, url_for, redirect
from peewee import *

app = Flask(__name__)
database = SqliteDatabase('developmentData.db')

#class Device(Model):

@app.route('/')
def index():
	# http://flask.pocoo.org/snippets/15/

	return render_template('inventory.html', inventoryData="", deviceLogData="")

if __name__ == '__main__':
	db.connect()
	app.run()
