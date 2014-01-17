<?php


include("inc/database.class.php");
include("inc/plugins.php");


$db = new Database ( "localhost", "admin_ornek", "ornek123", "admin_ornek");
$db->connect ();


$pl = new Plugins(array(),true,$db);


if(@$_GET['key'] and @$_GET['status'])
{
  
    
    if($pl->getMeta($_GET['key'])) { 
        
        $data['opt_val'] = $db->escape($_GET['status']);
        
    
        $db->query_update ( "cr_plugin_key", $data, "opt_key='" . $db->escape ( $_GET ['key'] )  . "'" );    
        
    }else
    {
    
    $data['opt_key'] = $db->escape($_GET['key']);    
    $data['opt_val'] = $db->escape($_GET['status']);
    
    $db->query_insert ( "cr_plugin_key", $data);    
         
        
    }
    
    header("location:yonetim.php");
    
}


//ayarlar kaydediliyor

if(@$_POST)
{
    
    foreach($_POST['opt_key'] as $key => $val)
    {
        
    if($pl->getMeta($key)) { 
        
        $data['opt_val'] = $db->escape($val);
        
    
        $db->query_update ( "cr_plugin_key", $data, "opt_key='" . $db->escape ( $key )  . "'" );    
        
    }else
    {
    
    $data['opt_key'] = $db->escape($key);    
    $data['opt_val'] = $db->escape($val);
    
    $db->query_insert ( "cr_plugin_key", $data);    
         
        
    }
        
    }
    
}





?>

<html>
<head>

<meta charset="utf-8"/>
<title>Yönetim Paneli | WordPress benzeri widget ve Plugin sistemi | Muhammet ARSLAN | Muhammetarslan.com.tr</title>

Yönetim Paneli

</head>
<body>

<h1>Pluginler</h1>

<ul>
    <?php foreach($pl->print_plugins() as $key => $val){ 
        
        $meta = $pl->getMeta($val['plugin']);
        
        ?>
    
       <li><?php echo $val['plugin'];?> : <?php if($meta and $meta['opt_val'] == 1) { ?> <a href="http://www.muhammetarslan.com.tr/projeler/plugin/yonetim.php?status=-1&key=<?php echo $val['plugin'];?>">Aktif</a>
       
      <br /> <strong>Ayarlar : </strong>
       
       
    <?php do_action($val['plugin'].'_settings');?>
       
       
    
       <br /><br />
       
       
       
        <?php }else { ?> <a href="http://www.muhammetarslan.com.tr/projeler/plugin/yonetim.php?status=1&key=<?php echo $val['plugin'];?>">Pasif</a> <?php }?></li> 
    
    
    
    <?php }?>
</ul>







    


</body>
</html>


