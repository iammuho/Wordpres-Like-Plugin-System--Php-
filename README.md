Wordpres Like Plugin System [PHP]
=================================

For Turkish : http://muhammetarslan.com.tr/2014/01/13/wordpress-benzeri-plugin-sistemi-php/

Demo : http://www.muhammetarslan.com.tr/projeler/plugin

Demo Administration Panel :  http://www.muhammetarslan.com.tr/projeler/plugin/yonetim.php

Usage
---------
 1. include plugins.php
 2. include db.class.php (or use your mysql class)
 3. add that code to your design where plugin can reachable
 
```
do_action("header")
```
 4. add that code to your plugin for reach "header" (header is a key describe the place)

```
add_action("header","your_function_name",1,$value)
```
 5. 1 is your plugins priority ( future days i will manage it from administration panel)
 6. $value is a variable from plugins , not change it.
 




