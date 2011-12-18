
Likeometer = function () {

	var self = this;
	var likes = {}; //  fb_uid => likes
	var things = {}; // id => data
	var collikes = {}; // collate likes in here like_id => [ uids who like this ]  
	var like_counts = {};
	var all_friends = []; // keyed by id, the name of each friend
	var like_count_keys = new Array();
	var started = false;
	var processed = false;

	this.friends = [];
	this.token = false;
	this.graph = false;
	this.user = {};

	// Why is this global?
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
		var limit = 250;

		for (var i=0; i < limit; i++) {
			if (collikes[like_count_keys[i]].length > 1) { 
				var thing_id = like_count_keys[i];
				var d = "<div class='ltr'><div class='h2'><a href='https://facebook.com/" + thing_id + 
					"' target=_blank><img src='http://graph.facebook.com/" +  
					thing_id + "/picture?type=large&auth_token=" + 
					self.token + "' align='top'  border='0' class='thing' border='0' /></a>" + 
					"<span class='bigger'>" + collikes[like_count_keys[i]].length + 
					"</span> friends like <br />" + 
					"<a target=_blank href='https://facebook.com/" + thing_id + "'>" + 
					things[like_count_keys[i]].name + "</a> <span class='category'>(" + 
					things[like_count_keys[i]].category + ")</span>" + '</div><div class="h3">';

				// d += '<fb:like href="https://www.facebook.com/' + like_count_keys[i] +'"></fb:like>';

				// draw a friend icon for each friend who likes this
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
		$("#statusline").hide();
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
				o += '<div style="display:inline-block;"><a title=' + things[thing_id].name +' target=_blank href="https://www.facebook.com/'+ thing_id + '">'+ '<img src="https://graph.facebook.com/'+ thing_id +'/picture?type=square&auth_token='+ self.token  +'" border="0" align="absmiddle" />&nbsp;' + "</a></div>";
			}
			o += "</div>";
			$("#yourlikes").append(o);
		}
		set_status_line("Your Likes are ready");
		$('#you').click(you_action);
	}

	var make_things = function(data) {
		for (var i in data) {
			things[data[i].id] = data[i];
		}
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

	var _collate = function(res) {
		set_status_line("Collating");
		if (!res || res.error) {
			set_status_line("Something went wrong " + res.error.message);
			return;
		}
		if (res.error_code) {

			set_status_line("Something went wrong. Error code: " + res.error_code);
			for (m in res) {
				console.log(m);
				console.log(eval('res["'+m+'"]'));
			

			}
			return;
		}

		// console.log('res'); 
		// console.log(res); 
		if (typeof(res.error) !== 'undefined') {
			set_status_line(res.error.type + " Error: " + res.error.message);
			return;
		}

		for(var friend_id in res) {
			var flikes = res[friend_id].data;

			set_status_line("Collating: " + friend_id  );

			for (var j = 0; j < flikes.length; j++) {
				var thing_id = flikes[j].id;
				things[thing_id] = flikes[j];
				if (typeof(collikes[thing_id]) !== 'undefined') {
					collikes[thing_id].push(friend_id);
				} else { 
					collikes[thing_id] = [friend_id];
				}

				// $("#scroll").append("<div>" + all_friends[friend_id] + "'s likes "+ flikes.length +" things.<div>");
			}
		}

		set_status_line("Collated friends likes");
			set_status_line("Initializing: Collating everybody's likes.");
			for (var i in collikes) {
				like_counts[i] = collikes[i].length;
				like_count_keys.push(i);
			}
			like_count_keys.sort(compare_collikes);
			set_status_line("Collated " + like_count_keys.length + " likes from all of your friends. This is your Top 20.");

			processed = true;

			// $("#scroll").hide();
			show_top_likes();
	};

	var _build = function(res) {
		// console.log("_build");
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
		fids = f.join(',');
		set_status_line("Asking Facebook what your friends like. Hang on.");
		FB.api("/likes?ids="+fids, function(res) {
						_collate(res);
				});	
	}

	var init = function (token, uid) {

		set_status_line("Hello.  We're just starting");

		self.token = token;
		self.uid = uid;
		var ts = Math.round((new Date()).getTime() / 1000);

		if (!started) { 
			started = true;
			switch_page(".about");

			var me = FB.Data.query('select name, uid from user where uid={0}', uid);
			me.wait(function(rows) {
					// console.log("wait came back");
					self.user = rows[0];
					self.user.id = self.user.uid;
				});	

FB.api({ method: 'fql.query', 
		query: 'SELECT friends_likes,user_likes FROM permissions WHERE uid=me()' }, 
	function(resp) {
    for(var key in resp[0]) {
        if(resp[0][key] === "1")
            console.log(key+' is granted')
        else
            console.log(key+' is not granted')
    }

		if (resp[0]['friends_likes'] != 1) {
			set_status_line("Permission Error 1");				
		return;
		}
		if (resp[0]['user_likes'] != 1) {
			set_status_line("Permission Error 2");				
			return;
		}


		_init(token, uid);
});








		}
	}
	var _init = function (token, uid) {
		self.token = token;
		self.uid = uid;
			//			// Auto scroll while loading and collating
			//			AS = setInterval(function() {
			//					if ($("#scroll").length) {
			//						var ds = $("div", $("#scroll")).length;
			//						var spot = $("#scroll")[0].scrollHeight - $("#scroll").outerHeight() - 1;
			//						$("#scroll").animate({scrollTop:spot}, 600);
			//					} else { 
			//						clearInterval(AS);
			//					}
			//				}, 599);
		
			// get data
			var friends_id = FB.Data.query(
				'select uid, name from user where uid in (' +
					'select uid2 from friend ' + 
					'where uid1=me() order by rand() ' +
					')');
			friends_id.wait(function(rows){
				 //console.log("friends_ids came back");
					_build(rows);
				});

			//	var likes = FB.Data.query('select object_id FROM like WHERE user_id=me()');
			//  likes.wait(function(rows){ console.log(rows); });

			
			// FB.api("/me", function(response) { got_me(response)});
			// FB.api("/me/friends", function(response) { got_friends(response, uid)});

			}

	return {
		init : init
	};
}
