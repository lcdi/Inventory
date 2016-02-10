import ldap

def areCredentialsValid(username, password):
	ret = checkCredentials(username, password)
	print(ret)
	return ret == None

def checkCredentials(username, password):
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
	return None
	