/**
 * @author diogenes
 */
	function offline_access() {
		this.login = null;
		this.pass = null;
		this.access = null;
	}

	offline_access.prototype.init = function (login,pass,access){
		this.login = login;
		this.pass = pass;
		this.access = access;
	}
	
	offline_access.prototype.fill_combo_of_users = function(users_combo) { 
		
		var users = expresso_local_messages.get_all_users();

		for(var i in users) {
					
			var option = document.createElement('option');
			option.value = i;
			option.text = users[i];
			try {
				users_combo.add(option, null);
			} 
			catch (ex) {//I.E
				users_combo.add(option);
			}

		}
	}
	
	offline_access.prototype.do_login = function(uid_usuario,pass) {
		control = expresso_local_messages.set_as_logged(uid_usuario,pass,false);
		if(!control) {
			document.getElementById('div_error').innerHTML = 'login ou senha inválida';
		}
		else {
			document.location.href = 'offline.php';
		}
		
	}
	
	offline_access.prototype.has_permition = function() {
		var user_logged = expresso_local_messages.user_logged();
		var d = new Date();

		if(user_logged==null || d.getTime()>=user_logged[1]+60000) {
			document.location.href = 'login_offline.php';
		}
		else	
			account_id = user_logged[0];
		
	}

	offline_access.prototype.do_logoff = function() {
		expresso_local_messages.unset_as_logged();
		location.href="login_offline.php"
	}
	

	var expresso_offline_access;
	expresso_offline_access = new offline_access();