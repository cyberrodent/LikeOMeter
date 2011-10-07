<?php
/**
 * main file for this webapp 
 */
// Enforce https on production
if ((array_key_exists('HTTP_X_FORWARDED_PROTO', $_SERVER) and $_SERVER['HTTP_X_FORWARDED_PROTO'] == "http") && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
  header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
  exit();
}


// Provides access to Facebook specific utilities defined in 'FBUtils.php'
require_once('lib/FBUtils.php');
// Provides access to app specific values such as your app id and app secret.
// Defined in 'AppInfo.php'
require_once('lib/AppInfo.php');
// This provides access to helper functions defined in 'utils.php'
require_once('lib/utils.php');

require "lib/urls.php";
require "lib/FrontController.php";
require "lib/Model.php";
require "lib/Friend_Model.php";


FrontController::dispatch();
