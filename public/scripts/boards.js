function editor(el) {
    if(typeof(Jodit) === 'undefined') {
        let promise = new Promise(function(resolve, reject) {
            setTimeout(function() {
                resolve( editor(el) );
             });
        });
        return promise;
    } else {
        return new Jodit(el, { autofocus: !0, height: "280", toolbarAdaptive: 1, allowResizeX: !1, allowResizeY: !1 });
    }  
}

function  isFunction(functionToCheck) {
    if (functionToCheck && {}.toString.call(functionToCheck) === '[object Function]') {
        return true;
    } else {
        return false;
    }
}

class Boards {

    jodit;
    static  chatbox;
    static  messenger;
    scripts;
    
    constructor() {
        this.scripts = assets + '/scripts/';
    }

    ready(fn) {
        if (document.readyState === "complete" || document.readyState === "interactive") {
            setTimeout(fn, 1);
        } else {
            document.addEventListener("DOMContentLoaded", fn);
        }
    } 

    objectExistById(id){
        let promise = new Promise(function(resolve, reject) {
            let obj = document.getElementById(id);
            if (obj === null) {
               resolve (false); 
            } else {
               resolve (true);
            }
        });
        return promise;
    }

    click(el, fn) {
        let element = document.querySelector(el);
        element.addEventListener("click",fn,false);
    }

    appendHtml(el, str) {
        if(typeof(el) !== "object") {
            el = document.querySelector(el);
        }
        let div = document.createElement('div');
        div.innerHTML = str;
        el.insertAdjacentElement('beforeend', div);
    }
    prependHtml(el, str) {
        if(typeof(el) !== "object") {
            el = document.querySelector(el);
        }
        let div = document.createElement('div');
        div.innerHTML = str;
        el.insertAdjacentElement('beforebegin', div);
    }
    
    getHeight(el, type = 'height') {
        if (type === 'inner')  // .innerWidth()
            return el.clientHeight;
        else if (type === 'outer')  // .outerWidth()
            return el.offsetHeight;
        const s = window.getComputedStyle(el, null);
        if (type === 'height' || !type)  // .height()
            return el.clientHeight - parseInt(s.getPropertyValue('padding-top')) - parseInt(s.getPropertyValue('padding-bottom'));
        else if (type === 'full')  // .outerWidth( includeMargins = true )
            return el.offsetHeight + parseInt(s.getPropertyValue('margin-top')) + parseInt(s.getPropertyValue('margin-bottom'));
        return null;
    }
    
    show(el, speed = 300, display = "flex"){
        if(typeof(el) !== "object") {
            el = document.querySelector(el);
        }
        el.style.display = display;
        el.style.visibility = "visible";
        el.setAttribute('data-visibility',"show");
        setTimeout(function(){ 
            el.style.opacity = 1;
        }, speed);
    }

    hide(el, speed = 300){
        if(typeof(el) !== "object") {
            el = document.querySelector(el);
        }
        el.style.opacity = 0;
        setTimeout(function(){ 
            el.style.display = "none";
            el.style.visibility = "hidden";
            el.setAttribute('data-visibility', "hide");
        }, speed);
    }
    
    toggle(el, cl) {
        document.querySelector(el).classList.toggle(cl);
    }

    ajax(e) {
        return new Promise((resolve, reject) => {
            let t = !1;
            let form;
            if(e.data === 'undefined') {
                form = null;
            } else {
                form = new FormData();
                for ( var key in e.data ) {
                    form.append(key, e.data[key]);
                }
            }
            if (window.XMLHttpRequest) (t = new XMLHttpRequest()).overrideMimeType && t.overrideMimeType("text/" + e.type);
            else if (window.ActiveXObject)
                try {
                    t = new ActiveXObject("Msxml2.XMLHTTP");
                } catch (e) {
                    try {
                        t = new ActiveXObject("Microsoft.XMLHTTP");
                    } catch (e) {}
                }
            if (!t) return console.log("Creating instance of XMLHTTP error!"), !1;
            (t.onreadystatechange = function () {
                4 === t.readyState && ((201 === t.status || 200 === t.status)? !0 === isFunction(e.success) && e.success(JSON.parse(t.responseText)) : !0 === isFunction(e.error) && e.error(t.responseText));
            }),
            
            t.onload = () => resolve(t.responseText);
            t.onerror = () => reject(t.statusText);
            t.open(e.method, e.url, !0);
            t.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            t.send(form);
        });    
    }
    
    getScript(source, load = 1, callback) {
        let script = document.createElement('script');
        let prior = document.getElementsByTagName('script')[0];
        script.async = load;

        script.onload = script.onreadystatechange = function( _, isAbort ) {
            if(isAbort || !script.readyState || /loaded|complete/.test(script.readyState) ) {
                script.onload = script.onreadystatechange = null;
                script = undefined;

                if(!isAbort && callback) setTimeout(callback, 0);
            }
        };

        script.src = source;
        prior.parentNode.insertBefore(script, prior);
    }
    
    getStyle(source) {
        let head = document.head;
        let link = document.createElement("link");

        link.type = "text/css";
        link.rel = "stylesheet";
        link.href = assets + '/css/' + source;

        head.appendChild(link);
    }
      
    async initJodit(el) {
        if(typeof(el) !== "object") {
            el = document.querySelector(el);
        }
        this.getScript(this.scripts+'jodit.min.js');
        this.getStyle('jodit.min.css');
        return await editor(el).then((o) => {
            this.jodit = o;
        });
    }
  
    initMessenger(){}

    setNewPlot(o) {
   
        let csrfName = document.getElementById('csrf_name').value;
        let csrfValue = document.getElementById('csrf_value').value;
   
		this.ajax({
		  method: "POST",
		  url: ajaxUrl,
		  data: {
              module: 'plot',
              route: 'set',
              topic: 	o.topic,
              content: o.content,
              board_id: o.id,
              csrf_name : csrfName,
              csrf_value : csrfValue
		  
		  },
		  type: 'json',
		  error: function(mydata){
              alert('error');
			console.log(mydata);
		  },
		  success: function(mydata){					
			if(mydata.redirect)
				window.location.replace(mydata.redirect);
			if(mydata.warn)
                alert(mydata.warn);
				// $('.card-header').after(mydata.warn);
				// setTimeout(function(){ $('.alert').fadeOut(); }, 5000);
				// setTimeout(function(){ $('.alert').remove(); }, 5600);
            }
		});	
    }
    
    post(o) {
        let csrfName = document.getElementById('csrf_name').value;
        let csrfValue = document.getElementById('csrf_value').value;
        return this.ajax({
              method: "POST",
              url: ajaxUrl,
              data: {
              'module': 'plot',
              'route':  o.route,
              'id': 	o.id,
              'url':    o.url,
              "csrf_name" : csrfName,
              "csrf_value" : csrfValue
              
              },
              type: 'json',
              error: function(mydata){
                alert('error');
                console.log(mydata);
              },
              success: function(mydata){					
                return mydata;
              }
                
        });
    }
}
let B = new Boards;