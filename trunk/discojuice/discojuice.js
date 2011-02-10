/*
 * jQuery doTimeout: Like setTimeout, but better! - v1.0 - 3/3/2010
 * http://benalman.com/projects/jquery-dotimeout-plugin/
 * 
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function($){var a={},c="doTimeout",d=Array.prototype.slice;$[c]=function(){return b.apply(window,[0].concat(d.call(arguments)))};$.fn[c]=function(){var f=d.call(arguments),e=b.apply(this,[c+f[0]].concat(f));return typeof f[0]==="number"||typeof f[1]==="number"?this:e};function b(l){var m=this,h,k={},g=l?$.fn:$,n=arguments,i=4,f=n[1],j=n[2],p=n[3];if(typeof f!=="string"){i--;f=l=0;j=n[1];p=n[2]}if(l){h=m.eq(0);h.data(l,k=h.data(l)||{})}else{if(f){k=a[f]||(a[f]={})}}k.id&&clearTimeout(k.id);delete k.id;function e(){if(l){h.removeData(l)}else{if(f){delete a[f]}}}function o(){k.id=setTimeout(function(){k.fn()},j)}if(p){k.fn=function(q){if(typeof p==="string"){p=g[p]}p.apply(m,d.call(n,i))===true&&!q?o():e()};o()}else{if(k.fn){j===undefined?e():k.fn(j===false);return true}else{e()}}}})(jQuery);


// Making sure that console.log does not throw errors on Firefox + IE etc.
if (typeof console == "undefined") var console = { log: function() {} };


/*
 * DiscoJuice
 *  Work is based upon mock up made by the Kantara ULX group.
 * 
 * Author: Andreas Åkre Solberg, UNINETT, andreas.solberg@uninett.no
 * Licence undecided. Awaiting alignment with the licence of the origin Kantara mockup.
 */
var DiscoJuice = {};



DiscoJuice.fetchMetadata = function () {
	if (DiscoJuice.data) return null;
	var metadataurl = DiscoJuice.options.get('metadata');
	
	DiscoJuice.log('metadataurl is ' + metadataurl);
	if (!metadataurl) return;
	
	$.getJSON(metadataurl, function(data) {
		DiscoJuice.data = data;
		DiscoJuice.log('Successfully loaded metadata');
		DiscoJuice.postMetadata();
	});

}


/* 
	An object that lets to request values of options by key
	and return a default value if no specific option is set.
	*/
DiscoJuice.prepareOptions = function (options) {
	return {
		"get": function (key, def) {
//			DiscoJuice.log(options);
//			DiscoJuice.log('Getting [' + key + '] default [' + def + '] val [' + options[key] + ']');
			if (!options) return def;
			if (!options[key]) return def;
			return options[key];
		}
	};
};

DiscoJuice.log = function(string) {
	console.debug(string);
	// opera.postError(string);
}

DiscoJuice.selectProvider = function(entityid) {
	var callback;
	
	var mustwait = DiscoJuice.discoWrite(entityid);
	
	if (DiscoJuice.options.get('cookie', false)) {
		DiscoJuice.log('COOKIE write ' + entityid);
		DiscoJuice.createCookie(entityid);		
	}

	if (DiscoJuice.options.get('callback')) {
		callback = DiscoJuice.options.get('callback');
		
		if (mustwait) {
			$.doTimeout(1000, function(){
				callback(entityid);
			});
			
		} else {
			callback(entityid);
		}
		
		return;
	}
};


