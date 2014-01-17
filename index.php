<?php


include("inc/database.class.php");
include("inc/plugins.php");


$db = new Database ( "localhost", "admin_ornek", "ornek123", "admin_ornek", "cr_" );
$db->connect ();


$pl = new Plugins(array(),true,$db);



?>

<html>
<head>

<meta charset="utf-8"/>
<title>WordPress benzeri widget ve Plugin sistemi | Muhammet ARSLAN | Muhammetarslan.com.tr</title>

<?php do_action('header');?>

</head>
<body>

<div class="nav"></div>
<div class="left" style="float: left;"><?php do_action('left');?></div>
<div class="center" style="float: left;"><?php do_action('center');?>


</div>
<div class="right" style="float: right;"><?php do_action('right');?></div>
</body>
</html>


