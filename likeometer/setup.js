


LOM = function (){
	var self = this;

	this.graph = [];
	

	getLikes = function (id, token) {
		var ret = [];
		console.log("getting id " + id + " and token " + token);
		return ret;
	};

	graphGet = function (id, token) {
		var ret = [];
		FB.api("/" + id, function (response) {
				self.graph[id] = response;
				ret = response;
			});
		return ret;
	};


	return {
		getLikes : getLikes,
		graphGet: graphGet ,
		graph : this.graph
	}
}





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
	FB.getLoginStatus(function(response) {
			if (response.authResponse) {
				console.log(response.authResponse.accessToken);
				console.log('Welcome!  Fetching your information.... ');
				FB.api('/me', function(response) {
						console.log('Good to see you, ' + response.name + '.');
						console.log(response);


				

						$("#log_in_now").hide();	


						var lom = new LOM();
						var x = lom.graphGet("me/friends",2);
					});
			} else {
				// console.log('User not logged in.');
				$("#log_in_now").show();	
			}
		}, {scope: 'email'});







}; // end fbAsyncInit

// Load the SDK Asynchronously
(function(d){
		var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
		js = d.createElement('script'); js.id = id; js.async = true;
		js.src = "//connect.facebook.net/en_US/all.js";
		d.getElementsByTagName('head')[0].appendChild(js);
	}(document));
