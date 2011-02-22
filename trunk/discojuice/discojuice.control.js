/*
 * DiscoJuice
 *  Work is based upon mock up made by the Kantara ULX group.
 * 
 * Author: Andreas Åkre Solberg, UNINETT, andreas.solberg@uninett.no
 * Licence undecided. Awaiting alignment with the licence of the origin Kantara mockup.
 */
if (typeof DiscoJuice == "undefined") var DiscoJuice = {};


DiscoJuice.Control = {
	// Reference to the top level DiscoJuice object
	"parent" : DiscoJuice,

	// Reference to the UI object...
	"ui": null,	
	"data": null,
	
	// Set filter values to filter the result.
	"filters": {},
	
	/*
	 * Fetching JSON Metadata using AJAX.
	 * Callback postLoad is called when data is returned.
	 */
	"load": function() {
		var that = this;		
		if (this.data) return;
		var metadataurl = this.parent.Utils.options.get('metadata');
		
		this.parent.Utils.log('metadataurl is ' + metadataurl);
		if (!metadataurl) return;
		
		$.getJSON(metadataurl, function(data) {
			that.data = data;
			that.parent.Utils.log('Successfully loaded metadata');
			that.postLoad();
		});
	},
	
	"postLoad": function() {
		if (!this.data) return;
		this.readCookie();
		this.prepareData();
		this.discoReadSetup();
		this.showallSetup();
		this.searchboxSetup();		
		this.filterCountrySetup();
		this.getCountry();
		
	},
	
	"readCookie": function() {
		if (this.parent.Utils.options.get('cookie', false)) {
			var selected = this.parent.Utils.readCookie();
			this.parent.Utils.log('COOKIE read ' + selected);
			if(selected) this.setWeight(selected, -100);			
		}
	},
	
	
	
	/*
	 * Set weight to a specific data entry.
	 */
	"setWeight": function(entityid, weight) {
		for(i = 0; i < this.data.length; i++) {
			if (this.data[i].entityid == entityid) {
				if (isNaN(this.data[i].weight)) this.data[i].weight = 0;
				this.data[i].weight += weight;
				this.parent.Utils.log('COOKIE Setting weight to ' + this.data[i].weight);
			}
		}
	},
	
	"discoResponse": function(entityid) {
		this.setWeight(entityid, -100);
		this.prepareData();
	},
	
	
	"prepareData": function() {
	
		this.parent.Utils.log('DiscoJuice.Control prepareData()');
		
		var hits, i, current, search;
		
// 		var i, hits, current, search;
 		var maxhits = 10;
// 		
 		var term = this.getTerm();
 		var categories = this.getCategories();
// 	
// 		var textIcon = '';
		
		if (!this.data) return;
		
		/*
		 * Sort data by weight...
		 */
		this.data.sort(function(a, b) {
			var xa, xb;		
			xa = (a.weight ? a.weight : 0);
			xb = (b.weight ? b.weight : 0);
			return (xa-xb);
		});
		
		if (term || categories) {
			this.ui.popup.find("p.discojuice_showall").show();
		} else {
			this.ui.popup.find("p.discojuice_showall").hide();
		}
		if (categories) {
			maxhits = 25;
		}
// 		if (term) {
// 			maxhits = 10;
// 		}
	
		this.ui.clearItems();
		
		hits = 0;
		for(i = 0; i < this.data.length; i++) {
			current = this.data[i];
			if (!current.weight) current.weight = 0;
			
			if (term) {
				search = this.parent.Utils.searchMatch(current,term);
				if (search === false && current.weight > -50) continue;
			} else {
				search = null;
			}
			
			if (categories && categories.country) {
				if (!current.country) continue;
				if (current.country !== '_all_' && categories.country !== current.country && current.weight > -50) continue;
			}
// 			if (categories && categories.type) {
// 				if (!current.ctype && current.weight > -50) {
// 	//				DiscoJuice.log(current);
// 				continue;
// 				}
// 	//			DiscoJuice.log(current.title + ' category ' + current.ctype);
// 				if (categories.type !== current.ctype && current.weight > -50) continue;
// 			}

			if (++hits > maxhits) { //  && showall !== true) {
				this.ui.popup.find("p.discojuice_showall").show();
				break;
			}
			
	// 		DiscoJuice.log('Accept: ' + current.title);
	
			if (search === true) {
				if (current.descr) {
					this.ui.addItem(current, current.descr);
				} else if (current.country) {
					var cname = (this.parent.Constants.Countries[current.country] ? this.parent.Constants.Countries[current.country] : current.country);
					this.ui.addItem(current, cname);
				} else {
					this.ui.addItem(current);
				}

			} else if (search === null) {
				this.ui.addItem(current);
			} else {
				this.ui.addItem(current, search);
			}

		}
		if (hits < maxhits) { //  && showall !== true) {
//			this.ui.popup.find("p.discojuice_showall").hide();
		}
		
		this.ui.refreshData();
		
		//log('Loaded ' + DiscoJuice.data.length + ' accounts to select from');
	},
	
	"discoWrite": function(entityid) {
		
	},
	
	"selectProvider": function(entityid) {			
		var callback;
		var that = this;
		var mustwait = that.discoWrite(entityid);
		
		if (this.parent.Utils.options.get('cookie', false)) {
			this.parent.Utils.log('COOKIE write ' + entityid);
			this.parent.Utils.createCookie(entityid);		
		}

		callback = this.parent.Utils.options.get('callback');	
		if (callback) {
			if (mustwait) {
				$.doTimeout(1000, function(){
					callback(entityid);
				});
				
			} else {
				callback(entityid);
			}
			return;
		}

	},
	
	// Setup an iframe to read discovery cookies from other domains
	"discoReadSetup": function() {
		var settings = this.parent.Utils.options.get('disco');
		if (!settings) return;
	
		var html = '';
		var returnurl = settings.url;
		var spentityid = settings.spentityid;
		var stores = settings.stores;
		var i;
		var currentStore;
		
		if (!stores) return;
		
		for(i = 0; i < stores.length; i++) {
			currentStore = stores[i];
			
			iframeurl = currentStore + '?entityID=' + escape(spentityid) + '&isPassive=true&returnIDParam=entityID&return=' + escape(returnurl);
			
			html = '<iframe src="' + iframeurl + '" style="display: none"></iframe>';
			this.ui.addContent(html);
		}
	},


	"discoWrite": function(e) {
	
		var settings = this.parent.Utils.options.get('disco');
		if (!settings) return false;
		if (!settings.writableStore) return false;
	
		var html = '';
		var returnurl = settings.url;
		var spentityid = settings.spentityid;
		var writableStore = settings.writableStore;
		
		this.parent.Utils.log('DiscoJuice.Control discoWrite(' + e + ') to ' + writableStore);
			
		iframeurl = writableStore + '?entityID=' + escape(spentityid) + '&IdPentityID=' + 
			escape(e) + '&isPassive=true&returnIDParam=bogus&return=' + escape(returnurl);
			
		html = '<iframe src="' + iframeurl + '" style="display: none"></iframe>';
		this.ui.addContent(html);
		return true;
	},

	"searchboxSetup": function() {
		
		var that = this;
		/*
			Initialise the search box.
			*/
			
//		this.parent.Utils.log(this.ui.popup.find("input.discojuice_search"));
		this.ui.popup.find("input.discojuice_search").autocomplete({
			minLength: 0,
			source: function( request, response ) {
				var term = request.term;
				if (term.length === 1) return;
//				that.resetCategories();							
				that.prepareData();
			}
		});
	},

	"filterCountrySetup": function (choice) {
		var that = this;
		var key;

		var preset = this.parent.Utils.options.get('setCountry');
		if (!choice && preset) {
			if (filterOptions[preset]) choice = preset;
		}
	
		var ftext = '<p class="discojuice_filter_country">Show providers in ' +
			'<select class="discojuice_filterCountrySelect" name="filterCountrySelect">';
		
		if (choice) {
			ftext += '<option value="all">all countries</option>';
		} else {
			ftext += '<option value="all" selected="selected">all countries</option>';
		}
		
		for (key in this.parent.Constants.Countries) {
			if (key === choice) {
				ftext += '<option value="' + key + '" selected="selected">' + this.parent.Constants.Countries[key] + '</option>';
			} else {
				ftext += '<option value="' + key + '" >' + this.parent.Constants.Countries[key] + '</option>';
			}
		}
		ftext += '</select></p>';
		
		this.ui.addFilter(ftext).find("select").change(function(event) {
			event.preventDefault();
			//$("input#ulxSearchField").val('')
			//DiscoJuice.listResults();
			that.resetTerm();
			that.ui.focusSearch();
			that.prepareData();
		});
	},
	"setCountry": function(country) {
		this.ui.popup.find('select.discojuice_filterCountrySelect').val(country);
		this.prepareData();		
	},
	"getCountry": function() {
		// If countryAPI is set, then lookup by IP.
		var countryapi = this.parent.Utils.options.get('countryAPI', false);
		var that = this;
		
		if (countryapi) {
			
			var countrycache = this.parent.Utils.readCookie('Country');
		
			if (countrycache) {
				
				this.setCountry(countrycache);
				this.parent.Utils.log('DiscoJuice getCountry() : Found country in cache: ' + countrycache);
				
			} else {
				
				$.getJSON(countryapi, function(data) {
		//			DiscoJuice.log(data);
					if (data.status == 'ok' && data.country) {
						that.parent.Utils.createCookie(data.country, 'Country');
						that.setCountry(data.country);
						that.parent.Utils.log('DiscoJuice getCountry() : Country lookup succeeded: ' + data.country);
					} else {
						that.parent.Utils.log('DiscoJuice getCountry() : Country lookup failed: ' + (data.error || ''));
					}
				});
			
			}
		}
	},
	
	"showallSetup": function() {
		var that = this;
		this.ui.popup.find("a.discojuice_showall").click(function(event) {
			event.preventDefault();
			that.resetCategories();
			that.resetTerm();
			that.prepareData();
		});
	},
	
	"resetCategories": function() {
		//this.ui.popup.find("select.discojuice_filterTypeSelect").val()
		this.ui.popup.find("select.discojuice_filterCountrySelect").val('all');
	},
	
		
	"getCategories": function () {
		var filters = {};
		var type, country;
		
		type = this.ui.popup.find("select.discojuice_filterTypeSelect").val();	
		if (type && type !== 'all') {
			filters.type = type;
		}
	
		country = this.ui.popup.find("select.discojuice_filterCountrySelect").val();	
		if (country && country !== 'all') {
			filters.country = country;
		}
	//	DiscoJuice.log('filters is');
//		this.parent.Utils.log(filters);
		
		return filters;
	},
	
	"getTerm": function() {
		return this.ui.popup.find("input.discojuice_search").val();
	},
	"resetTerm": function() {
		//this.ui.popup.find("select.discojuice_filterTypeSelect").val()
		this.ui.popup.find("input.discojuice_search").val('');
	},


};