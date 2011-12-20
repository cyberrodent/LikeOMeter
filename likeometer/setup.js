
FBLogin = function() {
  FB.login(function(response) {
      if (response.authResponse) {
        // console.log('Welcome!  Fetching your information.... ');
        FB.api('/me', function(response) {
            // console.log('Good to see you, ' + response.name + '.');
          });
      } else {
        // console.log('User cancelled login or did not fully authorize.');
      }
    }, {scope: 'email,friends_likes,user_likes, read_stream',
	 perms: 'email,friends_likes,user_likes, read_stream' });
}





$(function(){
    // . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .  
    window.fbAsyncInit = function() {
      FB.init({
          appId      : '<?php echo $YOUR_APP_ID ?>', // App ID
          channelURL : '/likeometer/channel.php', // Channel File
          status     : true, // check login status
          cookie     : true, // enable cookies to allow the server to access the session
          oauth      : true, // enable OAuth 2.0
          xfbml      : false // parse XFBML
        });

      // Additional initialization code here

      LOM = new Likeometer();

			client_id = '251829454859769';
			perms_needed = 'email,user_birthday,user_likes,friends_likes,read_stream';

			oauth_url = "https://www.facebook.com/dialog/oauth?scope=" + 
				perms_needed + "&perms=" + perms_needed + "&client_id=" + 
				client_id + "&redirect_uri=https://apps.facebook.com/like_o_meter/";

			fql_confirm_perms = 'SELECT read_stream,friends_likes,user_likes FROM permissions WHERE uid=me()';

			_to_login = function() {
					top.location.href=oauth_url;
			};

			_check_perms = function(resp) {

				if (resp[0]['friends_likes'] != 1){_to_login();}
				if (resp[0]['user_likes'] != 1){_to_login();}
				if (resp[0]['read_stream'] != 1){_to_login();}

				LOM.init(response.authResponse.accessToken, 
					response.authResponse.userID);
			};

			// Check if we have enough permission to do this
			FB.getLoginStatus(function(response) {
					if (response.authResponse) { // user is logged in
						FB.api({ method: 'fql.query', query: fql_confirm_perms }, _check_perms);
          } else { // User not logged in.
						_to_login();		
					}
				}, {perms: 'friends_likes,user_likes'});
    }; 
		// end fbAsyncInit 
		$("#log_in_now").click(FBLogin);
    // . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .  

		// Load the SDK 
    $('body').append('<div id="fb-root"></div>');
    $.getScript(document.location.protocol + 
        '//connect.facebook.net/en_US/all.js');
    });
