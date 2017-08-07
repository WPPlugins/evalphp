<?php
/** 
 * #########################################################################
 * # GPL License                                                           #
 * #                                                                       #
 * # This file is part of the Wordpress Eval PHP plugin.                   #
 * # Copyright (c) 2010-2012, Philipp Kraus, <philipp.kraus@flashpixx.de>  #
 * # This program is free software: you can redistribute it and/or modify  #
 * # it under the terms of the GNU General Public License as published by  #
 * # the Free Software Foundation, either version 3 of the License, or     #
 * # (at your option) any later version.                                   #
 * #                                                                       #
 * # This program is distributed in the hope that it will be useful,       #
 * # but WITHOUT ANY WARRANTY; without even the implied warranty of        #
 * # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         #
 * # GNU General Public License for more details.                          #
 * #                                                                       #
 * # You should have received a copy of the GNU General Public License     #
 * # along with this program.  If not, see <http://www.gnu.org/licenses/>. #
 * #########################################################################
 **/

namespace de\flashpixx\evalphp;

/** class for the menu **/
class menu {
    
    /** creates admin menu **/
    static function adminmenu() {
        add_options_page("Eval PHP", "Eval PHP", "administrator", "fpx_evalphp_option", get_class()."::renderMain");
    }
    
    
    /** shows the admin panel with actions **/
    static function optionfields() {
        register_setting("evalphp_option", "fpx_evalphp_option", get_class()."::validate");
        
        add_settings_section("evalphp_option",      __("Main Options", "evalphp"),                   get_class()."::render_mainsection",        "evalphp_optionglobal");
        add_settings_field("definedonly",           __("Run defined code only", "evalphp"),          get_class()."::render_definedonly",        "evalphp_optionglobal",      "evalphp_option");
        add_settings_field("e_error",               __("Show errors (E_ERROR)", "evalphp"),          get_class()."::render_e_error",            "evalphp_optionglobal",      "evalphp_option");
        add_settings_field("e_warning",             __("Show warnings (E_WARNING)", "evalphp"),      get_class()."::render_e_warning",          "evalphp_optionglobal",      "evalphp_option");
        add_settings_field("e_parse",               __("Show parse errors (E_PARSE)", "evalphp"),    get_class()."::render_e_parse",            "evalphp_optionglobal",      "evalphp_option");
        add_settings_field("e_notice",              __("Show notice (E_NOTICE)", "evalphp"),         get_class()."::render_e_notice",           "evalphp_optionglobal",      "evalphp_option");
        add_settings_field("e_deprecated",          __("Show deprecated (E_DEPRECATED)", "evalphp"), get_class()."::render_e_deprecated",       "evalphp_optionglobal",      "evalphp_option");
        add_settings_field("disallowed",            __("Disallowed keywords", "evalphp"),            get_class()."::render_disallowed",         "evalphp_optionglobal",      "evalphp_option");
        
        add_settings_section("evalphp_option",      __("Defined Functions", "evalphp"),              get_class()."::render_functionsection",    "evalphp_optionfunction");
        
        $option = get_option("fpx_evalphp_option");
        foreach( array_keys($option["functions"]) as $key) {
            $vis = "<span class=\"toggle\" rel=\"".str_replace(array(" ", ".", ",", "#"), null, $key)."\">(".__("show", "evalphp").")</span>";
            add_settings_field("function_".$key,    sprintf(__("Function [%s]", "evalphp"), $key)." ".$vis,    get_class()."::render_function",           "evalphp_optionfunction",    "evalphp_option", $key);
        }
        add_settings_field("function_new",    __("new function", "evalphp"),    get_class()."::render_newfunction",           "evalphp_optionfunction",    "evalphp_option");
        
    }
    
    
    /** validate the form input 
     * @param $pa form data
     * @return validated data
     **/
	static function validate($pa)
    {
        $option = get_option("fpx_evalphp_option");
        
        $option["rundefinedonly"]           = !empty($pa["rundefinedonly"]);
        $option["showerrors"][E_ERROR]      = !empty($pa["e_error"]);
        $option["showerrors"][E_WARNING ]   = !empty($pa["e_warning"]);
        $option["showerrors"][E_PARSE]      = !empty($pa["e_parse"]);
        $option["showerrors"][E_NOTICE]     = !empty($pa["e_notice"]);
        $option["showerrors"][E_DEPRECATED] = !empty($pa["e_deprecated"]);

        // add disallowed codes
        $option["disallowed"] = array_map( "trim", explode(" ", $pa["disallowed"]) );
        
        
        // remove functions
        $functiondelete = array_values(array_filter(array_keys($pa), function($el) { return strpos($el, "functiondelete_") === 0; }));
        foreach($functiondelete as $key)
            unset($option["functions"][str_replace("functiondelete_", null, $key)]);
        
        // update functions
        $functionnames  = array_values(array_filter(array_keys($pa), function($el) { return strpos($el, "function_") === 0; }));
        foreach($functionnames as $key)
        {
            $lc = str_replace("function_", null, $key);
            if (array_key_exists($lc, $option["functions"]))
                $option["functions"][$lc] = $pa[$key];
        }
        
        // add a new function
        if ( (!empty($pa["functionnew"])) && (!empty($pa["functionnewsource"])) )
        {
            $lc = str_replace(" ", null, $pa["functionnew"]);
            while (array_key_exists($lc, $option["functions"]))
                $lc = str_replace(" ", null, $pa["functionnew"]).mt_rand();
            
            $option["functions"][$lc] = $pa["functionnewsource"];
        }
        
        
        return $option;
    }
    
    
    /** render the option page **/
	static function renderMain() {
		echo "<div class=\"wrap\"><h2>Eval PHP ".__("Configuration", "evalphp")."</h2>\n";
        echo "<p>".__("This plugin can be used for embedding native PHP code within blog data, so the code is executated if the data is shown.", "evalphp")."<br><strong>".__("This plugin can be a security risk, because native code is run within the blog environment!", "evalphp")."</strong></p>";
		echo "<form method=\"post\" action=\"options.php\">";
		settings_fields("evalphp_option");
		do_settings_sections("evalphp_optionglobal");
        do_settings_sections("evalphp_optionfunction");
		echo "<p class=\"submit\"><input type=\"submit\" name=\"submit\" class=\"button-primary\" value=\"".__("Save Changes")."\"/></p>\n";
		echo "</form></div>\n";
	}
    
    
    
