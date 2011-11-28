
Likeometer = function () {

	var self = this;
	this.friends = [];
	var likes = {}; //  fb_uid => likes
	var things = {}; // id => data
	var collikes = {}; // collate likes in here like_id => [ uids who like this ]  
	var like_counts = {};
	this.token = false;
	this.graph =  false;
	this.user = {};
	var all_friends = []; // keyed by id, the name of each friend
	var like_count_keys = new Array();
	var started = false;
	var processed = false;
	list = []; //  this tracks how many callbacks have called back

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
		// console.log("do common");
		var my_likes = likes[self.uid];
		var my_like_ids = [];
		var commons = {};
		for (var i=0; i < my_likes.length; i++) {
			my_like_ids.push(my_likes[i].id);
		}
		set_status_line("Initializing: Finding common interests");
		// console.log(all_friends);
		for (var n in all_friends) {
			if (n === self.uid) {
				continue;
			}
			// console.log("n is " + n);
			var friend_likes = likes[n];
			// console.log("friend " + n + " likes " + friend_likes.length);
			for(var i=0; i < friend_likes.length; i++) {
				if (my_like_ids.indexOf(friend_likes[i].id) > 0) {
					// console.log(all_friends[n] + " also likes " + things[friend_likes[i].id].name);
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
		set_status_line("Ready.");
	}
	var show_top_likes = function () {
		if (!processed) { return; } 
		set_status_line("Preparing Like-O-Meter Report");
		var limit = 1000;
		// TODO : watch for sets smaller that limit
		for (var i=0; i < limit; i++) {
			if (collikes[like_count_keys[i]].length > 1) { 
				var thing_id = like_count_keys[i];
				var d = "<div><div class='h2'><img src='https://graph.facebook.com/" +  thing_id + "/picture?type=square' width='50' height='50' border='0' class='thing' />" + "<span class='bigger'>" +  collikes[like_count_keys[i]].length + "</span> friends like " + "<a target=_blank href='https://facebook.com/" + thing_id + "'>" + things[like_count_keys[i]].name + "</a> <span class='category'>(" + things[like_count_keys[i]].category + ")</span>" + '</div><div class="h3">';

				// '<fb:like href="https://www.facebook.com/'+ like_count_keys[i]  +'"></fb:like>' +
				for (var j=0; j < collikes[thing_id].length; j++) {
					d += "<div class='fimg'><a target='_blank' href='https://facebook.com/"+
						collikes[thing_id][j] + "' title='"+ 
						all_friends[collikes[thing_id][j]] + "' >" +
						"<img src='https://graph.facebook.com/" +
						collikes[thing_id][j] + 
						"/picture?type=square' height='50' width='50' border='0' /></a></div>";
				}	
				d += "</div></div>";
				$("#friendslikes").append(d);
			}
		}

		// FB.XFBML.parse();

		$('#flikes').click(flikes_action);
		$('#home').click(home_action);
		$('#common').click(common_action);
		$("nav").show();
		switch_page("#friendslikes");
		set_status_line("Ready.");
	}

	var got_my_likes = function() {
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
				o += '<div style="display:inline-block;"><a title=' + things[thing_id].name +' target=_blank href="https://www.facebook.com/'+ thing_id + '">'+ '<img src="https://graph.facebook.com/'+ thing_id +'/picture?type=square" border="0" align="absmiddle" />&nbsp;' + "</a></div>";
			}
			o += "</div>";
			$("#yourlikes").append(o);
		}
		set_status_line("Your Likes are ready");
		$('#you').click(you_action);
	}

	var got_all_likes = function() {

		got_my_likes();
		set_status_line("Initializing: Got "+	count_likes() +" friends' likes. Processing...");
		if (count_likes() > 1) {  // first time through we need to skip; 
			// FIXME bad edge case for losers with no friends
			//
			$("#scroll").hide();
			$("#statusline").hide();
			for (var i in likes) { 
				var flikes = likes[i];
				for (var f in flikes) {
					var flike = flikes[f];
					things[flike.id] = flike;
					if (typeof(collikes[flike.id])=== 'undefined') {
						collikes[flike.id] = [i];
					} else { 
						collikes[flike.id].push(i);
					}
				}
			}
			set_status_line("Initializing: Collating everybody's likes.");
			for (var i in collikes) {
				like_counts[i] = collikes[i].length;
				like_count_keys.push(i);
			}
			like_count_keys.sort(compare_collikes);
			set_status_line("Collated " + like_count_keys.length + " likes from all of your friends. This is your Top 20.");
			// console.log(like_count_keys[0]);
			// console.log(things[ like_count_keys[0]]);
			// console.log(collikes[ like_count_keys[0]]);

			processed = true;

			show_top_likes();

			// do_common();
		}
	}




	var make_things = function(data) {
		for (var i in data) {
			things[data[i].id] = data[i];
		}
	}

	var got_likes = function(response, uid) {
		// pop the uid from the global list	
		var idx = list.indexOf(uid);
		if (idx < 0) {
			// console.log("got less than zero for " + uid);
		}
		list.splice(idx,1);
		// stash the response in the likes array keyed by this uid
		likes[uid] = response.data;
		// add the things liked by this person to our global things array
		make_things(response.data);
		// scroll the output
		$("#scroll").append("<div>" + all_friends[uid] + "'s likes "+ response.data.length +" things.<div>");
		// if there is nothing left to get then call got_all_likes
		if (list.length === 0) { 
			got_all_likes();
		}
	}	

	var get_likes_for_id = function(id) {
		list.push(id);
		FB.api("/" + id  + "/likes", function(response) { 
				got_likes(response, id) ;
			});
	}

	var got_friends = function(response, uid) { 
		var friends = response.data;
		self.friends = friends;
		set_status_line("Initializing: Fetching friends' likes.");
		for (var i=0; i < friends.length; i++) {
			all_friends[friends[i].id] = friends[i].name;
			get_likes_for_id(friends[i].id);
			// if (i > 99 ) { break; }
		}
	}

	var got_me = function(response) {
		self.user = response;
		all_friends[self.user.id] = self.user.name;
		get_likes_for_id(self.user.id);
	}



	var flikes_action = function() {
		var uid = self.uid;
		set_status_line("What your friend's like");
		switch_page("#friendslikes");	
	}
	var home_action = function() {
		switch_page(".about");
		set_status_line("Welcome");
	}
	var you_action = function() {
		set_status_line("Here's what you like");
		switch_page("#yourlikes");
	}
	var common_action = function() {
		set_status_line("What you and your friends have in common");
		switch_page("#commonlikes");
	}

	var set_status_line = function(message) {
		$("#statusline").text(message);
	}
	var switch_page = function(to_show){
		$(".about").hide();
		$("#friendslikes").hide();
		$("#yourlikes").hide();
		$("#commonlikes").hide();
		if (to_show) {
			$(to_show).show();
		}
	}

	var init = function (token, uid) {
		self.token = token;
		self.uid = uid;

		// console.log("init for " + self.uid + " with token " + self.token);
		if (!started) { 
			started = true;

			switch_page(".about");

			// Auto scroll while loading and collating
			AS = setInterval(function() {
					if ($("#scroll").length) {
						var ds = $("div", $("#scroll")).length;
						var spot = $("#scroll")[0].scrollHeight - $("#scroll").outerHeight() - 1;
						// console.log( spot  +" :: "+  $("#scroll").scrollTop() + 
						// " :: " + $("#scroll")[0].scrollHeight  );
						$("#scroll").animate({scrollTop:spot}, 600);
					} else { 
						clearInterval(AS);
					}
				}, 599);
			// get data
			FB.api("/me", function(response) { got_me(response)});
			FB.api("/me/friends", function(response) { got_friends(response, uid)});

			// console.log('Likeometer init run');
		} else {
			// console.log('Sorry. Likeometer is already running');
		}
	}

	return {
		init : init
	};
}