DiscoJuice.popuphtml = 
	'<dl id="discojuice">' +
		'<dt>' +
			'<span id="maintitle"></span>' +
			'<span id="subtitle"></span>' +
			'<a href="#" id="close">Close</a>' +
		'</dt>' +
		'<dd id="content" style="">' +
			'<p id="moretext"></p>' +
			'<div id="scroller"></div>' +
		'</dd>' +


		'<dd id="search" class="" >' +
			'<p><input type="search" id="ulxSearchField" results=5 autosave=ulxsearch name="searchfield" placeholder="or search for a provider, in example Univerity of Oslo" value="" /></p>' +
			'<div id="whatisthis" style="margin-top: 15px; font-size: 11px;">' +
				'<a href="#" id="what">Help me, I cannot find my provider</a>' +
				'<p id="whattext">If your institusion is not connected to Foodle, you may either select to login one of the commercial providers such as Facebook or Google, or you may create a new account using any of the Guest providers, such as Feide OpenIdP.</p>' +
			'</div>' +
		'</dd>' +
		
		'<dd id="filters" class="bottom">' +
			'<p id="filterCountry"></p>' +
			'<p id="filterType"></p>' +
			'<p id="showall"><a href="">Show all providers</a></p>' +
		'</dd>' +



		'<dd id="locatemediv">' +
			'<img style="float: left; margin-right: 5px" src="ulx/images/target.png" alt="locate me..." />' +
			'<p style="margin-top: 10px"><a id="locateme" href="">' +
				'Locate me</a> to show providers nearby' +
			'</p>' +
			'<p style="color: #999" id="locatemeinfo"></p>' +
			'<div style="clear: both" >' +
			'</div>' +

		'</dd>' +
		

	'</dl>';


DiscoJuice.discoResponse = function(entityid) {

	if(entityid) {
		for(i = 0; i < DiscoJuice.data.length; i++) {
			// DiscoJuice.log(DiscoJuice.data[i].entityid);
			if (DiscoJuice.data[i].entityid == entityid) {
				if (isNaN(DiscoJuice.data[i].weight)) DiscoJuice.data[i].weight = 0;
				DiscoJuice.data[i].weight -= 100;
				DiscoJuice.log('COOKIE Setting weight to ' + DiscoJuice.data[i].weight);
			}
		}
	}	
	DiscoJuice.listResults(entityid);
}



DiscoJuice.discoWrite = function(e) {
	
	var settings = DiscoJuice.options.get('disco');
	if (!settings) return false;
	if (!settings.writableStore) return false;

	var html = '';
	var returnurl = settings.url;
	var spentityid = settings.spentityid;
	var writableStore = settings.writableStore;
		
	iframeurl = writableStore + '?entityID=' + escape(spentityid) + '&IdPentityID=' + 
		escape(e) + '&isPassive=true&returnIDParam=bogus&return=' + escape(returnurl);
		
	html = '<iframe src="' + iframeurl + '" style="display: none"></iframe>';
	$(DiscoJuice.control).parent().after(html);
	return true;
}


DiscoJuice.discoReadSetup = function() {
	
	var settings = DiscoJuice.options.get('disco');
	if (!settings) return;

	var html = '';
	var returnurl = settings.url;
	var spentityid = settings.spentityid;
	var stores = settings.stores;
	var i;
	var currentStore;
	
	for(i = 0; i < stores.length; i++) {
		currentStore = stores[i];
		
		iframeurl = currentStore + '?entityID=' + escape(spentityid) + '&isPassive=true&returnIDParam=entityID&return=' + escape(returnurl);
		
		html = '<iframe src="' + iframeurl + '" style="display: none"></iframe>';
		$(DiscoJuice.control).parent().after(html);
	}
	
}



DiscoJuice.postMetadata = function() {

	if (DiscoJuice.options.get('cookie', false)) {
		var selected = DiscoJuice.readCookie();
		DiscoJuice.log('COOKIE read ' + selected);
		if(selected) {
			for(i = 0; i < DiscoJuice.data.length; i++) {
				// DiscoJuice.log(KULX.data[i].entityid);
				if (DiscoJuice.data[i].entityid == selected) {
					if (isNaN(DiscoJuice.data[i].weight)) DiscoJuice.data[i].weight = 0;
					DiscoJuice.data[i].weight -= 100;
					DiscoJuice.log('COOKIE Setting weight to ' + DiscoJuice.data[i].weight);
				}
			}
		}
	}
	DiscoJuice.discoReadSetup();
	DiscoJuice.listResults();	
}


/*
	The embrace function will take a sign in link as a parameter, and it will prepare the whole
	setup. It will add action listeners, and prepare the popup.
	*/
