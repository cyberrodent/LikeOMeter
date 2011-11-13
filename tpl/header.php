<?php
/**
 * header php 
 */
?><html><head>
	<title>The Things Your Friends Like ... on Facebook</title>
    <link rel="stylesheet" href="stylesheets/my.css" media="screen">
</head>
<body>	
<div id="page-header">
	<h1 class="clean">
		The Things Your Friends Like ... on Facebook
	</h1>
	<br />
	<ul>
	<li>
		<a href="/">Home</a>
	</li>
	<li>&gt;<a href="/?<?php
e($_SERVER['QUERY_STRING']);
?>">You And What You Like</a></li>
	<li>&gt;<a href="/flikes?<?php

e($_SERVER['QUERY_STRING']);
?>">What Your Friends Like</a></li>

	<li>&gt;<a href="/common?<?php
e($_SERVER['QUERY_STRING']);
?>">Common Interests</a></li>
	</ul>
	</div>
