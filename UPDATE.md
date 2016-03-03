Commands to install:

sudo -i
pip install flask
pip install flask_whooshalchemy
pip install peewee
apt-get install python-dev libssl-dev
apt-get update && apt-get install -y libldap2-dev
apt-get update && apt-get install -y libsasl2-dev
sudo pip install python-ldap
exit

sudo apt-get install mysql-server
sudo service mysql start
sudo mysql -p
<empty password>
set password = PASSWORD('root');
quit;

sudo mysql -p < inventory_schema.sql
