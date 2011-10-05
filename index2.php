<?php

// Enforce https on production
if ((array_key_exists('HTTP_X_FORWARDED_PROTO', $_SERVER) and $_SERVER['HTTP_X_FORWARDED_PROTO'] == "http") && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
  header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
  exit();
}

/**
 * This sample app is provided to kickstart your experience using Facebook's
 * resources for developers.  This sample app provides examples of several
 * key concepts, including authentication, the Graph API, and ,read_stream,offline_accessFQL (Facebook
 * Query Language). Please visit the docs at 'developers.facebook.com/docs'
 * to learn more about the resources available to you
 */

// Provides access to Facebook specific utilities defined in 'FBUtils.php'
require_once('FBUtils.php');
// Provides access to app specific values such as your app id and app secret.
// Defined in 'AppInfo.php'
require_once('AppInfo.php');
// This provides access to helper functions defined in 'utils.php'
require_once('utils.php');

require('lib/urls.php');
require_once('lib/FrontController.php');

FrontController::dispatch();
