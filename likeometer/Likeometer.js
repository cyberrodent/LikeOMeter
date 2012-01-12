/*
 * Initialization Cycle
 *
 * init is exported and calling init() will start the likeometer.
 *
 * init() will ask the graph a little bit about the user (me) and callback to _init
 *
 * _init  asks for the uid of all me's friends in a random orger and calls back _build
 *
 * _build takes that list of friend uids and in chunks asks the grid for all the things
 * those uids like. The callback to _collate
 *
 * _collate builds the "collikes" and "things" array. _collate keeps track to make sure we
 * get called back for all your friends and then calls show_top_likes()
 *
 * show_top_likes draws the list of things that me's friends like for the likeometer.
 *
 */
Likeometer = function () {

  var self = this;
  this.friends = [];
  this.token = false;
  this.graph = false;
  this.user = {};

  var likes = {}; //  fb_uid => likes
  var things = {}; // id => data
  var collikes = {}; // collate likes in here like_id => [ uids who like this ]  
  var like_counts = {}; // keyed by thing_id, how many friends like it
  var all_friends = []; // keyed by id, the name of each friend
	var arrived = []; // track which users like data has arrived 
	var like_count_keys = new Array(); // sorted array of the thing_ids like
  var started = false; // rm? // if the app has been started - presumably to prevent restarting the app but as yet unimplemented
	var processed = false;  // keep certain functions from rendering if processing is not yet complete
	var rThings = {}; // thing data needed to render a row of liked thing
	var scroll_point = 0; // track how far down in pixels we've infinite scrolled
  var scrolled_pages = 0; // track how many pages we've loaded
	var page_size = 5; // how many items to load on each call - attach to infinite scroll

  var count_likes = function() {
    var count = 0;
    for (var i in likes) {
      count++;
    }
    return count;
  }
  var compare_collikes = function(a, b) {
    if (collikes[a].length > collikes[b].length ) {
      return -1;
    } else if (collikes[a].length === collikes[b].length) {
      return 0;
    } else { 
      return 1;
    }
  }
  var do_common = function() {
		// This finds a list of friends who have likes in common with you
    var my_likes = likes[self.uid];
    var my_like_ids = [];
    var commons = {};
    for (var i=0; i < my_likes.length; i++) {
      my_like_ids.push(my_likes[i].id);
    }
    set_status_line("Initializing: Finding common interests");
    
    for (var n in all_friends) {
      if (n === self.uid) {
        continue;
      }
      var friend_likes = likes[n];
      for(var i=0; i < friend_likes.length; i++) {
        if (my_like_ids.indexOf(friend_likes[i].id) > 0) {
          if (typeof(commons[friend_likes[i].id]) === "undefined") {
            commons[friend_likes[i].id] = [n];
          } else { 
            commons[friend_likes[i].id].push(n);
          }
        }
      }
    }
    for (var t in commons) { 
      $("#commonlikes").append("<div>"+ things[ t ].name + " Liked by you and these "+ commons[t].length + " friends.</div>");
    }
  }

	var find_thing_with_two = function() {
		// this is used as part of creating the message to write on the users wall
		var thing = 0;
		for(i in like_counts) {
			if (like_counts[i] === 2) {
				thing = i;
				break
			}
		}
		if (thing === 0) {
			return like_counts[ Math.floor(Math.random() * like_counts.length) ];
		} else { 
			return thing;
		}
	}
	var if_not_already_announced = function(callback, param) {
		// look on my own wall and see if likeometer published recently
		// if no then call callback with params 


		FB.api("/me/feed", function(res) { 
				if (typeof(res.data) === 'object') {
					for(var i=0; i < res.data.length; i++) {
						try { 
							if (res.data[i].application.namespace === client_name) { 
								//"ns_enilemit_local") {
							 return false;
						} else {
							//
						}
					} catch (e) { 
						// 
					}
					}
				} else { 
					return false;
				}	
				callback(param);
			});
	};
	var announce_on_wall = function() {
		var first = things[like_count_keys[0]];
		var second = things[like_count_keys[1]];
		var third = things[like_count_keys[2]];
		var lucky = find_thing_with_two() ;
		var rand = things[ lucky ];
		// like_counts[first.id] + 
		// " like_counts[second.id] + " like 
		var o = "More of my friends like " +first.name +", "+
						second.name + " and " + third.name + " than anything else. "+
            "But only 2 of them like \"" + rand.name + "\"." 
		var params = {};
		params['message']  = o;
		params['name'] = 'Like-o-Meter';
		params['description'] = 'What your friends like';
		params['link'] = 'https://apps.facebook.com/like_o_meter/';
		// params['picture'] = '';
		// params['caption'] = '';

        var use_ui = 1;

        if (use_ui === 1) { 

            // calling the API ...
            var obj = {
                method: 'feed',
                link: 'https://apps.facebook.com/like_o_meter/',
                // FIXME: 
                // picture: 'https://enilemit.home/images/lom.png',
                picture: 'https://young-autumn-6232.herokuapp.com/images/lom.png',
                name: 'Like-O-Meter',
                caption: o,
                description: 'See what stuff your friends like.'
            };

            function callback(response) {
                // document.getElementById('msg').innerHTML = "Post ID: " + response['post_id'];
            }

            FB.ui(obj, callback);

        } else { 
            // use graph api
            FB.api('/me/feed', 'post', params, function(res) {
                if (!res || res.error) {
                    alert('error'+ res.error.message);
                } else { 
//                    alert("published to stream");

                }
            });
        }
	}
	var show_top_likes = function () {
		// shows a set of the users friends top likes
		// the size of the set is determinied by scroll_point
		// and page_size. 
		//
		// Now with infinite scrolling

		if (!processed) { return; } 

		if (scroll_point === 0) {
			page_size += page_size;
		}
		var limit = scroll_point + page_size;

		// track how far down someone scrolls
    scrolled_pages = scrolled_pages + 1;
		$.get('/likeometer/graphit.php', { 'page' : scrolled_pages } );

		// collikes are things keyed by thing_id
		// like_count_keys is an index of things sorted by how many of me's friends like it
		for (var i=scroll_point;  i < limit; i++) {
			if (collikes[like_count_keys[i]].length > 1) { 
				var thing_id = like_count_keys[i];
				var dataObject = {};
				dataObject['thing_id'] = thing_id;
				dataObject['token'] = self.token;
				dataObject['how_many_friends'] = collikes[thing_id].length;
				dataObject['things_name'] = things[thing_id].name;
				dataObject['things_category'] = things[thing_id].category;
				dataObject['aLikers'] = collikes[thing_id];
				dataObject['friend_name'] = null;
				dataObject['link'] = '';
				dataObject['link'] = '';

				// stash it where a callback and template can access it
				rThings[thing_id] = dataObject; 

				// render a placeholder template to handle keeping things in
				// ranked order 
				var d = tmpl("ltrph_tpl", dataObject);
				$("div#friendslikes").append(d);

				// on callback swap DOM with the placeholder
				FB.api('/' + thing_id +"?fields=link,username,id" , function(res) {
						var data = rThings[res.id]; 
                        if (typeof(data) === "undefined") {
                            // console.log("got back undefined data");
                            // console.log(res);
                            //
                            // FIXME Need somewhere to put errors like this
                            // FIXME Need someone to look at error logs from time to time
                            return;
                        }
						if (res.link) { 
							data.link = res.link;
						} 
						data.friend_name = all_friends;
						data.picture = res.picture;
						var d = tmpl("ltr_tpl", data);
						$("#ltr"+res.id).replaceWith(d);
						FB.XFBML.parse(document.getElementById("h2"+res.id ));
					});
			}
		}

		if (scroll_point === 0) {  // first time only
			$("#statusline").hide();
			$('nav a#friendslikes').click(flikes_action);
			$('nav a#about').click(home_action); 
			// this command would enable click to the "common likes" page
			$('nav a#common').click(common_action);
			// this would enable the "your likes" page
			$('nav a#yourlikes').click(you_action);
			$('nav a#share').click(announce_on_wall);

			$("nav").show();
			switch_page("#friendslikes");
		
			// write on the users wall if we haven't aleady done so recently	
			var go = if_not_already_announced(announce_on_wall);

			// Metrics gathering via facebook	
			FB.Canvas.setDoneLoading();

			// attach scroll handler
			$(document).scroll(function() {
					var doc_h = $(document).height();
					var win_h = $(window).height();
					var st = $(window).scrollTop();
					// this should kick in when we get 
					// 1 scrollbar's height above the 
					// bottom of the screen.
					// FIXME: be more sure these are ints before
					// using + on them ?
					if (st + win_h + win_h > doc_h) {
						show_top_likes();		
					}
					if (false) { // shows some scrolling debug info
						var data = {
							'doc_height' : doc_h,
							'scrolltop' : $(window).scrollTop() + $(window).height(),
							'scroll_bar_height' : (st + win_h + win_h > doc_h) ? 'LOAD NOW!' : '',
							// debug info if you want it
							//'crap' : " DocH: " + $(document).height()  + 
							//	" WinH:" + $(window).height() + " ~~~>> " + 
							//	( $(window).height() / $(document).height() ) * $(window).height()
						};
						var d = tmpl("debug_tpl", data);
						$("#debug").replaceWith(d);
					} // end scrolling debug block
					
				});
		}
	
		/*
		// Draw a button at the bottom if there are more to get
		// This is a fallback if the infinite scroll didn't work
		// if (limit!== page_size && Object.size(collikes) > 0 && (limit < Object.size(collikes)))  
		*/
		$("#more").remove();
		if (limit < Object.size(collikes)) { 
			$("div#friendslikes").append("<div id='more'>Click to see more.</div>");
			$("#more").click(show_top_likes);
		}
		scroll_point = limit; // ready for more ...
		if (false) { // debug
			$("#debug").append("<p>H" + $(document).height() + "</p>");
			$("#debug").append("<p>S" + $(window).scrollTop() + "</p>");
		}
	}

	var got_my_likes = function() {
		//
		// Categorize your likes by category
		//   currently unused in the application
		//

    set_status_line("Categorizing your likes");

    var my_likes = likes[self.uid];
    var my_cats = {};
    var my_cat_keys = new Array();
    var sort_cat_counts = function(a,b) {
      if (my_cats[a].length < my_cats[b].length) {
        return 1;
      } else if (my_cats[a].length ===  my_cats[b].length) {
        return 0;
      } else { 
        return -1;
      }
    }

    for (var like in my_likes) {
      if (typeof(my_cats[my_likes[like].category]) === 'undefined') {
        my_cats[my_likes[like].category] = [my_likes[like].id];
        my_cat_keys.push(my_likes[like].category);
      } else {
        my_cats[my_likes[like].category].push( my_likes[like].id);
      }
    }

    my_cat_keys.sort(sort_cat_counts);

    for (var i in my_cat_keys) {
      key = my_cat_keys[i];
      var o = "<div><h3>" + key + "</h3>";
      var how_many =  my_cats[key].length;
      o += "You like " + how_many + " " + key + "<br />";

      for (var j in my_cats[key]) {
        var thing_id = my_cats[key][j];
        o += '<div style="display:inline-block;"><a title=' + things[thing_id].name +' target=_blank href="https://www.facebook.com/'+ thing_id + '">'+ '<img src="https://graph.facebook.com/'+ thing_id +'/picture?type=square&auth_token='+ self.token  +'" border="0" align="absmiddle" />&nbsp;' + "</a></div>";
      }
      o += "</div>";
      $("div#yourlikes").append(o);
    }
    set_status_line("Your Likes are ready");
    $('a#yourlikes').click(you_action);
  }

  var make_things = function(data) {
    for (var i in data) {
      things[data[i].id] = data[i];
    }
  }

	//
	// these "*_action" functions handle what "page"
	// is being shown.
	//
  var flikes_action = function() {
    var uid = self.uid;
    set_status_line("What your friend's like");
    switch_page("#friendslikes");	
  }

  var home_action = function() {
    switch_page("#about");
    set_status_line("Welcome");
  }

  var you_action = function() {
    set_status_line("Here's what you like");
    switch_page("#yourlikes");
  }

  var common_action = function() {
    set_status_line("What you and your friends have in common");
    switch_page("#common");
  }




  var set_status_line = function(message) {
    $("#statusline").text(message);
  }

  var switch_page = function(to_show){
    $("div.loading").hide();
    $("div#about").hide();
    $("div#friendslikes").hide();
    $("div#yourlikes").hide();
    $("div#common").hide();

		$("nav a").removeClass("here");

    if (to_show) {
      $('div'+to_show).show();
			$("nav").find(to_show).addClass("here");	
    }
  }

  var _collate = function(res) {
		//
		// Collates friend likes by liked thing
		// handles batches of input coming back async
		// build things and collikes arrays
		// sets processing to true when this is done
		// kicks off show_top_likes when all done
		//
    set_status_line("Collating");
    if (!res || res.error) {
      set_status_line("Something went wrong " + res.error.message);
      return;
    }
    if (res.error_code) {
      set_status_line("Something went wrong. Error code: " + res.error_code);
      return;
    }
    if (typeof(res.error) !== 'undefined') {
      set_status_line(res.error.type + " Error: " + res.error.message);
      return;
    }

    for(var friend_id in res) {
      arrived.push(friend_id); 
      var flikes = res[friend_id].data;
      set_status_line("Collated " + Object.size(collikes) + " things from " + arrived.length + " friends.");
      for (var j = 0; j < flikes.length; j++) {
        var thing_id = flikes[j].id;
        things[thing_id] = flikes[j];

			
        if (typeof(collikes[thing_id]) !== 'undefined') {
          collikes[thing_id].push(friend_id);
        } else { 
          collikes[thing_id] = [friend_id];
        }
      }
    }

    // if we are last then we do this
    if (arrived.length >= how_many_friends_i_have) {
      set_status_line("Sorting.....");

      for (var i in collikes) {
        like_counts[i] = collikes[i].length;
        like_count_keys.push(i);
      }
      like_count_keys.sort(compare_collikes);

      processed = true;

      show_top_likes();
			
			// wire this off for now
			// show_distribution();
    }
  };

	var show_distribution = function() { 
		// show some sort of histogram showing the long long tail
		var like_count_lengths = [];
		for (var i=0; i < like_count_keys.length; i++) {
			var h = collikes[like_count_keys[i]].length;
			like_count_lengths.push(h);
		}
		var max = 0;
		var dist = {};
		for (var i= like_count_lengths.length; i >= 0; i--) {
			if (like_count_lengths[i] > max) {
				max = like_count_lengths[i];
			}
			if (typeof(dist[ like_count_lengths[i]]) === 'undefined') {
				dist[ like_count_lengths[i]] = 1;
			} else { 
				dist[ like_count_lengths[i]]++;
			}
		}
		var DIST = []
		for(di in dist) {
			if (di !== 'undefined') { 
				DIST.push( { 'dx': 1 , 'x' : dist[di] , 'y' : di });	
			}
		}

		//var data = {
		//	'like_count_lengths' : like_count_lengths,	
		//};
		// var d = tmpl("histogram", data);
		// $("#friendslikes").before(d);

		$("div#friendslikes").append("<div id='vis'></div>");
		createLayout(like_count_lengths, like_count_lengths.length, 1+max);	

	}
	var createLayout = function(data, points, max) {
		// part of the histogram
		var w = 620,
			h = 200,
			x = pv.Scale.linear(0, max).range(0,w),
			bins = pv.histogram(data).bins(x.ticks(30));
			var y = pv.Scale.root(0, points).range(0,h).power(3);
			var vis = new pv.Panel()
				.width(w)
				.height(h)
				.margin(40);

			vis.add(pv.Bar)
				.data(bins)
				.bottom(0)
				.left(function(d){return x(d.x)   })
				.width(function(d){return x(d.dx) })
				.height(function(d){return y(d.y)})
				.fillStyle("#f36")
				.strokeStyle("rgba(255,255,255, 1)")
				.lineWidth(1)
				.antialias(false);

			vis.add(pv.Rule)
				.data(y.ticks(5))
				.bottom(y)
				.left(0)
				.strokeStyle("#333")
				.anchor("left").add(pv.Label)
				.text(y.tickFormat);

			vis.add(pv.Rule)
				.data(x.ticks())
				.strokeStyle("#333")
				.left(x)
				.bottom(-5)
				.height(5)
				.anchor("bottom").add(pv.Label)
				.text(x.tickFormat);

				vis.add(pv.Rule)
				.bottom(0);

				vis.render();


	}

  var _build = function(res) {
		// gets the list of friends that kicks off the whole show
    if (typeof(res.error) !== 'undefined') {
      set_status_line(res.error.type + " Error: " + res.error.message);
      return;
    }
    set_status_line("Building query");
    friends = res;
    var fids = ''; // string of comma separated friend ids
    var f = [];
    for (var i=0; i < friends.length; i++) {
      f.push(friends[i].uid);
      all_friends[ friends[i].uid] = friends[i].name;
    }
    f.push(self.user.id);

    how_many_friends_i_have = Object.size(all_friends) ;

    // DON'T JUST DO THIS -->  var fids = f.join(',');
    // Doesn't work for large result sets. You get a network error.  
    // So we need to chunk.
    var message = "Asking Facebook what your friends like. "
    set_status_line(message);

    var chunk_size = 8; // how many friend likes to ask for in a batch?
    var c = 0; // to keep track of how many chunks we've fetched
    while (f.length) {
      if (c>9999) { 
				// stop runaway
				break; 
			}
      c++;
      var chunk = f.splice(0,chunk_size);
      var fids = chunk.join(','); // fid ~ "Friends' ids"
      FB.api("/likes?ids="+fids, function(res) {
          _collate(res);
        });	
		}
  }

  var init = function (token, uid) {
    set_status_line("Hello.  We're just starting");
    self.token = token;
    self.uid = uid;
    var ts = Math.round((new Date()).getTime() / 1000);
    if (!started) {  
      started = true;
      switch_page(".loading");
      var me = FB.Data.query('select name, uid from user where uid={0}', uid);
      me.wait(function(rows) {
          self.user = rows[0];
          self.user.id = self.user.uid;
          _init(token, uid);
        });	
    }
  }
  var _init = function (token, uid) {
    self.token = token;
    self.uid = uid;

    // get data via FB.Data.query
    var friends_id = FB.Data.query(
      'select uid, name from user where uid in (' +
        'select uid2 from friend ' + 
        'where uid1=me() order by rand() ' +
        ')');
      friends_id.wait(function(rows){
          _build(rows);
        });
    }
		// - =  - =  - =  - =  - =  - =  - =  - =  - =  - =  - =  - =  - =  - = 
    return {
      init : init
    };
  }
