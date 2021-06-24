let ajaxUrl = '';

function  isFunction(functionToCheck) {
    if (functionToCheck && {}.toString.call(functionToCheck) === '[object Function]') {
        return true;
    } else {
        return false;
    }
}

function ajax(e) {
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
        try {
            (t = new XMLHttpRequest()).overrideMimeType;
            t.overrideMimeType("text/" + e.type);
        } catch (e) {
            try {
                t = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                try {
                    t = new ActiveXObject("Microsoft.XMLHTTP");
                } catch (e) {}
            }
        }
        if (!t) return console.log("Creating instance of XMLHTTP error!"), !1;
        (t.onreadystatechange = function () {
            4 === t.readyState && ((201 === t.status || 200 === t.status)? !0 === isFunction(e.success) && e.success(JSON.parse(t.responseText)) : !0 === isFunction(e.error) && e.error(t.responseText));
        }),
        
        t.onload = () => resolve(JSON.parse(t.responseText));
        t.onerror = () => reject(t.statusText);
        t.open(e.method, e.url, !0);
        t.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        t.send(form);
    });    
}


function init(url)
   ajaxUrl = url;
}

function run(){
     ajax({
        method: "POST",
        url: 'post.php',
        data: {},
        type: 'json'      
    }).then((value) => {   
        postMessage(value);    
        setTimeout(function(){
          run();
      },1000);
    });  
}

init(); 
self.onmessage = function (msg) {
    ajax({
        method: "POST",
        url: 'post.php',
        data: {
            message: msg.data
        },
        type: 'json'      
  }).then((value) => {      
          postMessage(value);
  });  
}


