!function(e){typeof define=="function"&&define.amd?define(["index","jquery","jquery.ba-resize"],e):TabsAccordion=e(Index,jQuery)}(function(e,t){var n=0,r="tabsaccordion",i=t(window),s=t(document.documentElement).addClass("js"),o=t(document.body);t.resize.throttleWindow=!1,t.fn.TabsAccordion=function(s){function u(s,u){var a=r+"-"+n++,f=t(s),l,c,h,p,d={version:"1.1.2",type:f.hasClass("accordion")&&"accordion"||f.hasClass("tabs")&&"tabs",create:function(){l=f.children(),c=l.children(":first-child");if(d.index)var n=d.index.curr;return(d.index=e(l.length-1)).loop=!0,n&&d.index.set(n),d.type==="tabs"&&f.prepend((c=d.tabsCreateTablist(c).children()).end()),h=(d.type==="tabs"?c.parent():f).attr("role","tablist"),c.attr({id:function(e){return this.id||a+"-tab-"+e},role:"tab"}),(p=l.map(function(e){return t(this).attr({"aria-labelledby":c[e].id,id:this.id||a+"-panel-"+e,role:"tabpanel"}).children().slice(1).wrapAll("<div><div></div></div>").parent().parent().get()})).each(d.collapse),f.attr({id:s.id||a,tabindex:0}).on("click."+a,d.type==="accordion"&&"> * > :first-child"||"> :first-child > *",function(e){d.goTo(c.index(e.target))}).on("keydown."+a,function(e){if(e.target!==s)return;var t={37:"prev",38:"prev",39:"next",40:"next"}[e.keyCode];t&&(e.preventDefault(),d.goTo(d.index[t]))}).on("resize."+a,d.resize).trigger("create"),u.saveState&&d.extensions.saveState(u.saveState),u.responsiveSwitch&&d.extensions.responsiveSwitch(u.responsiveSwitch),u.hashWatch&&d.extensions.hashWatch(),u.pauseMedia&&d.extensions.pauseMedia(),typeof d.index.curr!="number"&&d.index.set(0),setTimeout(function(){f.addClass("transition")}),d.expand(d.index.curr)},destroy:function(e){return d.type==="tabs"?(f.height("auto"),h.remove()):(c.removeAttr("role").filter('[id^="'+a+'"]').removeAttr("id"),h.removeAttr("role")),l.removeAttr("aria-expanded aria-labelledby role").filter('[id^="'+a+'"]').removeAttr("id"),p.children().children().unwrap().unwrap(),e||f.removeData(r).removeData("responsiveBreakpoint."+a),f.add([window,document.body]).off("."+a).end().removeAttr("aria-activedescendant tabindex").removeClass(d.type).filter('[id^="'+a+'"]').removeAttr("id").end(),d},resize:function(){return d.type==="tabs"?f.height(h.outerHeight()+l.eq(d.index.curr).outerHeight()):d.type==="accordion"&&l[d.index.curr].ariaExpanded&&p.eq(d.index.curr).height(p.eq(d.index.curr).children().outerHeight()),d},expand:function(e){var t=l.eq(e).attr("aria-expanded",l[e].ariaExpanded=!0);return d.resize().type==="tabs"&&c.eq(e).addClass("current"),f.attr("aria-activedescendant",l[d.index.curr].id).trigger("expand",[e,t]),d},collapse:function(e){var t=l.eq(e).attr("aria-expanded",l[e].ariaExpanded=!1);return d.type==="tabs"?c.eq(e).removeClass("current"):p.eq(e).height(0),f.trigger("collapse",[e,t]),d},goTo:function(e){return d.index.curr!==e&&typeof d.index.curr=="number"&&d.collapse(d.index.curr),d.index.set(e),d[d.type==="accordion"&&l.eq(e).prop("ariaExpanded")?"collapse":"expand"](d.index.curr)},tabsCreateTablist:u.tabsCreateTablist||function(e){for(var n=0,r="";n<e.length;n++)r+="<li>"+e[n].innerHTML+"</li>";return t("<ul>"+r+"</ul>")},extensions:{hashWatch:function(){var e=l.index(l.filter(location.hash));e>=0&&d.goTo(e),o.on("click."+a,'a[href^="#"]:not([href="#"])',function(t){(e=l.index(l.filter(this.getAttribute("href"))))>=0&&d.goTo(e)})},saveStateLoaded:!1,saveState:function(e){if(typeof e!="object")return;var t={remove:function(){e.removeItem(a)},load:function(){var t=e.getItem(a),n=JSON.parse(t);n&&n.current&&d.index.set(n.current),d.extensions.saveStateLoaded=!0},save:function(){e.setItem(a,JSON.stringify({current:d.index.curr,expanded:l[d.index.curr].ariaExpanded}))}};return d.extensions.saveStateLoaded||t.load(),i.on("unload."+a,t.save),t},responsiveSwitch:function(e){function t(){for(var e=0,t=0;e<c.length;e++)t+=c.eq(e).outerWidth(!0);return t}function n(e){var t=d.index.curr,n=l[t].ariaExpanded;d.destroy(!0),f.addClass(d.type=e),d.index.set(t),d.create(),f.trigger("typechange",e)}function r(){var t=f.outerWidth()<=e?"accordion":"tabs";d.type!==t&&n(t)}e==="tablist"&&(d.type==="tabs"?f.data("responsiveBreakpoint."+a,e=t()):e=f.data("responsiveBreakpoint."+a)),f.on("resize."+a,r)},pauseMedia:function(){if(typeof Modernizr=="undefined"||!Modernizr.audio||!Modernizr.video||!f.find("audio, video").length)return;f.on("collapse."+a,function(e,t,n){n.find("audio, video").each(function(){this.pause()})})}}};return d.create()}var s=s||{},a=Array.prototype.slice.call(arguments,1);return this.each(function(e){var n=t(this);return n.data(r)?n.data(r)[s].apply(this,a):n.data(r,u(this,s))})}});