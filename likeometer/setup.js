// Simple JavaScript Templating
// John Resig - http://ejohn.org/ - MIT Licensed
(function(){
  var cache = {};
  
  this.tmpl = function tmpl(str, data){
    // Figure out if we're getting a template, or if we need to
    // load the template - and be sure to cache the result.
    var fn = !/\W/.test(str) ?
      cache[str] = cache[str] ||
        tmpl(document.getElementById(str).innerHTML) :
      
      // Generate a reusable function that will serve as a template
      // generator (and which will be cached).
      new Function("obj",
        "var p=[],print=function(){p.push.apply(p,arguments);};" +
        
        // Introduce the data as local variables using with(){}
        "with(obj){p.push('" +
        
        // Convert the template into pure JavaScript
        str
          .replace(/[\r\t\n]/g, " ")
          .split("<%").join("\t")
          .replace(/((^|%>)[^\t]*)'/g, "$1\r")
          .replace(/\t=(.*?)%>/g, "',$1,'")
          .split("\t").join("');")
          .split("%>").join("p.push('")
          .split("\r").join("\\'")
      + "');}return p.join('');");
    
    // Provide some basic currying to the user
    return data ? fn( data ) : fn;
  };
})();




var perms_needed = 'publish_stream,read_stream,email,user_likes,friends_likes';



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
    }, {
			// documention may be wrong according to stackoverflow
			// so play it both ways
			scope: perms_needed,
      perms: perms_needed });
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

      var client_id = '251829454859769';
      // client_id = '260337734005390'; // local
      var oauth_url = "https://www.facebook.com/dialog/oauth?scope=" + 
        perms_needed + "&perms=" + perms_needed + "&client_id=" + 
        client_id + "&redirect_uri=https://apps.facebook.com/like_o_meter/";

      var fql_confirm_perms = 'SELECT friends_likes,user_likes,publish_stream FROM permissions WHERE uid=me()';

      var _to_login = function() {
        top.location.href=oauth_url;
      };

      var _check_perms = function(resp) {

        if (resp[0]['friends_likes'] != 1){_to_login();}
        if (resp[0]['user_likes'] != 1){_to_login();}
        if (resp[0]['publish_stream'] != 1){_to_login();}

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
