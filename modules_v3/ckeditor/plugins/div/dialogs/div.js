(function(){function a(e,d,c){if(!d.is||!d.getCustomData("block_processed")){d.is&&CKEDITOR.dom.element.setMarker(c,d,"block_processed",!0),e.push(d)}}function b(e,d){var c=CKEDITOR.tools.extend({},CKEDITOR.dtd.$blockLimit);delete c.div;e.config.div_wrapTable&&(delete c.td,delete c.th);var f=CKEDITOR.dtd.div,h={},g=[];return{title:e.lang.div.title,minWidth:400,minHeight:165,contents:[{id:"info",label:e.lang.common.generalTab,title:e.lang.common.generalTab,elements:[{type:"hbox",widths:["50%","50%"],children:[{id:"elementStyle",type:"select",style:"width: 100%;",label:e.lang.div.styleSelectLabel,"default":"",items:[[e.lang.common.notSet,""]],onChange:function(){var m=["info:class","advanced:dir","advanced:style"],o=this.getDialog(),l=o._element&&o._element.clone()||new CKEDITOR.dom.element("div",e.document);this.commit(l,!0);for(var m=[].concat(m),i=m.length,k,n=0;n<i;n++){(k=o.getContentElement.apply(o,m[n].split(":")))&&k.setup&&k.setup(l,!0)}},setup:function(i){for(var j in h){h[j].checkElementRemovable(i,!0)&&this.setValue(j)}},commit:function(i){var k;if(k=this.getValue()){k=h[k];var j=i.getCustomData("elementStyle")||"";k.applyToObject(i);i.setCustomData("elementStyle",j+k._.definition.attributes.style)}}},{id:"class",type:"text",label:e.lang.common.cssClass,"default":""}]}]},{id:"advanced",label:e.lang.common.advancedTab,title:e.lang.common.advancedTab,elements:[{type:"vbox",padding:1,children:[{type:"hbox",widths:["50%","50%"],children:[{type:"text",id:"id",label:e.lang.common.id,"default":""},{type:"text",id:"lang",label:e.lang.link.langCode,"default":""}]},{type:"hbox",children:[{type:"text",id:"style",style:"width: 100%;",label:e.lang.common.cssStyle,"default":"",commit:function(i){var j=this.getValue()+(i.getCustomData("elementStyle")||"");i.setAttribute("style",j)}}]},{type:"hbox",children:[{type:"text",id:"title",style:"width: 100%;",label:e.lang.common.advisoryTitle,"default":""}]},{type:"select",id:"dir",style:"width: 100%;",label:e.lang.common.langDir,"default":"",items:[[e.lang.common.notSet,""],[e.lang.common.langDirLtr,"ltr"],[e.lang.common.langDirRtl,"rtl"]]}]}]}],onLoad:function(){this.foreach(function(k){/^(?!vbox|hbox)/.test(k.type)&&(k.setup||(k.setup=function(l){k.setValue(l.getAttribute(k.id)||"")}),k.commit||(k.commit=function(l){var m=this.getValue();"dir"==k.id&&l.getComputedStyle("direction")==m||(m?l.setAttribute(k.id,m):l.removeAttribute(k.id))}))});var i=this,j=this.getContentElement("info","elementStyle");e.getStylesSet(function(l){var k;if(l){for(var m=0;m<l.length;m++){var n=l[m];n.element&&"div"==n.element&&(k=n.name,h[k]=new CKEDITOR.style(n),j.items.push([k,k]),j.add(k,k))}}j[1<j.items.length?"enable":"disable"]();setTimeout(function(){j.setup(i._element)},0)})},onShow:function(){if("editdiv"==d){var i;(i=(i=(new CKEDITOR.dom.elementPath(e.getSelection().getStartElement())).blockLimit)&&i.getAscendant("div",!0))&&this.setupContent(this._element=i)}},onOk:function(){if("editdiv"==d){g=[this._element]}else{var q=[],w={},p=[],x,o=e.document.getSelection(),v=o.getRanges(),l=o.createBookmarks(),s,m;for(s=0;s<v.length;s++){for(m=v[s].createIterator();x=m.getNextParagraph();){if(x.getName() in c){var u=x.getChildren();for(x=0;x<u.count();x++){a(p,u.getItem(x),w)}}else{for(;!f[x.getName()]&&"body"!=x.getName();){x=x.getParent()}a(p,x,w)}}}CKEDITOR.dom.element.clearAllMarkers(w);v=[];s=null;for(m=0;m<p.length;m++){x=p[m];for(var u=(new CKEDITOR.dom.elementPath(x)).elements,y=void 0,i=0;i<u.length;i++){if(u[i].getName() in c){y=u[i];break}}u=y;u.equals(s)||(s=u,v.push([]));v[v.length-1].push(x)}for(s=0;s<v.length;s++){u=v[s][0];p=u.getParent();for(x=1;x<v[s].length;x++){p=p.getCommonAncestor(v[s][x])}m=new CKEDITOR.dom.element("div",e.document);for(x=0;x<v[s].length;x++){for(u=v[s][x];!u.getParent().equals(p);){u=u.getParent()}v[s][x]=u}for(x=0;x<v[s].length;x++){if(u=v[s][x],!u.getCustomData||!u.getCustomData("block_processed")){u.is&&CKEDITOR.dom.element.setMarker(w,u,"block_processed",!0),x||m.insertBefore(u),m.append(u)}}CKEDITOR.dom.element.clearAllMarkers(w);q.push(m)}o.selectBookmarks(l);g=q}q=g.length;for(w=0;w<q;w++){this.commitContent(g[w]),!g[w].getAttribute("style")&&g[w].removeAttribute("style")}this.hide()},onHide:function(){"editdiv"==d&&this._element.removeCustomData("elementStyle");delete this._element}}}CKEDITOR.dialog.add("creatediv",function(c){return b(c,"creatediv")});CKEDITOR.dialog.add("editdiv",function(c){return b(c,"editdiv")})})();