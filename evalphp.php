<?php
/*
Plugin Name: Eval PHP
 Plugin URI: http://wordpress.org/extend/plugins/evalphp/
 Author URI: http://flashpixx.de
Description: This plugin runs native PHP code that can be added to post and page data.
Author: flashpixx
Version: 0.1
 

#########################################################################
# GPL License                                                           #
#                                                                       #
# This file is part of the Wordpress Eval PHP plugin.                   #
# Copyright (c) 2012, Philipp Kraus, <philipp.kraus@flashpixx.de>       #
# This program is free software: you can redistribute it and/or modify  #
# it under the terms of the GNU General Public License as published by  #
# the Free Software Foundation, either version 3 of the License, or     #
# (at your option) any later version.                                   #
#                                                                       #
# This program is distributed in the hope that it will be useful,       #
# but WITHOUT ANY WARRANTY; without even the implied warranty of        #
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         #
# GNU General Public License for more details.                          #
#                                                                       #
# You should have received a copy of the GNU General Public License     #
# along with this program.  If not, see <http://www.gnu.org/licenses/>. #
#########################################################################
*/

namespace de\flashpixx\evalphp;
    
// ==== constant for developing with the correct path of the plugin ================================================================================
define(__NAMESPACE__."\LOCALPLUGINFILE", __FILE__);
//define(__NAMESPACE__."\LOCALPLUGINFILE", WP_PLUGIN_DIR."/evalphp/".basename(__FILE__));
// =================================================================================================================================================

    
    
// ==== plugin initialization ======================================================================================================================
@require_once("menu.class.php");
@require_once("filter.class.php");
    
// stop direct call
if (preg_match("#" . basename(__FILE__) . "#", $_SERVER["PHP_SELF"])) { die("You are not allowed to call this page directly."); }

// translation
if (function_exists("load_plugin_textdomain"))
	load_plugin_textdomain("evalphp", false, dirname(plugin_basename(LOCALPLUGINFILE))."/lang");
// =================================================================================================================================================  
    
    

// ==== create Wordpress Hooks =====================================================================================================================
add_filter("the_content", "de\\flashpixx\\evalphp\\filter::runInline", -1000);
add_filter("the_content", "de\\flashpixx\\evalphp\\filter::runInclude", -1000);
register_shutdown_function("de\\flashpixx\\evalphp\\filter::catchevalerror");
register_activation_hook(LOCALPLUGINFILE, "de\\flashpixx\\evalphp\\install");
register_uninstall_hook(LOCALPLUGINFILE, "de\\flashpixx\\evalphp\\uninstall");
add_action("admin_menu", "de\\flashpixx\\evalphp\\menu::adminmenu");
add_action("admin_init", "de\\flashpixx\\evalphp\\menu::optionfields");
add_action("admin_enqueue_scripts", "de\\flashpixx\\evalphp\\initAdminScripts");
// =================================================================================================================================================

    

// ==== filter and other functions =================================================================================================================

/** create the default options **/
function install() {
    
    // php.ini values check
    $lc = strtolower(ini_get("safe_mode"));
    if ( (!empty($lc)) && ($lc != "off") )
        trigger_error(__("safe mode must be disabled", "evalphp"), E_USER_ERROR);
    
    $ini = ini_get("disable_functions");
    if (!empty($ini))
    {
        $la = explode(" ", $ini);
        if (is_array($la))
            foreach( array("eval", "register_shutdown_function", "error_get_last") as $i)
                if (in_array($i, $la))
                    trigger_error( printf(__("required [%s] function, but is disallowed in your php settings (php.ini)", "evalphp"), $i), E_USER_ERROR);
    }
    
    
    
    $lxConfig = get_option("fpx_evalphp_option");
    if (empty($lxConfig))
        update_option("fpx_evalphp_option",
                      array(
                            "showerrors" => array(
                                E_ERROR         => true,
                                E_WARNING       => true,
                                E_PARSE         => true,
                                E_NOTICE        => true,
                                E_DEPRECATED    => true
                            ),
                            
                            "disallowed"      => array(),
                            "functions"       => array(),
                            "rundefinedonly"  => false
                      )
        );
}

/** uninstall functions **/
function uninstall() {
    unregister_setting("fpx_evalphp_option", "fpx_evalphp_option");
    delete_option("fpx_evalphp_option");
}
    
    
/** plugin administration page initialization
  * @param $hook hook name of the enquere call
 **/
function initAdminScripts($hook)
{
    wp_register_script( "evalphp_administration", plugins_url("administration.js", LOCALPLUGINFILE), array("jquery") );
    
    // scripts are only load if needed (injection error supress)
    if ($hook == "settings_page_fpx_evalphp_option")
    {
        wp_enqueue_script("evalphp_administration");
        
        wp_localize_script( "evalphp_administration", "evalphp_messages", array(
              "show"      => __("show", "evalphp"),
              "hide"      => __("hide", "evalphp")
       ));
    }
}

// =================================================================================================================================================

?>