DiscoJuice.embrace = function(control, options) {
	var i;
	
	
	if($.browser.msie && jQuery.browser.version < 7.0) return;
	
	DiscoJuice.control = control;

	DiscoJuice.options = DiscoJuice.prepareOptions(options);	

	$(control).parent().after(DiscoJuice.popuphtml);

	

	

	

	// Hide the popup window
	$("#discojuice").hide();

	// Add a listener to the sign in button.
	$(control).click(function(event) {
		event.preventDefault();
		DiscoJuice.ulxControlClick();
		return false;
	});

	// Add listeners to the close button.
	$("#close, #overlay").click(function() {
		DiscoJuice.ulxClose();
	});

	// Add toogle for what is this text.
	$("#what").click(function() {
		$("#whatisthis").toggleClass("show");
	});

	
	// Add listener to show all providers button.
	$("p#showall a").click(function(event){
		event.preventDefault();
		$("select#filterCountrySelect").val('all');	
		DiscoJuice.listResults(true);
		$("p#showall").hide();
	});
	$("p#showall").hide();
	
	//locateMe();

	// Setup filter by type.
	if (DiscoJuice.options.get('location', false) && navigator.geolocation) {
		$("#locateme").click(function(event) {
			event.preventDefault();
			DiscoJuice.locateMe();
		});
	} else {
		$("dd#locatemediv").hide();
	}	


	// Setup filter by type.
	if (DiscoJuice.options.get('type', false)) {
		DiscoJuice.filterTypeSetup();
	}


	// Setup filter by country.
	if (DiscoJuice.options.get('country', false)) {
		DiscoJuice.filterCountrySetup();
	}
	
	
	// If countryAPI is set, then lookup by IP.
	var countryapi;
	if (countryapi = DiscoJuice.options.get('countryAPI', false)) {
		
		var countrycache = DiscoJuice.readCookie('Country');
	
		if (countrycache) {
			
			DiscoJuice.filterCountrySetup(countrycache);
			DiscoJuice.log('Found country in cache: ' + countrycache);
			
		} else {
			
			$.getJSON(countryapi, function(data) {
	//			DiscoJuice.log(data);
				if (data.status == 'ok' && data.country) {
					DiscoJuice.createCookie(data.country, 'Country');
					DiscoJuice.filterCountrySetup(data.country);
					DiscoJuice.log('Country lookup succeeded: ' + data.country);
				} else {
					DiscoJuice.log('Country lookup failed: ' + (data.error || ''));
				}
			});
		
		}
	}
	
		
	if (DiscoJuice.options.get('location', false)) {
		$("#locateme").click(function(event) {
			event.preventDefault();
			DiscoJuice.locateMe();
		});
	} else {
		$("dd#locatemediv").hide();
	}	
	
	/*
		Initialise the search box.
		*/
	$("input#ulxSearchField").autocomplete({
		minLength: 2,
		source: function( request, response ) {
			var term = request.term;
			var result;
			
			$("select#filterCountrySelect").val('all');
						
//			$("dd#content img.spinning").show();
			DiscoJuice.listResults();
//			$("dd#content img.spinning").hide();
		}
	});

	// List the initial results...
	// DiscoJuice.listResults();

};



DiscoJuice.createCookie = function(value, type) {
	var type = type || 'EntityID';
	var name = '_DiscoJuice_' + type;
	var days = 1825;
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+escape(value)+expires+"; path=/";
}

DiscoJuice.readCookie = function(type) {
	var type = type || 'EntityID';
	var name = '_DiscoJuice_' + type;
	var days = 1825;
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return unescape(c.substring(nameEQ.length,c.length));
	}
	return null;
}

DiscoJuice.eraseCookie = function (type) {
	var type = type || 'EntityID';
	var name = '_DiscoJuice_' + type;
	DiscoJuice.createCookie(name,"",-1);
}








DiscoJuice.ulxClose = function() {
	$("#overlay").fadeOut("slow");
	$("#discojuice").fadeOut("fast");
	$("#scroller a").removeAttr("style");
};


DiscoJuice.ulxControlClick = function () {
	$("#overlay").fadeIn("fast");
	$("#discojuice").fadeIn("slow");

	$("#maintitle").html("Sign in to <strong>Foodle</strong>");
	$("#subtitle").html("with one of the providers below");
	$("#content2, #more, #moretext").hide();
	$("#search, #whatisthis").show();

	$("input#ulxSearchField").focus();
	
	DiscoJuice.log('Fetching metadata...');
	DiscoJuice.fetchMetadata();

	return false;
};

