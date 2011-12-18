
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
    }, {scope: 'email,friends_likes,user_likes' });
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

      FB.getLoginStatus(function(response) {
          if (response.authResponse) { // user is logged in
            LOM.init(response.authResponse.accessToken, 
              response.authResponse.userID);

            $("#log_in_now").hide();	
          } else { // User not logged in.
            $("#log_in_now").show();	
          }
        }, {perms: 'friends_likes,user_likes'});
// https://www.facebook.com/dialog/oauth?scope=email,user_birthday,user_likes,friends_likes&perms=email,user_birthday,user_likes,friends_likes&client_id=251829454859769&redirect_uri=https://apps.facebook.com/like_o_meter/

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
						top.location.href="https://www.facebook.com/dialog/oauth?scope=email,user_birthday,user_likes,friends_likes&perms=email,user_birthday,user_likes,friends_likes&client_id=251829454859769&redirect_uri=https://apps.facebook.com/like_o_meter/";
						return;
					}
					if (resp[0]['user_likes'] != 1) {

						top.location.href="https://www.facebook.com/dialog/oauth?scope=email,user_birthday,user_likes,friends_likes&perms=email,user_birthday,user_likes,friends_likes&client_id=251829454859769&redirect_uri=https://apps.facebook.com/like_o_meter/";
						return;
					}






    }; // end fbAsyncInit
    // . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .  

    $('body').append('<div id="fb-root"></div>');
    // Load the SDK 
    $.getScript(document.location.protocol + 
        '//connect.facebook.net/en_US/all.js');
      // bind our login function to the login button
      $("#log_in_now").click(FBLogin);
    });
