import ldap

def checkCredentials(username, password):
	if password == "":
		return 'Empty Password'
	
	controller = 'devdc'
	domainA = 'dev'
	domainB = 'devlcdi'
	domain = domainA + '.' + domainB
	
	ldapServer = 'ldap://' + controller + '.' + domain
	ldapUsername = username + '@' + domain
	ldapPassword = password
	
	base_dn = 'DC=' + domainA + ',DC=' + domainB
	ldap_filter = 'userPrincipalName=' + ldapUsername
	attrs = ['memberOf']
	
	# Note: empty passwords WILL validate with ldap
	try:
		ldap_client = ldap.initialize(ldapServer)
		ldap_client.set_option(ldap.OPT_REFERRALS, 0)
		ldap_client.simple_bind_s(ldapUsername, ldapPassword)
	except ldap.INVALID_CREDENTIALS:
		ldap_client.unbind()
		return 'Wrong Credentials'
	except ldap.SERVER_DOWN:
		return 'Server Down'
	ldap_client.unbind()
	return True
	