// calculate distance between two locations
DiscoJuice.calculateDistance = function (lat1, lon1, lat2, lon2) {
	var R = 6371; // km
	var dLat = KULX.toRad(lat2-lat1);
	var dLon = KULX.toRad(lon2-lon1); 
	var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
			Math.cos(KULX.toRad(lat1)) * Math.cos(KULX.toRad(lat2)) * 
			Math.sin(dLon/2) * Math.sin(dLon/2); 
	var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
	var d = R * c;
	return d;
};

DiscoJuice.searchMatch = function (item, term) {
	if (item.title.toLowerCase().search(term.toLowerCase()) !== -1) return true;
	var key, i, keyword;
	
	if (item.keywords) {
		for(key in item.keywords) {
			keyword = item.keywords[key];
			for(i = 0; i < keyword.length; i++) {
				if (keyword[i].toLowerCase().search(term.toLowerCase()) !== -1) return keyword[i];
			}
		}
	}
	return false;
};

DiscoJuice.toRad = function (deg) {
	return deg * Math.PI/180;
};

DiscoJuice.calculateDistanceEntry = function (current, lat, lon) {
	var key, location;
	
	var distanceMemory = {};
	var distance;
	
// 	DiscoJuice.log('locations');
// 	DiscoJuice.log(current.locations);
	
	if (current.locations) {
		for(key in current.locations) {
			location = current.locations[key];
			
			distance = DiscoJuice.calculateDistance(lat, lon, location.lat, location.lon);
			
			if(!distanceMemory.distance || distanceMemory.distance > distance) {
				distanceMemory = {
					'distance': distance,
					'location': key
				};
			}			
		}
	}
	if (distanceMemory.distance) {
		current.nearby = {
			'distance': distanceMemory.distance,
			'distanceH': Math.round(distanceMemory.distance*10)/10 + ' km',
			'location': distanceMemory.location
		};
	}
	
};


DiscoJuice.getItemText = function (current, substring) {

	var textLink = '';
	var classes = '';
	if (current.weight < -50) classes += 'hothit';
	
	if (current.icon) {
		if (!substring) {
			textLink += '<a href="" class="' + classes + '" rel="' + escape(current.entityid) + '" title="' + current.title + '">' + 
				'<img src="' + current.icon + '" />' +
				'<span class="title">' + current.title + '</span><hr style="clear: both; height: 0px; visibility:hidden" /></a>';
		} else {
			textLink += '<a href="" class="' + classes + '" rel="' + escape(current.entityid) + '" title="' + current.title + '">' + 
				'<img src="' + current.icon + '" />' +
				'<span class="title">' + current.title + '</span>' + 
				'<span class="substring">' + substring + '</span>' +
				'<hr style="clear: both; height: 0px; visibility:hidden" /></a>';
					}
	} else {
		if (!substring) {
			textLink += '<a href="" class="' + classes + '" rel="' + escape(current.entityid) + '"><span class="title">' + current.title + '</span></a>';		
		} else {
			textLink += '<a href="" class="' + classes + '" rel="' + escape(current.entityid) + '"><span class="title">' + current.title + '</span><span class="substring">' + substring + '</span></a>';					
		}

	}
	return textLink;
};



DiscoJuice.findNearby = function (lat, lon) {
	var i, current;
	var hits = 0;
	var textIcon = '';
	var textLink = '';
	var textRes;
	
	var distanceList = [];

	for(i = 0; i < DiscoJuice.data.length; i++) {
		current = DiscoJuice.data[i];

		DiscoJuice.calculateDistanceEntry(current, lat, lon);
// 		DiscoJuice.log('Show object: ' );
// 		DiscoJuice.log(current);
	}
	
	DiscoJuice.data.sort(function(a, b) {
// 		DiscoJuice.log('Distance sort');
// 		DiscoJuice.log(a);
		if (!a.nearby || !a.nearby.distance) return 0;
		if (!b.nearby || !b.nearby.distance) return 0;
// 		DiscoJuice.log('Comparing ' + a.nearby.distance + ' with ' + b.nearby.distance);
		return (a.nearby.distance - b.nearby.distance);
	});
	
	for(i = 0; i < DiscoJuice.data.length; i++) {
		current = DiscoJuice.data[i];
		
		if (!current.nearby) continue;
		
		hits++;
		
		if (hits > 30) break;
		
		textRes = DiscoJuice.getItemText(current, current.nearby.distanceH + ', ' + current.nearby.location);

		textIcon += textRes[0];
		textLink += textRes[1];

	}
	if (textLink) textLink = '<ul>' + textLink + '</ul>';
	
	$("div#scroller").empty().append(textIcon);
//	$("div#scrollerText").empty().append(textLink);

};

