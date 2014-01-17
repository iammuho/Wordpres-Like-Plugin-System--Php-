<?php


   /**
    * 
    * Bu dosya Php için wordpress tarzı plugin sistemini sağlamaktadır. Mantık şöyle işlemektedir 
    * 
    * Application/plugins içerisine taşınan ( yönetim panelinden upload ile yüklenen pluginler ) tekrar yönetim paneliden aktif edilince bu dosya
    * üzerinde activate_plugins içerisinden seçilir ve plugin_pool arrayine atanır . Plugin_pool arrayide dosyayı include ederek dosyadaki
    *  add_action'u çalıştırır.
    * 
    * 
    * @author Muhammet ARSLAN
    * @package Creaq
    * @version 1.0
    * @copyright Creaq
    * 
    * */
    
    
    class Plugins
    {
    
    //DB Örneği
    public static $db;
        
    // Bu class'ın instancı
    public static $instance;
    
    // İşlem bilgileri
    public static $actions;
    public static $current_action;
    public static $run_actions;
    
    // Pluginler
    public static $plugins_pool;
    
    //Aktif widgetlar
    public static $widgets_active;
    public static $plugins_active;
    
    // Pluginlerin yolu
    public $plugins_dir;
    public $widgets_dir;
    
    // Hata ve diğer mesaj havuzu
    public static $errors;
    public static $messages;
        
        
        
    /**
    * Constructor
    * 
    * @param  $params(array)
    * @return Pluginler
    */
    public function __construct($params = array(),$activate=true,$db)
    {
            $this->setDb($db);      
     
        
        // Parametre ile gelirse plugin path'i params değerinden gelen plugins_dir indisine atıyorum
        if (array_key_exists('plugins_dir', $params))
        {
        	$this->set_plugin_dir($params['plugins_dir']);
        }
        else // yoksa default değer
        {

        	$this->set_plugin_dir("plugins/");
        }
        
        
        
        if($activate) {
        
        // Pluginler path'inde index.php varsa siliyorum
        $this->plugins_dir = str_replace("index.php", "", $this->plugins_dir);   
        
         
                
        // Tüm pluginleri çekiyorum
        $this->find_plugins();
        
        // Tüm aktif pluginleri çekiyorum
        $this->get_activated_plugins();
        
        // Pluginleri aktif ediyorum
        $this->include_plugins();       
        
        self::$messages = ""; // Mesajları temizle
        self::$errors   = ""; // Hataları temizle  
        }         
    }
    
    
    /**
    * Pluginlerin bulunduğu klasörü ayarlıyorum
    * 
    * @param  $directory
    */
    public function set_plugin_dir($directory)
    {
        if ( !empty($directory) )
        {
            $this->plugins_dir = trim($directory);
        }    
    }
    
    /**
     * Db ayarlıyorum
     * 
     * */
     
     public function setDb($db)
     {
        $this->db = $db;
     }
    
    
    /**
    * Plugin classımın örneğini alıyorum
    * 
    */
    public static function instance()
    {
        if ( ! self::$instance)
        {
            self::$instance = new Plugins(array(),false,self::$db);
        }

        return self::$instance;
    }
    
    /**
    * Pluginleri Bul
    * 
    * Plugins klasöründeki tüm pluginleri bul
    * 
    */
    public function find_plugins()
    {        
        $plugins_folder = opendir($this->plugins_dir); // Find plugins
        
        if ($plugins_folder !== false)
        {        
            while ( $name = readdir ( $plugins_folder ) ) 
            {                 
                $name = strtolower(trim($name));
                      
                // If the plugin hasn't already been added and isn't a file
                if ( ! isset(self::$plugins_pool[$name]) AND !stripos($name, ".") )
                {              
                    // Make sure a valid plugin file by the same name as the folder exists
                    if ( file_exists($this->plugins_dir.$name."/".$name.".php") )
                    {
                        // Register the plugin
                        self::$plugins_pool[$name]['plugin'] = $name; 
                    }
                    else
                    {
                        self::$errors[$name][] = $name." dosyası bulunamadı";
                    }
                }
            }
        }
    }
    
    /**
    * Aktif Pluginleri Çek
    * Veritabanından aktif pluginleri çek
    * 
    */
    public function get_activated_plugins()
    {
         $option = array();
        foreach(self::$plugins_pool as $key => $val)
        {
            
            $sql = "SELECT * FROM cr_plugin_key WHERE opt_key = '" . $this->db->escape($key)  . "' and opt_val = '1'";
            $pl_options = $this->db->fetch_all_array ( $sql ) ;
            
            if($pl_options)
             {
               
                
                foreach($pl_options as $opt_key => $opt_val)
                {
                    
                $option[$opt_key] = $opt_val;
                    
                }
                
             }
        }
        
      
        foreach($option as $key => $val)
        {
          
            
             $info = array(
                 'name' => $val['opt_key'],
                 'id' => $val['id'],
                 'plugin_dir' => 'plugins/'.$val['opt_key'].'/',
                 'main' => $this,             
                );
               
                
                
                self::$plugins_active[$val['opt_key']] = $info;
            
            
        }
        
    
      
            
      
    }
    
    
    /**
    * Pluginleri dahil et
    * 
    */
    public function include_plugins()
    {
        
        if(self::$plugins_active AND !empty(self::$plugins_active))
        {
          
            // Pluginleri döndür
            foreach (self::$plugins_active AS $name => $value)
            {
               
               
               
                include_once $this->plugins_dir.$value['name']."/".$value['name'].".php";
                     
            }   
        }
    }
    

    
    /**
    * Plugin'i aktif et
    * 
    * @param mixed $name
    */
    public function activate_plugin($name)
    {
        $name = strtolower(trim($name));
        
        
        if ( isset(self::$plugins_pool[$name]) AND !isset(self::$plugins_active[$name]) )
        {            
            $db = $this->_ci->db->select('plugin_system_name')->where('plugin_system_name', $name)->get('plugins', 1);
            
            if ($db->num_rows() == 0)
            {
                $this->_ci->db->insert('plugins', array('plugin_system_name' => $name));   
            }
            
           
            do_action('activate_' . $name);
        }
    }
    
     /**
    * Plugini deaktif et
    *
    * 
    * @param string $name
    */
    public function deactivate_plugin($name)
    {
        $name = strtolower(trim($name)); 
        
      
        if ( isset(self::$plugins_active[$name]) )
        {
            $this->_ci->db->where('plugin_system_name', $name)->delete('plugins');
            self::$messages[] = "Plugin ".self::$plugins_pool[$name]['plugin_info']['plugin_name']." has been deactivated!";
            
          
            do_action('deactivate_' . $name);
        }        
    }
    
     /**
    * Plugin Bilgisi
    *
    * Plugin hakkında bilgileri döndürür (pooldan)
    * 
    * @param mixed $name
    */
    public function plugin_info($name)
    {
        if ( isset(self::$plugins_pool[$name]) )
        {
            return self::$plugins_pool[$name]['plugin_info'];
        }
        else
        {
            return true;
        }
    }
    
    
    /**
    * Pluginleri bas
    *
    * Pluginleri ekrana basar
    * 
    */
    public function print_plugins()
    {
        return self::$plugins_pool;
    }
    
    
    /**
    * Plugin için hareket ekleme
    *
    * Tetikleyici
    * 
    * @param mixed $name
    * @param mixed $function
    * @param mixed $priority
    */
    public function add_action($name, $function, $priority=10,$args = array())
    {
        // Zaten varsa boş döndür
        if ( isset(self::$actions[$name][$priority][$function]) )
        {
            return true;
        }
        
        
        if ( is_array($name) )
        {
            foreach ($name AS $name)
            {
               
                self::$actions[$name][$priority][$function] = array("function" => $function,"info" => $args);
            }
        }
        else
        {
           
            self::$actions[$name][$priority][$function] = array("function" => $function,"info" => $args);
        }
        
     
        return true;
    }
    
    
    /**
    * DO ACTION
    *
    * Add action yapılmış belli bir yer için hareketi çağırır
    * 
    * @param mixed $name
    * @param mixed $arguments
    * @return mixed
    */
    public function do_action($name, $arguments = "")
    {
       
        if ( !isset(self::$actions[$name]) )
        {
            return $arguments;
        }
        
        
        self::$current_action = $name;
        
      
        ksort(self::$actions[$name]);
        
        foreach(self::$actions[$name] AS $priority => $names)
        {
            if ( is_array($names) )
            {
                foreach($names AS $name)
                {
                    
                                    
                    $returnargs = call_user_func_array($name['function'], array($name['info']));
                    
                    if ($returnargs)
                    {
                        $arguments = $returnargs;
                    }
                    
                    
                    self::$run_actions[$name][$priority];
                }
            }
        }
        
        
        self::$current_action = '';
        
        return $arguments;
    }
      
    
    /**
    * Hareketi sil
    *
    * Hareketi sileceğimizi söylüyoruz
    * 
    * @param mixed $name
    * @param mixed $function
    * @param mixed $priority
    */
    public function remove_action($name, $function, $priority=10)
    {
        // If the action hook doesn't, just return true
        if ( !isset(self::$actions[$name][$priority][$function]) )
        {
            return true;
        }
        
        // Remove the action hook from our hooks array
        unset(self::$actions[$name][$priority][$function]);
    }
    
    
    /**
    * Geçerli İşlem
    *
    * Mevcutta çalışan actionu çağır
    * 
    */
    public function current_action()
    {
        return self::$current_action;
    }
    
    
/**
 * Plugin bilgilerini getir
 * @param string $key
 * */
    
    function getMeta($key)
{
    $sql = "SELECT * FROM cr_plugin_key WHERE opt_key = '" . $this->db->escape($key)  . "'";
    
    $a = $this->db->query_first ( $sql ) ;
    
    return $a;
            
            
}

/**
 * Plugin bilgilerini getir (plugin sahipleri için)
 * @param string $key
 * */
    
    function getPluginMeta($key)
{
    $sql = "SELECT * FROM cr_plugin_key WHERE opt_key = '" . $this->db->escape($key)  . "'";
    
    $a = $this->db->query_first ( $sql ) ;
    
    return $a['opt_val'];
            
            
}
    

    
}

/**
*  Yeni bir hook ekle
* 
* @param mixed $name
* @param mixed $function
* @param mixed $priority
*/
function add_action($name, $function, $priority=10,$args = array())
{
    return Plugins::instance()->add_action($name, $function, $priority,$args);
}

/**
* Hook'u çalıştır
* 
* @param mixed $name
* @param mixed $arguments
* @return mixed
*/
function do_action($name, $arguments = "")
{
    return Plugins::instance()->do_action($name, $arguments);
}

/**
* Harketi sil
* 
* @param mixed $name
* @param mixed $function
* @param mixed $priority
*/
function remove_action($name, $function, $priority=10)
{
    return Plugins::instance()->remove_action($name, $function, $priority);
}



    
        
        
    
    