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

/** class for doing content filtering **/
class filter {
    
    /** content filter function for get the tags
     * @param $pcContent Content
     **/
    static function runInline($pcContent)
    {
        return preg_replace_callback("!\[evalphp(.*)\](.*)\[/evalphp\]!isU", "de\\flashpixx\\evalphp\\filter::actionInline", $pcContent);
    }
    
    /** content filter function for get the tags
     * @param $pcContent Content
     **/
    static function runInclude($pcContent)
    {
        return preg_replace_callback("!\[evalphp(.*)\]!isU", "de\\flashpixx\\evalphp\\filter::actionInclude", $pcContent);
    }

    
    
    /** create action on inline code
     * @param $pa Array with founded regular expressions
     * @return output
     **/
    static function actionInline($pa)
    {
        if ( (empty($pa)) || (count($pa) != 3) )
            return null;
        
        return self::runCode(self::splitParameter($pa[1]), $pa[2]);
    }

    /** create action on include code only
     * @param $pa Array with founded regular expressions
     * @return output
     **/
    static function actionInclude($pa)
    {
        if ( (empty($pa)) || (count($pa) != 2) )
            return null;
        
        return self::runCode(self::splitParameter($pa[1]));
    }
    
    
    
    /** run the code
     * @param $param input parameter
     * @param $code input code
     **/
    private static function runCode($param, $code = null)
    {
        $option = get_option("fpx_evalphp_option");
        
        // setup includes
        $lcCode = "";
        foreach($param["include"] as $val)
        if (array_key_exists($val, $option["functions"]))
            $lcCode .= $option["functions"][$val];
        
        // set code if allowed
        if (!$option["rundefinedonly"])
            $lcCode .= $code;
        
        // check disallowed
        foreach($option["disallowed"] as $val)
        if ((!empty($val)) && (strpos($lcCode, $val) !== false))
        {
            if (current_user_can("edit_pages") || current_user_can("edit_posts"))
                return sprintf(__("The keyword [%s]  is denied for using", "evalphp"), $val);
            return null;
        }
        
        // run code and get error message
        if (empty($lcCode))
            return null;
        
        $content = self::evalrun($lcCode);
        $error   = self::generateErrorMessage($option);
        if (!empty($error))
            return $error;
        
        return $content;
    }
    
    
    /** splits the tag parameter
     * @param $pa input string
     * @return array with parameter data
     **/
    private static function splitParameter($pa)
    {
        // split the parameters
        $param		= array();
        $tagparam   = preg_split('/\G(?:"[^"]*"|\'[^\']*\'|[^"\'\s]+)*\K\s+/', $pa, -1, PREG_SPLIT_NO_EMPTY);
        foreach($tagparam as $val)
        {
            // remove double / single quotes
            $lcTag = str_replace("\"", null, $val);
            $lcTag = str_replace("'", null, $lcTag);
            
            // find first occurence of = and split the string
            $laTag = preg_split('/=/', $lcTag, 2);
            if (count($laTag) == 2)
                $param[trim($laTag[0])] = trim($laTag[1]);
        }
        
        if (array_key_exists("include", $param))
            $param["include"] = array_map( "trim", explode(" ", $param["include"]));
        else
            $param["include"] = array();
        
        
        return $param;
    }

    
    /** method for returning errors, that stop the script execution **/
    static function catchevalerror()
    {
        $error = self::generateErrorMessage( get_option("fpx_evalphp_option") );
        if (!empty($error))
            echo $error;
    }
    
    
    /** creates the error message
     * @param $option plugin options
     * @return string representation of the error
     **/
    static function generateErrorMessage($option)
    {
        $lc = null;
        $error = error_get_last();

        if (  (!empty($error)) && (strpos($error["file"], "evalphp/filter.class.php") !== false)  )
        {
            if (!current_user_can("edit_pages") && !current_user_can("edit_posts"))
                return $lc;
            
            if (array_key_exists($error["type"], $option["showerrors"]) && $option["showerrors"][$error["type"]])
                switch ($error["type"])
            {
                case E_ERROR      : $lc = "<p><strong>".__("PHP error", "evalphp").":</strong> ".$error["message"]; break;
                case E_WARNING    : $lc = "<p><strong>".__("PHP warning", "evalphp").":</strong> ".$error["message"]; break;
                case E_PARSE      : $lc = "<p><strong>".__("PHP parse error", "evalphp").":</strong> ".$error["message"]; break;
                case E_NOTICE     : $lc = "<p><strong>".__("PHP notice", "evalphp").":</strong> ".$error["message"]; break;
                case E_DEPRECATED : $lc = "<p><strong>".__("PHP deprecated", "evalphp").":</strong> ".$error["message"]; break;
            }
        }
        
        return $lc;
    }
    
    
    /** eval function
     * @return the output buffer
     **/
    private static function evalrun()
    {
        if (func_num_args() != 1)
            return null;
        
        ob_start();
        eval(func_get_arg(0));
        $content = ob_get_contents();
        ob_end_clean();
        
        return $content;
    }
}

?>
