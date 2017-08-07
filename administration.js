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


jQuery(document).ready( function() {
                       
    jQuery(".source").hide();
                       
    jQuery(".toggle").click( function() { 
        jQuery(this).data("show", !jQuery(this).data("show"));
        jQuery("#"+jQuery(this).attr("rel")).toggle("slow");
       
        if (jQuery(this).data("show"))
            jQuery(this).html("("+evalphp_messages.hide+")");
        else
            jQuery(this).html("("+evalphp_messages.show+")");
    });
                       
});