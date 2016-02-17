import ldap

validEditAccessGroups = ['Office Assistants', 'Domain Admins']

def checkCredentials(username, password):
	if password == "":
		return ('Empty Password', False)
	
	controller = 'devdc'
	domainA = 'dev'
	domainB = 'devlcdi'
	domain = domainA + '.' + domainB
	
	ldapServer = 'ldap://' + controller + '.' + domain
	ldapUsername = username + '@' + domain
	ldapPassword = password
	
	base_dn = 'DC=' + domainA + ',DC=' + domainB
	ldap_filter = 'userPrincipalName=' + ldapUsername
	
	# Note: empty passwords WILL validate with ldap
	try:
		ldap_client = ldap.initialize(ldapServer)
		ldap_client.set_option(ldap.OPT_REFERRALS, 0)
		ldap_client.simple_bind_s(ldapUsername, ldapPassword)
	except ldap.INVALID_CREDENTIALS:
		ldap_client.unbind()
		return ('Wrong Credentials', False)
	except ldap.SERVER_DOWN:
		return ('Server Down', False)
	
	hasEditAccess = False
	dn = 'cn=Users,' + base_dn
	filter = 'cn=' + str(username)
	attrs = ['memberOf']
	id = ldap_client.search(dn, ldap.SCOPE_SUBTREE, filter, attrs)
	groups = ldap_client.result(id)[1][0][1]['memberOf']
	for group in groups:
		address = group.split(',')
		groupName = address[0].split('=')[1]
		if groupName in validEditAccessGroups:
			hasEditAccess = True
			break
	
	ldap_client.unbind()
	return (True, hasEditAccess)
	