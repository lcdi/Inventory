from flask import Flask, render_template, url_for, redirect

app = Flask(__name__)

@app.route('/')
def index():
	return render_template('index.html')

if __name_ == '__main__':
	app.debug = True
	app.run()
