(function(){CKEDITOR.dialog.add("templates",function(c){function l(h,j){var f=CKEDITOR.dom.element.createFromHtml('<a href="javascript:void(0)" tabIndex="-1" role="option" ><div class="cke_tpl_item"></div></a>'),b='<table style="width:350px;" class="cke_tpl_preview" role="presentation"><tr>';h.image&&j&&(b+='<td class="cke_tpl_preview_img"><img src="'+CKEDITOR.getUrl(j+h.image)+'"'+(CKEDITOR.env.ie6Compat?' onload="this.width=this.width"':"")+' alt="" title=""></td>');b+='<td style="white-space:normal;"><span class="cke_tpl_title">'+h.title+"</span><br/>";h.description&&(b+="<span>"+h.description+"</span>");f.getFirst().setHtml(b+"</td></tr></table>");f.on("click",function(){var k=h.html,m=CKEDITOR.dialog.getCurrent();m.getValueOf("selectTpl","chkInsertOpt")?(c.on("contentDom",function(n){n.removeListener();m.hide();n=new CKEDITOR.dom.range(c.document);n.moveToElementEditStart(c.document.getBody());n.select(1);setTimeout(function(){c.fire("saveSnapshot")},0)}),c.fire("saveSnapshot"),c.setData(k)):(c.insertHtml(k),m.hide())});return f}function e(m){var n=m.data.getTarget(),h=g.equals(n);if(h||g.contains(n)){var j=m.data.getKeystroke(),k=g.getElementsByTag("a"),o;if(k){if(h){o=k.getItem(0)}else{switch(j){case 40:o=n.getNext();break;case 38:o=n.getPrevious();break;case 13:case 32:n.fire("click")}}o&&(o.focus(),m.data.preventDefault())}}}CKEDITOR.skins.load(c,"templates");var g,d="cke_tpl_list_label_"+CKEDITOR.tools.getNextNumber(),i=c.lang.templates,a=c.config;return{title:c.lang.templates.title,minWidth:CKEDITOR.env.ie?440:400,minHeight:340,contents:[{id:"selectTpl",label:i.title,elements:[{type:"vbox",padding:5,children:[{id:"selectTplText",type:"html",html:"<span>"+i.selectPromptMsg+"</span>"},{id:"templatesList",type:"html",focus:!0,html:'<div class="cke_tpl_list" tabIndex="-1" role="listbox" aria-labelledby="'+d+'"><div class="cke_tpl_loading"><span></span></div></div><span class="cke_voice_label" id="'+d+'">'+i.options+"</span>"},{id:"chkInsertOpt",type:"checkbox",label:i.insertOption,"default":a.templates_replaceContent}]}]}],buttons:[CKEDITOR.dialog.cancelButton],onShow:function(){var f=this.getContentElement("selectTpl","templatesList");g=f.getElement();CKEDITOR.loadTemplates(a.templates_files,function(){var s=(a.templates||"default").split(",");if(s.length){var r=g;r.setHtml("");for(var u=0,q=s.length;u<q;u++){for(var t=CKEDITOR.getTemplates(s[u]),o=t.imagesPath,t=t.templates,b=t.length,m=0;m<b;m++){var h=l(t[m],o);h.setAttribute("aria-posinset",m+1);h.setAttribute("aria-setsize",b);r.append(h)}}f.focus()}else{g.setHtml('<div class="cke_tpl_empty"><span>'+i.emptyListMsg+"</span></div>")}});this._.element.on("keydown",e)},onHide:function(){this._.element.removeListener("keydown",e)}}})})();