


// Likeometer depends on being able to call size like this:
Object.size = function(obj) {
  var size = 0, key;
  for (key in obj) {
    if (obj.hasOwnProperty(key)) size++;
  }
  return size;
};






Likeometer = function () {

  var self = this;
  this.friends = [];
  this.token = false;
  this.graph = false;
  this.user = {};

  var likes = {}; //  fb_uid => likes
  var things = {}; // id => data
  var collikes = {}; // collate likes in here like_id => [ uids who like this ]  
  var like_counts = {};
  var all_friends = []; // keyed by id, the name of each friend
  var arrived = []; // track which users like data has arrived
  var like_count_keys = new Array();
  var started = false;
  var processed = false;
	var rThings = {};

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
    var limit = 300;

    for (var i=0; i < limit; i++) {
      if (collikes[like_count_keys[i]].length > 1) { 

				var dataObject = {};
				var thing_id = like_count_keys[i];
				dataObject['thing_id'] = thing_id;
				dataObject['token'] = self.token;
				dataObject['how_many_friends'] = collikes[thing_id].length;
				dataObject['things_name'] = things[thing_id].name;
				dataObject['things_category'] = things[thing_id].category;
				dataObject['aLikers'] = collikes[thing_id];
				dataObject['friend_name'] = null;
				dataObject['link'] = '';

				rThings[thing_id] = dataObject;
				var d = tmpl("ltrph_tpl", dataObject);
				$("#friendslikes").append(d);

				FB.api('/' + thing_id +"?fields=link,username,id" , function(res) {
						//console.log(res);
						var data = rThings[res.id];
						//console.log(data);
						if (res.link) { 
							data.link = res.link;
						} 
						data.friend_name = all_friends;
						var d = tmpl("ltr_tpl", data);
						$("#ltr"+res.id).replaceWith(d);
					 	FB.XFBML.parse(document.getElementById("h2"+res.id ));
					});
      }
    }

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

    // console.log("arrived length: "+arrived.length);
    // console.log("all_friends length: " + Object.size(all_friends));
    
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
    }
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

    how_many_friends_i_have = Object.size(all_friends) ;
    // really this can't work for largis result sets
    // you get a network error.  So we need to chunk
    // DON'T DO THIS ->  fids = f.join(',');

    var message = "Asking Facebook what your friends like. "
    set_status_line(message);

    var chunk_size = 8;
    var c = 0;
    while (f.length) {
      var chunk = f.splice(0,chunk_size);
      c++;
      var fids = chunk.join(',');
      // console.log("fetching " + fids);
      FB.api("/likes?ids="+fids, function(res) {
          _collate(res);
        });	
      if (c>9999) { break; }  // stop runaway
    }
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
          _init(token, uid);
        });	
    }
  }
  var _init = function (token, uid) {
    self.token = token;
    self.uid = uid;
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
    }

    return {
      init : init
    };
  }
