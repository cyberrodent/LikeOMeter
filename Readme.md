Like-O-Meter
============

This is a [Facebook Canvas
App](https://developers.facebook.com/docs/guides/canvas/) that uses the
[Facebook Javascript SDK](https://developers.facebook.com/docs/reference/javascript/)

Set the Canvas URL to your.herokuapp.com/likeometer/ 
You can use that URL for mobile web URL as well - it just runs likeometer smaller.

It assumes a php enabled server as it uses php to grab facebook app id from the apache environment, as described in the heroku VirtualHost below. 

You can see more about the like-o-meter including some screenshots [here](https://www.facebook.com/the.real.like.o.meter).

You can try the like-o-meter on [Facebook](https://apps.facebook.com/like_o_meter/) (facebook account required)


Run locally
-----------
To run a development version locally set Canvas URL to something that resolves back to your
dev apache. Its not necessary but you should Set the App to "Sandbox Mode" on Facebook. 

Configure Apache with a `VirtualHost` that points to the location of this code checkout on your system.

Copy the App ID and Secret from the Facebook app settings page into your `VirtualHost` config, something like:

    <VirtualHost *:80>
        DocumentRoot /Users/abe/Sites/myapp
        ServerName myapp.localhost
        SetEnv FACEBOOK_APP_ID 12345
        SetEnv FACEBOOK_SECRET abcde
    </VirtualHost>

Restart Apache, and you should be able to visit your app at its local URL.






Deploy to Heroku via Facebook integration
-----------------------------------------
The easiest way to deploy is to create an app on Facebook and click Cloud Services -> Get Started, then choose PHP from the dropdown.  You can then `git clone` the resulting app from Heroku.

Deploy to Heroku directly
-------------------------
If you prefer to deploy yourself, push this code to a new Heroku app on the Cedar stack, then copy the App ID and Secret into your config vars:

    heroku create --stack cedar
    git push heroku master
    heroku config:add FACEBOOK_APP_ID=12345 FACEBOOK_SECRET=abcde

Enter the URL for your Heroku app into the Website URL section of the Facebook app settings page, hen you can visit your app on the web.
