
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
		$("body").append("<div id='report'></div>");
		for (var i=0; i < limit; i++) {
			var thing_id = like_count_keys[i];
			var d = "<div>" + (i+1) + ") " +
					"<a target=_blank href='https://facebook.com/" + thing_id + "'>" +
					things[like_count_keys[i]].name + "</a> (" +
					things[like_count_keys[i]].category + ")" +
					": Liked by these " + 
					collikes[like_count_keys[i]].length + " friends:<br />";

				for (var j=0; j < collikes[thing_id].length; j++) {
					d += "<div class='fimg'><a target='_top' href='https://facebook.com/"+
						collikes[thing_id][j] + "' title='"+ 
						all_friends[collikes[thing_id][j]] + "' >" 	
						+ "<img src='https://graph.facebook.com/" +
						collikes[thing_id][j] + 
						"/picture?type=square' height='30' width='30' border='0' /></a></div>";
				}	
				d += "</div><br />";
				$("#report").append(d);
		}
	}

  var got_all_likes = function() {
    if (count_likes() > 1) {  // first time through we need to skip; FIXME bad edge case for losers with no friends
      $("#friends").replaceWith("<div id='friends'>got " + count_likes() +" friends' likes. Processing...</div>");
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

      $("#friends").replaceWith("<div id='friends'>Collated " + like_count_keys.length +" likes from all of your friends.</div>");
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
    $("#scroll").append("<div>Friend " + all_friends[uid] + "'s likes "+ response.data.length +" things.<div>");

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
      // $("#friends").append("<div>Fetching " + friends[i].name + "'s likes....</div>");
      all_friends[friends[i].id] = friends[i].name;
      get_likes_for_id(friends[i].id);
      // if (i > 99 ) { break; }
    }
    $("#friends").append("Friend list loaded. Fetching friends' likes.");
  }

  var got_me = function(response) {
    self.user = response;
		all_friends[self.user.id] = self.user.name;
    get_likes_for_id(self.user.id);
  }

  var init = function (token, uid) {
    console.log("init with " + uid);
    self.token = token;
    self.uid = uid;

    $('body').append('<input type="button" id="LOM" value="Sounds great. Start the Like-O-Meter" />');
    $('#LOM').click(function() {

        // console.log(self.list.length);
        // console.log(self.friends.length);
        // console.log(likes.length);
				if (!started) { 
					$(".about").hide();
					$("#LOM").hide();
					$('body').append('<div id="friends"></div>');
					$('body').append('<div id="scroll"></div>');

					AS = setInterval(function() {
							if ( $("#scroll").length) {
								var ds = $("div", $("#scroll")).length;
								var spot =  $("#scroll")[0].scrollHeight - $("#scroll").outerHeight() -1;
								// console.log( spot  +" :: "+  $("#scroll").scrollTop()+ " :: " + $("#scroll")[0].scrollHeight  );
								$("#scroll").animate({scrollTop:spot}, 900);
							} else { 
								clearInterval(AS);
							}
						}, 999);
					FB.api("/me", function(response) { got_me(response)});
					// FB.api("/me/likes", function(response) { got_likes(response, uid)});
					FB.api("/me/friends", function(response) { got_friends(response, uid)});
					started = true;
				}

       
      });
    console.log('inited');
  }

  return {
    init : init
    //getLikes : getLikes,
    //graphGet: graphGet ,
    //graph : this.graph
  };
}


FBLogin = function() {
  FB.login(function(response) {
      if (response.authResponse) {
        // console.log('Welcome!  Fetching your information.... ');
        FB.api('/me', function(response) {
            console.log('Good to see you, ' + response.name + '.');
          });
      } else {
        console.log('User cancelled login or did not fully authorize.');
      }
    }, {scope: 'email, friends_likes, user_likes' });
}





$(function(){

    // everything's in here

    window.fbAsyncInit = function() {
      FB.init({
          appId      : '<?php echo AppInfo::appID() ?>', // App ID
          channelURL : '/likeometer/channel.php', // Channel File
          status     : true, // check login status
          cookie     : true, // enable cookies to allow the server to access the session
          oauth      : true, // enable OAuth 2.0
          xfbml      : true  // parse XFBML
        });
      // Additional initialization code here

      LOM = new Likeometer();

      FB.getLoginStatus(function(response) {
          if (response.authResponse) { // user is logged in
            LOM.init(response.authResponse.accessToken, 
              response.authResponse.userID);

            $("#log_in_now").hide();	
					} else { // User not logged in.
            $("#log_in_now").show();	
          }
        }, {scope: 'email, friend_likes, user_likes'});

    }; // end fbAsyncInit

    $('body').append('<div id="fb-root"></div>');
    // Load the SDK 
    $.getScript(document.location.protocol + '//connect.facebook.net/en_US/all.js');

    // bind our login function to the login button
    $("#log_in_now").click(FBLogin);



  });

lASt=-99;

