<?php
/**
 * Plugin Name:  Hello World
 * Version: 1.0
 * Description: Ekrana bir şeyler yazar
 * Author: Muhammet Arslan
 */

 


add_action('header', 'hello_world', 1, $value);
add_action('hello_world_settings', 'settings', 1, $value);


function hello_world($args = array())
{
   
  
   
    
    $type = $args['main']->getPluginMeta("name");
    
  echo "Hello ".$type;
        
  

}

function settings()
{
   ?>
   
   <form method="post">
   
   <input type="text" name="opt_key[name]"/>  <br />

   
   <input type="submit" name="send" value="Kaydet"/>
   
   </form>
   
   
   <?php
    
    
    
}


