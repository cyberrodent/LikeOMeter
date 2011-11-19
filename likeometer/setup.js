
Likeometer = function (){

	var self = this;
	this.friends = [];
	this.likes = {}; //  fb_uid => likes 
	this.token = false;
	this.graph =  false;
	this.user =  false;   
	var all_friends = [];

	var got_likes = function(response, uid) {
		// console.log("got " + uid + " likes");
		self.likes[uid] = response.data;
		console.log(self.likes.length);
		$("#scroll").append("<div>Friend " + all_friends[uid] + "'s likes "+ response.data.length +" things.<div>");
	}	

	var get_likes_for_id = function(id) {
		FB.api("/" + id  + "/likes", function(response) { 
			// console.log('getting likes for face ' + id);
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
			if (i > 10 ) {
				break;
			}
		}
		$("#friends").append("Friend list loaded. Fetching friends' likes.");
	}

	var got_me = function(response) {
		self.user = response;
	}

	var init = function (token, uid) {
		console.log("init with " + uid);
		self.token = token;
		self.uid = uid;

		

		
		$('body').append('<input type="button" id="LOM" value="DUMP" />');
		$('#LOM').click(function() {
				console.log(self.friends.length);
				console.log(self.likes.length);
				var llimit=4;
				var c= 0;
				for (var i in self.likes) {
					console.log(self.likes[i]);
					if (c++ > llimit) { break; } 
				}
				});





		FB.api("/me", function(response) { got_me(response)});
		FB.api("/me/likes", function(response) { got_likes(response, uid)});
		FB.api("/me/friends", function(response) { got_friends(response, uid)});




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

$('body').append('<div id="friends"></div>');
$('body').append('<div id="scroll"></div>');
lASt=-99;
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
