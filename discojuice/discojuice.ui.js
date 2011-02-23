/*
 * DiscoJuice
 *  Work is based upon mock up made by the Kantara ULX group.
 * 
 * Author: Andreas Åkre Solberg, UNINETT, andreas.solberg@uninett.no
 * Licence undecided. Awaiting alignment with the licence of the origin Kantara mockup.
 */
if (typeof DiscoJuice == "undefined") var DiscoJuice = {};





DiscoJuice.UI = {
	// Reference to the top level DiscoJuice object
	"parent" : DiscoJuice,
	
	// The current data model
	"control": null,
	
	// Reference to the 
	"popup": null,
	
	// Entities / items
	"resulthtml": 'Loading data…',

	"show": function() {
		this.control.load();
	
		this.popup.fadeIn("slow");
		$("div#discojuice_overlay").show(); // fadeIn("fast");
		this.focusSearch();
	},
	
	"focusSearch": function() {
		$("input.discojuice_search").focus();
	},
	"hide": function() {
		$("div#discojuice_overlay").fadeOut("slow"); //fadeOut("fast");
		this.popup.fadeOut("slow");
	},
	
	"clearItems": function() {
		this.resulthtml = '';
	},
	"addItem": function(current, substring, flag) {
		var textLink = '';
		var classes = '';
		if (current.weight < -50) classes += 'hothit';

		var iconpath = this.parent.Utils.options.get('discoPath', '') + 'logos/';
		var flagpath = this.parent.Utils.options.get('discoPath', '') + 'flags/';
		
		var flagtext = '';
		
		if (flag) {
			flagtext = '<img src="' + flagpath + flag + '" alt="' + escape(substring) + '" /> ';
		}

		if (current.icon) {
			if (!substring) {
				textLink += '<a href="" class="' + classes + '" rel="' + escape(current.entityid) + '" title="' + current.title + '">' + 
					'<img class="logo" src="' + iconpath + current.icon + '" />' +
					'<span class="title">' + current.title + '</span><hr style="clear: both; height: 0px; visibility:hidden" /></a>';
			} else {
				textLink += '<a href="" class="' + classes + '" rel="' + escape(current.entityid) + '" title="' + current.title + '">' + 
					'<img class="logo" src="' + iconpath +  current.icon + '" />' +
					'<span class="title">' + current.title + '</span>' + 
					'<span class="substring">' + flagtext + substring + '</span>' +
					'<hr style="clear: both; height: 0px; visibility:hidden" /></a>';
						}
		} else {
			if (!substring) {
				textLink += '<a href="" class="' + classes + '" rel="' + escape(current.entityid) + '"><span class="title">' + current.title + '</span></a>';		
			} else {
				textLink += '<a href="" class="' + classes + '" rel="' + escape(current.entityid) + '"><span class="title">' + current.title + '</span><span class="substring">' + flagtext + substring + '</span></a>';					
			}
	
		}
		this.resulthtml += textLink;
	},
	"refreshData": function() {
		var that = this;
		
		this.parent.Utils.log('DiscoJuice.UI refreshData()');
		
		this.popup.find("div.scroller").empty().append(this.resulthtml);
		this.popup.find("div.scroller a").each(function() {
			var overthere = that;	// Overthere is a reference to the UI object
			$(this).click(function(event) {
				event.preventDefault();
				overthere.hide();
				var entityid = unescape($(this).attr('rel'));
				overthere.control.selectProvider(entityid);
			});
		});
	},

	"enable": function(control) {
		var html = 	'<div style="display: none" class="discojuice">' +
			'<div class="top">' +
				'<a href="#" class="discojuice_close">&nbsp;</a>' +
				'<p class="discojuice_maintitle">' + this.parent.Utils.options.get('title', 'Title')  +  '</p>' +
				'<p class="discojuice_subtitle">' + this.parent.Utils.options.get('subtitle', 'Subtitle') + '</p>' +
			'</div>' +
			'<div id="content" style="">' +
				'<p class="moretext"></p>' +
				'<div class="scroller"></div>' +
			'</div>' +
	
			'<div id="search" class="" >' +
				'<p><input type="search" class="discojuice_search" results=5 autosave="discojuice" name="searchfield" placeholder="or search for a provider, in example Univerity of Oslo" value="" /></p>' +
				'<div class="discojuice_whatisthis" style="margin-top: 15px; font-size: 11px;">' +
					'<a href="#" class="discojuice_what">Help me, I cannot find my provider</a>' +
					'<p class="discojuice_whattext">If your institusion is not connected to Foodle, you may either select to login one of the commercial providers such as Facebook or Google, or you may create a new account using any of the Guest providers, such as Feide OpenIdP.</p>' +
				'</div>' +
			'</div>' +
			
			'<div class="filters bottom">' +
				'<p id="filterCountry"></p>' +
				'<p id="filterType"></p>' +
				'<p class="discojuice_showall" ><a class="discojuice_showall" href="">Show all providers</a></p>' +
				'<p style="margin 0px; text-align: right; color: #ccc; font-size: x-small">DiscoJuice &copy; 2011, UNINETT</p>' +
			'</div>' +
	
// 			'<dd id="locatemediv">' +
// 				'<img style="float: left; margin-right: 5px" src="ulx/images/target.png" alt="locate me..." />' +
// 				'<p style="margin-top: 10px"><a id="locateme" href="">' +
// 					'Locate me</a> to show providers nearby' +
// 				'</p>' +
// 				'<p style="color: #999" id="locatemeinfo"></p>' +
// 				'<div style="clear: both" >' +
// 				'</div>' +
// 			'</dd>' +
		'</div>';
		var that = this;
		
		if (this.parent.Utils.options.get('overlay', true) === true) {
			var overlay = '<div id="discojuice_overlay" style="display: none"></div>';
			$(overlay).appendTo($("body"));
		}
		
		this.popup = $(html).appendTo($("body"));


		if (this.parent.Utils.options.get('always', false) === true) {
			this.popup.find(".discojuice_close").hide();
			this.show();
		} else {
			// Add a listener to the sign in button.
			$(control).click(function(event) {
				event.preventDefault();
				that.show();
				return false;
			});
		}


		// Add listeners to the close button.
		this.popup.find(".discojuice_close").click(function() {
			that.hide();
		});

 		// Add toogle for what is this text.
		this.popup.find(".discojuice_what").click(function() {
			that.popup.find(".discojuice_whatisthis").toggleClass("show");
		});


// 	
// 		
// 		// Add listener to show all providers button.
// 		$("p#showall a").click(function(event){
// 			event.preventDefault();
// 			$("select#filterCountrySelect").val('all');	
// 			DiscoJuice.listResults(true);
// 			$("p#showall").hide();
// 		});
// 		$("p#showall").hide();
// 		
// 		//locateMe();
// 	
// 		// Setup filter by type.
// 		if (DiscoJuice.options.get('location', false) && navigator.geolocation) {
// 			$("#locateme").click(function(event) {
// 				event.preventDefault();
// 				DiscoJuice.locateMe();
// 			});
// 		} else {
// 			$("dd#locatemediv").hide();
// 		}	
// 	
// 	
// 		// Setup filter by type.
// 		if (DiscoJuice.options.get('type', false)) {
// 			DiscoJuice.filterTypeSetup();
// 		}
// 	
// 	
// 		// Setup filter by country.
// 		if (DiscoJuice.options.get('country', false)) {
// 			DiscoJuice.filterCountrySetup();
// 		}
// 		
// 		

// 		
// 			
// 		if (DiscoJuice.options.get('location', false)) {
// 			$("#locateme").click(function(event) {
// 				event.preventDefault();
// 				DiscoJuice.locateMe();
// 			});
// 		} else {
// 			$("dd#locatemediv").hide();
// 		}	
// 		
// 		/*
// 			Initialise the search box.
// 			*/
// 		$("input#ulxSearchField").autocomplete({
// 			minLength: 2,
// 			source: function( request, response ) {
// 				var term = request.term;
// 				var result;
// 				
// 				$("select#filterCountrySelect").val('all');
// 							
// 	//			$("dd#content img.spinning").show();
// 				DiscoJuice.listResults();
// 	//			$("dd#content img.spinning").hide();
// 			}
// 		});
// 	
// 		// List the initial results...
// 		// DiscoJuice.listResults();

	
	},
	
	"addContent": function(html) {
		return $(html).appendTo($("body"));
	},
	"addFilter": function(html) {
		return $(html).prependTo(this.popup.find('.filters'));
//		this.popup.find('.filters').append(html).css('border', '1px solid red');
	}
};

