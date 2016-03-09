import ldap

validEditAccessGroups = ['Office Assistants', 'Domain Admins']

def checkCredentials(controller, domainA, domainB, username, password):
	if password == "":
		return ('Empty Password', False)
	
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
	
	#print(ldap_client.whoami_s())
	
	hasEditAccess = False
	dn = 'ou=Users,' + base_dn
	dn = base_dn
	#dn = 'cn=' + username + ',' + base_dn
	#print(dn)
	
	filter = 'cn=' + username
	filter = '(&(objectclass=person)(cn=%s)' % username
	filter = '(uid=*)'
	filter = 'samaccountname=' + username
	#filter = ldap_filter
	#filter = '(&(objectCategory=person)(%s))' % filter
	#filter = 'memberOf=' + validEditAccessGroups[0]
	#filter = 'cn=' + username
	#print(filter)
	
	attrs = ['memberOf']
	
	result = ldap_client.search_s(dn, ldap.SCOPE_SUBTREE, filter, attrs)
	#print(result)
	#for d1 in result:
	#	print(d1)
	groups = result[0][1]['memberOf']
	#print(groups)
	
	#return ("", "")
	#groups = ldap_client.result(id)[1][0][1]['memberOf']
	for group in groups:
		address = group.split(',')
		groupName = address[0].split('=')[1]
		if groupName in validEditAccessGroups:
			hasEditAccess = True
			break
	#print(hasEditAccess)
	
	ldap_client.unbind()
	return (True, hasEditAccess)
	
#checkCredentials("research-dc", "research", "lcdi", "dyost", "D4rKcH0c014+3$!")
#print("")
#checkCredentials("research-dc", "research", "lcdi", "amaccarone", "DiMo17*71")
