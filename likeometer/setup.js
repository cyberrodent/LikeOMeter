Likeometer = function (){

	var self = this;
	this.token = false;
	this.graph =  false;
	this.user =  false;   
	this.all_friends = [];
	this.likes = []; //  fb_uid => likes 

	got_friend_like = function(response, friend_id) {
		
	}

	got_likes = function(response, uid) {
		console.log("got " + uid + " likes");
		self.likes[uid] = response.data;
		$("#friends").append("<div>Friend " + self.all_friends[uid] + "'s likes "+ response.data.length +" things.<div>");
	}	
		
	got_friends = function(response, uid) { 
		// self.friends[uid]  = response.data;
		$("#friends").append("friends loaded");
		// console.log('got friends for' + uid);		
		// console.log(response.data);
		var friends = response.data;

		for (var i=0; i < friends.length; i++) {
				$("#friends").append("<div>Fetching " + friends[i].name + "'s likes....</div>");
//				console.log(friends[i]);
				// var id = friends[i].id;
				self.all_friends[friends[i].id] = friends[i].name;
				var id = friends[i].id;
				FB.api("/" + friends[i].id  + "/likes", function(response ) { 
						console.log('starting to fetch friend ' + id);
						got_likes(response, id) ;
					});
		}

 	}

	got_me = function(response) {
		self.user = response;
		console.log('got me');		
	}

	init = function (token, uid) {
		console.log(uid);
		self.token = token;
		self.uid = uid;
		FB.api("/me", function(response) { got_me(response) });
		FB.api("/me/likes", function(response) { got_likes(response, uid)} );
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
				console.log('Welcome!  Fetching your information.... ');
				FB.api('/me', function(response) {
						console.log('Good to see you, ' + response.name + '.');
					});
			} else {
				console.log('User cancelled login or did not fully authorize.');
			}
		}, {scope: 'email, friends_likes, user_likes' });

}




$(function(){
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
		$('body').append('<div id="friends"></div>');
		$.getScript(document.location.protocol + '//connect.facebook.net/en_US/all.js');
		$("#log_in_now").click(FBLogin);




});
// Load the SDK Asynchronously
/*
(function(d){
		var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
		js = d.createElement('script'); js.id = id; js.async = true;
		js.src = "//connect.facebook.net/en_US/all.js";
		d.getElementsByTagName('head')[0].appendChild(js);
	}(document));
*/
