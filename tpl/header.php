<?php
/**
 * header php 
 */
?><html><head>
	<title>The Company You Keep</title>
    <link rel="stylesheet" href="stylesheets/my.css" media="screen">
</head>
<body>	
<div id="page-header">
	<h1 class="clean">
		The Company You Keep: 
		<span class="small">What your friends say about you.</span>
	</h1>

	<ul>
	<li>&gt;<a href="/?<?php
e($_SERVER['QUERY_STRING']);
?>">You and what you like</a></li>
	<li>&gt;Common Interests</li>
	<li>&gt;<a href="/flikes?<?php

e($_SERVER['QUERY_STRING']);
?>">Friends Like</a></li>

	</ul>
	</div>
