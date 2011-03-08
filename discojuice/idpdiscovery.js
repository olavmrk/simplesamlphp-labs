/*
 * IdP Discovery Service
 *
 * An implementation of the IdP Discovery Protocol in Javascript
 * 
 * Author: Andreas Åkre Solberg, UNINETT, andreas.solberg@uninett.no
 * Licence: LGPLv2
 */

var IdPDiscovery = function() {

	var acl = false;
	var returnURLs = [
		'http://bridge.feide.no/'
	];
	var serviceNames = {
		'http://dev.andreas.feide.no/simplesaml/module.php/saml/sp/metadata.php/default-sp': 'Andreas Developer SP',
		'https://beta.foodl.org/simplesaml/module.php/saml/sp/metadata.php/saml': 'Foodle Beta',
		'https://foodl.org/simplesaml/module.php/saml/sp/metadata.php/saml': 'Foodle',
		'https://ow.feide.no/simplesaml/module.php/saml/sp/metadata.php/default-sp': 'Feide OpenWiki',
		'https://openwiki.feide.no/simplesaml/module.php/saml/sp/metadata.php/default-sp': 'Feide OpenWiki Administration',
		'https://rnd.feide.no/simplesaml/module.php/saml/sp/metadata.php/saml': 'Feide Rnd'
	};
	
	var query = {};
	(function () {
		var e,
			a = /\+/g,  // Regex for replacing addition symbol with a space
			r = /([^&;=]+)=?([^&;]*)/g,
			d = function (s) { return decodeURIComponent(s.replace(a, " ")); },
			q = window.location.search.substring(1);

		while (e = r.exec(q))
		   query[d(e[1])] = d(e[2]);
	})();
	
	return {
		
		"nameOf": function(entityid) {
			if (serviceNames[entityid]) return serviceNames[entityid];
			return entityid;
		},
		"getSP": function() {
			return (query.entityID || null);
		},
		"getName": function() {
			return this.nameOf(this.getSP());
		},
		"returnTo": function(e) {
			
			var returnTo = query['return'] || null;
			var returnIDParam = query.returnIDParam || 'entityID';
			if(!returnTo) {
				DiscoJuice.Utils.log('Missing required parameter [return]');
				return;
			}
			if (acl) {
				var allowed = false;
				for (var i = 0; i < returnURLs.length; i++) {
					if (returnURLs[i] == returnTo) allowed = true;
				}
				
				if (!allowed) {
					DiscoJuice.Utils.log('Access denied for return parameter [' + returnTo + ']');
					return;
				}
			}
			
			if (e.auth) {
				returnTo + '&auth=' + e.auth;
			}
			
			if (!e.entityid) {
				window.location = returnTo;
			} else {
				window.location = returnTo + '&' + returnIDParam + '=' + e.entityid;
			}
			
			

		},
		
		"receive": function() {
		
			var entityID = this.getSP();

			if(!entityID) {
				DiscoJuice.Utils.log('Missing required parameter [entityID]');
				return;
			}
			
			var preferredIdP = DiscoJuice.Utils.readCookie() || null;
			
			if (query.IdPentityID) {
				DiscoJuice.Utils.createCookie(query.IdPentityID);
				preferredIdP = query.IdPentityID;
			}
			
			var isPassive = query.isPassive || 'false';
			
			if (isPassive === 'true') {
				this.returnTo(preferredIdP);
			}
		},
		

		
		"setup": function() {
			
			var that = this;
				
			$(document).ready(function() {
				var overthere = that;
				var name = overthere.getName();
				if (!name) name = 'unknown service';
				$("a.signin").DiscoJuice({
					"title": 'Sign in to <strong>' + name + '</strong>',
					"subtitle": "Select your Provider",
					"discoPath": "discojuice/",
					"always": true,
					"overlay": true,
					"cookie": true,
					"type": false,
					"country": true,
					"countryAPI": "https://disco.uninett.no/simplesaml/module.php/ulxmeta/country.php",
					"metadata": "https://disco.uninett.no/simplesaml/module.php/ulxmeta/index.php",
					"location": false,
					"disco": {
						"spentityid": "https://foodl.org",
						"url": "https://disco.uninett.no/discojuice/discojuiceDiscoveryResponse.html?",
						"stores": [
	 						'https://foodle.feide.no/simplesaml/module.php/discopower/disco.php',
	 						'https://kalmar2.org/simplesaml/module.php/discopower/disco.php',
						],
//						'writableStore': 'https://rnd.feide.no/disco/'
					},
					"callback": function(e) {
						overthere.returnTo(e); 
					}
				});
				$("div.noscript").hide();
			});
			
		}
		
	};
}();