// The function that prepares the list of result, based upon
// eigther a search term or a category selection - or both
//  - term; a search term, may be undefined to match all
//  - categories; an object specifying 'category': 'value'.
//
// Example of use listResults('school', {'country': 'norway'})
DiscoJuice.listResults = function (showall) {
	var i, hits, current, search;
	var maxhits = 6;
	
	var term = $("input#ulxSearchField").val();
	var categories = DiscoJuice.getCategories();

	//log('Searching from ' + DiscoJuice.data.length + ' accounts to select from');

	var textIcon = '';
	
	if (!DiscoJuice.data) return;
	
	
	DiscoJuice.data.sort(function(a, b) {
		var xa, xb;		
		xa = (a.weight ? a.weight : 0);
		xb = (b.weight ? b.weight : 0);
		return (xa-xb);
	});
	
	if (term || categories) {
		$("p#showall").show();
	}
	if (categories) {
		maxhits = 25;
	}
	if (term) {
		maxhits = 10;
	}



	hits = 0;
	for(i = 0; i < DiscoJuice.data.length; i++) {
		current = DiscoJuice.data[i];
		if (!current.weight) current.weight = 0;
		
		if (term) {
			search = DiscoJuice.searchMatch(current,term);
			if (search === false && current.weight > -50) continue;
		} else {
			search = null;
		}
		
		if (categories && categories.country) {
			if (!current.country) continue;
			if (categories.country !== current.country && current.weight > -50) continue;
		}
		if (categories && categories.type) {
			if (!current.ctype && current.weight > -50) {
//				DiscoJuice.log(current);
			continue;
			}
//			DiscoJuice.log(current.title + ' category ' + current.ctype);
			if (categories.type !== current.ctype && current.weight > -50) continue;
		}
		
		if (++hits > maxhits && showall !== true) {
			$("p#showall").show();
			break;
		}
		
// 		DiscoJuice.log('Accept: ' + current.title);
		
		if (search === true) {
			if (current.descr) {
				textIcon += DiscoJuice.getItemText(current, current.descr);
			} else if (current.country) {
				textIcon += DiscoJuice.getItemText(current, current.country);
			} else {
				textIcon += DiscoJuice.getItemText(current);
			}
			
		} else {
			textIcon += DiscoJuice.getItemText(current, search);
		}
	}
	
	$("div#scroller").empty().append(textIcon);
	$("div#scroller a").each(function() {
		$(this).click(function(event) {
			event.preventDefault();
			DiscoJuice.ulxClose();
			var entityid = unescape($(this).attr('rel'));
			DiscoJuice.selectProvider(entityid);
		});
	});
	
	//log('Loaded ' + DiscoJuice.data.length + ' accounts to select from');
};


DiscoJuice.initSearchBox = function () {
	// $("select#loginsearch").flexselect();
//	$("input#loginsearch_flexselect").defaultvalue("or search accounts, i.e. Boston University").addClass("empty");
	var cache = {};

	$("input#ulxSearchField").autocomplete({
		minLength: 2,
		source: function( request, response ) {
			var term = request.term;
			var result;
			
			
//			$("dd#content img.spinning").show();
			DiscoJuice.listResults();
//			$("dd#content img.spinning").hide();
			
			// if ( !term in cache ) {
			// 	result = $.getJSON( "ulxSearch.php", request, function( data, status, xhr ) {
			// 		cache[ term ] = data;
			// 		if ( xhr === lastXhr ) {
			// 			response( data );
			// 		}
			// 	});
			// }
			// response( cache[ term ] );
		}
	});
};

DiscoJuice.locatemeInfo = function (text) {
	$("p#locatemeinfo").text(text);
};

