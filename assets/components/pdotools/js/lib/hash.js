pdoHash = {
	get: function() {
		var vars = {}, hash, splitter, hashes;
		if (!this.oldbrowser()) {
			var pos = window.location.href.indexOf('?');
			hashes = (pos != -1) ? decodeURIComponent(window.location.href.substr(pos + 1)) : '';
			splitter = '&';
		}
		else {
			hashes = decodeURIComponent(window.location.hash.substr(1));
			splitter = '/';
		}

		if (hashes.length == 0) {return vars;}
		else {hashes = hashes.split(splitter);}

		for (var i in hashes) {
			if (hashes.hasOwnProperty(i)) {
				hash = hashes[i].split('=');
				if (typeof hash[1] == 'undefined') {
					vars['anchor'] = hash[0];
				}
				else {
					vars[hash[0]] = hash[1];
				}
			}
		}
		return vars;
	}

	,set: function(vars) {
		var hash = '';
		for (var i in vars) {
			if (vars.hasOwnProperty(i)) {
				hash += '&' + i + '=' + vars[i];
			}
		}

		if (!this.oldbrowser()) {
			if (hash.length != 0) {
				hash = '?' + hash.substr(1);
			}
			window.history.pushState({pdoPage: document.location.pathname + hash}, '', document.location.pathname + hash);
		}
		else {
			window.location.hash = hash.substr(1);
		}
	}

	,add: function(key, val) {
		var hash = this.get();
		hash[key] = val;
		this.set(hash);
	}

	,remove: function(key) {
		var hash = this.get();
		delete hash[key];
		this.set(hash);
	}

	,clear: function() {
		this.set({});
	}

	,oldbrowser: function() {
		return !(window.history && history.pushState);
	}
};