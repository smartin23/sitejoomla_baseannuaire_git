/**
 * @package     Extly.Addons
 * @subpackage  categoriesfilterapp - Categories Filter Addon allows to apply a categories filter to the search form.
 * 
 * @author      Prieco S.A.
 * @copyright   Copyright (C) 2007 - 2012 Prieco, S.A. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL 
 * @link        http://www.extly.com http://support.extly.com 
 */

/*jslint plusplus: true, browser: true, sloppy: true */
/*global jQuery, SobiProUrl*/

var ExtSearchHelperAddon = function (sectionid, searchformid, sidlistid) {
        this.sectionid = sectionid;
		this.searchformid = searchformid;
		this.searchform = jQuery(this.searchformid);
		this.sidlistid = sidlistid;
		this.sidlist = jQuery(sidlistid);		
		
        this.new_values = [];
        this.sid_list = [];		

        this.cache = {};
        this.lastXhr = null;
    };
	
ExtSearchHelperAddon.prototype.extractFormValues = function () {
    var aparms, i, avalue, sname, svalue;
    this.new_values = [];
    this.sid_list = [];

    aparms = this.searchform.serialize().split("&");
    for (i = 0; i < aparms.length; i++) {
        avalue = aparms[i].split("=");
        sname = avalue[0];
        svalue = this.decode(avalue[1]);
        this.addCategory(sname, svalue);
    }
    this.fillSidList();
    return this.sid_list;
};

ExtSearchHelperAddon.prototype.addCategory = function (sname, svalue) {
    if (sname.match(/^to_sid_list/) && svalue && svalue.length > 0) {
        var strOut = svalue.replace(/\D+/g, '');
        this.sid_list.push(strOut);
    }
};

ExtSearchHelperAddon.prototype.fillSidList = function () {
    var v = null;
    if (this.sid_list.length > 0) {
        v = this.sid_list.join(",");
        this.sidlist.val(v);
    }
    return v;
};

ExtSearchHelperAddon.prototype.decode = function (str) {
    str = decodeURIComponent(str);
    return str.replace(/\+/g, " ");
};
