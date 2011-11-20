
Likeometer = function (){

  var self = this;
  this.friends = [];
  var likes = {}; //  fb_uid => likes
	var things = {}; // id => data
  var collikes = {}; // collate likes in here like_id => [ uids who like this ]  
	var like_counts = {};
  this.token = false;
  this.graph =  false;
  this.user = {};
  var all_friends = [];
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

  var compare_likes = function(a, b) {
    if (collikes[a].length > collikes[b].length ) {
        return -1;
    } else if (collikes[a].length === collikes[b].length) {
      return 0;
    } else { 
        return 1;
    }
  }

	var show_top_likes = function() {
		if (!processed) { return; } 
		var limit = 20;
		for (var i=0; i < limit; i++) {
			var thing_id = like_count_keys[i];
			var d = "<div>" + (i+1) + ") " +
				"<a target=_blank href='https://facebook.com/" + thing_id + "'>" +
				things[like_count_keys[i]].name + "</a> (" +
				things[like_count_keys[i]].category + ")" +
				": Liked by these " + 
				collikes[like_count_keys[i]].length + " friends:<br />";
			for (var j=0; j < collikes[thing_id].length; j++) {
				d += "<div class='fimg'><a target='_blank' href='https://facebook.com/"+
					collikes[thing_id][j] + "' title='"+ 
					all_friends[collikes[thing_id][j]] + "' >" +
				 	"<img src='https://graph.facebook.com/" +
					collikes[thing_id][j] + 
					"/picture?type=square' height='30' width='30' border='0' /></a></div>";
			}	
			d += "</div><br />";
			$("#friendslikes").append(d);
		}
	}

  var got_all_likes = function() {
    if (count_likes() > 1) {  // first time through we need to skip; 
			// FIXME bad edge case for losers with no friends

			set_status_line("got "+	count_likes() +" friends' likes. Processing...");

			$("#scroll").hide();
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
     
		  for (var i in collikes) {
				like_counts[i] = collikes[i].length;
				like_count_keys.push(i);
			}
		  like_count_keys.sort(compare_likes);
			set_status_line("Collated " + like_count_keys.length +
					" likes from all of your friends. This is your Top 20.");
      // console.log(like_count_keys[0]);
			// console.log(things[ like_count_keys[0]]);
			// console.log(collikes[ like_count_keys[0]]);

      processed = true;

			show_top_likes();
    }
  };

  var got_likes = function(response, uid) {
    // console.log("got " + uid + " likes");
    var idx = list.indexOf(uid);
    if (idx < 0) {
     console.log("got less than zero for " + uid);
    }
    list.splice(idx,1);
    likes[uid] = response.data;
    $("#scroll").append("<div>Friend " + all_friends[uid] + "'s likes "+ 
				response.data.length +" things.<div>");

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
    for (var i=0; i < friends.length; i++) {
      all_friends[friends[i].id] = friends[i].name;
      get_likes_for_id(friends[i].id);
      // if (i > 99 ) { break; }
    }
		set_status_line("Friend list loaded. Fetching friends' likes.");
  }

  var got_me = function(response) {
    self.user = response;
		all_friends[self.user.id] = self.user.name;
    get_likes_for_id(self.user.id);
  }



	var flikes_action = function() {
		var uid = self.uid;
		// console.log(self.list.length);
		// console.log(self.friends.length);
		// console.log(likes.length);
		
		switch_page("#friendslikes");	

		if (!started) { 
			$('body').append('<div id="scroll"></div>');
			AS = setInterval(function() {
					if ( $("#scroll").length) {
						var ds = $("div", $("#scroll")).length;
						var spot =  $("#scroll")[0].scrollHeight - $("#scroll").outerHeight() -1;
						// console.log( spot  +" :: "+  $("#scroll").scrollTop()+ " :: " + $("#scroll")[0].scrollHeight  );
						$("#scroll").animate({scrollTop:spot}, 600);
					} else { 
						clearInterval(AS);
					}
				}, 599);

			FB.api("/me", function(response) { got_me(response)});
			FB.api("/me/friends", function(response) { got_friends(response, uid)});

			started = true;
		} else {
			console.log('Sorry. Likeometer is already running');
			set_status_line("What your friend's like");
		}

	}
	var set_status_line = function(message) {
		$("#statusline").text(message);
	}
	var init = function (token, uid) {
		console.log("init with " + uid);
		self.token = token;
		self.uid = uid;

		$("body").append("<div id='friendslikes'></div>");
		$("body").append("<div id='commonlikes'>C</div>");
		$("body").append("<div id='yourlikes'>Y</div>");

		switch_page();

		$('#flikes').click(flikes_action);
		$('#home').click(home_action);
		$('#you').click(you_action);
		$('#common').click(common_action);

		console.log('Likeometer init run');
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

	var switch_page = function(to_show){
		$(".about").hide();
		$("#friendslikes").hide();
		$("#yourlikes").hide();
		$("#commonlikes").hide();
		if (to_show) {
			$(to_show).show();
		}
	}

  return {
    init : init
  };
}