    static function render_mainsection() {
        echo "<p>".__("Within the main section the global parameters can be defined. With the \"definedonly\" parameter only functions can be used, that are definied here within the settings. Each PHP message can be enabled / disabled, so that different messages can be ignored. Also a list with denied keywords can be added (each keyword is seperated by spaces).", "evalphp")."</p>";
    }
    
    static function render_definedonly() {
        $options = get_option("fpx_evalphp_option");
        echo "<input name=\"fpx_evalphp_option[rundefinedonly]\" type=\"checkbox\" value=\"1\" ".($options["rundefinedonly"] ? "checked" : null)." />";
    }
    
    static function render_e_error() {
        $options = get_option("fpx_evalphp_option");
        echo "<input name=\"fpx_evalphp_option[e_error]\" type=\"checkbox\" value=\"1\" ".($options["showerrors"][E_ERROR] ? "checked" : null)." />";
    }
    
    static function render_e_warning() {
        $options = get_option("fpx_evalphp_option");
        echo "<input name=\"fpx_evalphp_option[e_warning]\" type=\"checkbox\" value=\"1\" ".($options["showerrors"][E_WARNING] ? "checked" : null)." />";
    }
    
    static function render_e_parse() {
        $options = get_option("fpx_evalphp_option");
        echo "<input name=\"fpx_evalphp_option[e_parse]\" type=\"checkbox\" value=\"1\" ".($options["showerrors"][E_PARSE] ? "checked" : null)." />";
    }
    
    static function render_e_notice() {
        $options = get_option("fpx_evalphp_option");
        echo "<input name=\"fpx_evalphp_option[e_notice]\" type=\"checkbox\" value=\"1\" ".($options["showerrors"][E_NOTICE] ? "checked" : null)." />";
    }
    
    static function render_e_deprecated() {
        $options = get_option("fpx_evalphp_option");
        echo "<input name=\"fpx_evalphp_option[e_deprecated]\" type=\"checkbox\" value=\"1\" ".($options["showerrors"][E_DEPRECATED] ? "checked" : null)." />";
    }
    
    static function render_disallowed() {
        $options = get_option("fpx_evalphp_option");
        echo "<textarea name=\"fpx_evalphp_option[disallowed]\" cols=\"80\" rows=\"5\">".implode(" ", $options["disallowed"])."</textarea>";
    }
    
    
    
    static function render_functionsection() {
        echo "<p>".__("Within this section code blocks can be definied, that can be included on a plugin action. You can not define any function or classes, because the code is executated within a function call.", "evalphp")."</p>";
    }
    
    static function render_function($key) {
        $options = get_option("fpx_evalphp_option");
        echo "<input name=\"fpx_evalphp_option[functiondelete_".$key."]\" type=\"checkbox\" value=\"".$key."\" /> <label for=\"fpx_evalphp_option[functiondelete ".$key."]\">".__("remove", "evalphp")."</label>";
        echo "</td><td>";
        
        $denied = array();
        foreach($options["disallowed"] as $val)
            if (!empty($val) && (strpos($options["functions"][$key], $val) !== false))
                array_push($denied, $val);
        if (!empty($denied))
            echo "<strong>".__("The following keywords are denied for using", "evalphp").":</strong> <em>".implode(", ", $denied)."</em><br/>";
        
        echo "<textarea class=\"source\" id=\"".str_replace(array(" ", ".", ",", "#"), null, $key)."\" name=\"fpx_evalphp_option[function_".$key."]\" cols=\"80\" rows=\"5\" wrap=\"soft\">".$options["functions"][$key]."</textarea>";
    }
    
    static function render_newfunction() {
        echo "<input name=\"fpx_evalphp_option[functionnew]\" size=\"30\" type=\"text\" />";
        echo "</td><td>";
        echo "<textarea name=\"fpx_evalphp_option[functionnewsource]\" cols=\"80\" rows=\"5\" wrap=\"soft\"></textarea>";
    }

}

?>