DiscoJuice.locateMe = function () {

//	DiscoJuice.log('Locate me');

	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition( 

			function (position) {  

				// Did we get the position correctly?
				// alert (position.coords.latitude);

				// To see everything available in the position.coords array:
				// for (key in position.coords) {alert(key)}

				DiscoJuice.locatemeInfo('You are here: lat ' + position.coords.latitude + ' lon ' + position.coords.longitude);
				DiscoJuice.findNearby(position.coords.latitude, position.coords.longitude);


			}, 
			// next function is the error callback
			function (error) {
				switch(error.code) {
					case error.TIMEOUT:
						DiscoJuice.locatemeInfo ('Timeout');
						break;
					case error.POSITION_UNAVAILABLE:
						DiscoJuice.locatemeInfo ('Position unavailable');
						break;
					case error.PERMISSION_DENIED:
						DiscoJuice.locatemeInfo ('Permission denied');
						break;
					case error.UNKNOWN_ERROR:
						DiscoJuice.locatemeInfo ('Unknown error');
						break;
				}
			}
		);
	} else {
		DiscoJuice.locatemeInfo('Did not find navigator.geolocation');
	}	
};


DiscoJuice.getCategories = function () {
	var filters = {};
	var type, country;
	
	type = $("select#filterTypeSelect").val();	
	if (type !== 'all') {
		filters.type = type;
	}

	country = $("select#filterCountrySelect").val();	
	if (country !== 'all') {
		filters.country = country;
	}
//	DiscoJuice.log('filters is');
//	DiscoJuice.log(filters);
	
	return filters;
};



DiscoJuice.filterCountrySetup = function (choice) {
	var key;
	var filterOptions = {
		'NO': 'in Norway',
		'DK': 'in Denmark',
		'FI': 'in Finland',
		'SE': 'in Sweden',
		'ES': 'in Spain',
		'IT': 'in Italy',
		'DE': 'in Germany',
		'FR': 'in France',
		'NL': 'in Netherlands',
		'HR': 'in Croatia',
		'CZ': 'in Czech',
		'GR': 'in Greece',
		'PT': 'in Portugal',
		'LU': 'in Luxembourg',
		'CH': 'in Switzerland',
		'SI': 'in Slovenia',
		'US': 'USA',
	};
	var preset = DiscoJuice.options.get('setCountry');
	if (!choice && preset) {
		if (filterOptions[preset]) choice = preset;
	}

	var ftext = 'Show providers ' +
		'<select id="filterCountrySelect" name="filterCountrySelect">';
	
	if (choice) {
		ftext += '<option value="all">in all countries</option>';
	} else {
		ftext += '<option value="all" selected="selected">in all countries</option>';
	}
	
	for (key in filterOptions) {
		if (key === choice) {
			ftext += '<option value="' + key + '" selected="selected">' + filterOptions[key] + '</option>';
		} else {
			ftext += '<option value="' + key + '" >' + filterOptions[key] + '</option>';
		}
	}
	ftext += '</select>';

	$("p#filterCountry").empty().append(ftext);
	$("p#filterCountry select").change(function(event) {
		event.preventDefault();
		$("input#ulxSearchField").val('')
		DiscoJuice.listResults();
	});

};


DiscoJuice.filterTypeSetup = function (choice) {
	var key;
	var filterOptionsType = {
		'commercial': 'commerical companies',
		'lower': 'lower education',
		'higher': 'higher education'
	};

	var ftext = 'Show ' +
		'<select id="filterTypeSelect" name="filterTypeSelect">';
	
	if (choice) {
		ftext += '<option value="all">all types</option>';
	} else {
		ftext += '<option value="all" selected="selected">all types</option>';
	}
	
	for (key in filterOptionsType) {
		if (key === choice) {
			ftext += '<option value="' + key + '" selected="selected">' + filterOptionsType[key] + '</option>';
		} else {
			ftext += '<option value="' + key + '" >' + filterOptionsType[key] + '</option>';
		}
	}
	
	ftext += '</select>';

	$("p#filterType").empty().append(ftext);
	$("p#filterType select").change(function(event) {
		event.preventDefault();
		DiscoJuice.slistResults();
	});

};


/*
	Plugin for JQuery.
	*/
(function($) {
	$.fn.DiscoJuice = function(options) {
		return this.each(function() {
			DiscoJuice.embrace(this, options);
		});
	};
})(jQuery);











