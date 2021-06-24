let startTime,
    endTime,
    currentX,
    currentY,
    initialX,
    initialY,
    dragItem = document.querySelector("#boards-messenger"),
    container = document.getElementsByTagName("body")[0],
    moveHandler = !1,
    autocompleteinclude = !1,
    h = document.documentElement.clientHeight,
    w = document.documentElement.clientWidth / 2,
    active = !1,
    xOffset = 0,
    yOffset = 0,
    currentConversationId = 0,
    currentLastMessageId = 0,
    unread = 0,
    messengerSnippet = document.getElementById('boards-messenger-snippet'),
    messageIco = document.querySelector("#messenger-ico"),
    messengerList = document.querySelectorAll('[data-msg-list-id]'),
    messengerChat = document.querySelector('.direct-chat-messages'),
    messengerChatButton = document.getElementById('msg-chat-btn'),
    messengerChatInput = document.getElementById('msg-chat-input'),
    messengerNewMessages = [],
    messengerLastEl = null,
    listBtn = document.getElementById('msg-list-btn'),
    list = document.getElementById('msg-list'),
    managerBtn = document.getElementById('msg-manager-btn'),
    manager = document.getElementById('msg-manager'),
    msgStartChatBtn = document.getElementById('msg-start-chat');
function start() {
    startTime = new Date();
}
function end() {
    let endDate = (endTime = new Date()) - startTime;
    return Math.round(endDate);
}    
    
function dragStart(e) {
    start(); 
    "touchstart" === e.type ? ((initialX = e.touches[0].clientX - xOffset), (initialY = e.touches[0].clientY - yOffset)) : ((initialX = e.clientX - xOffset), (initialY = e.clientY - yOffset)), e.target === dragItem && (active = !0);
}
function dragEnd(e) {
    (initialX = currentX),
        (initialY = currentY),
        Math.abs(w + currentX) < 70 && Math.abs(currentY) < 40
            ? (B.hide('#boards-messenger'),
              B.hide("#boards-messenger-snippet"),
              (xOffset = currentX = 0),
              (yOffset = currentY = 0),
              setTimeout(function () {
                  setTranslate(currentX, currentY, document.getElementById("boards-messenger")), document.querySelector("#boards-messenger").classList.remove("x");
              }, 500))
            : ((xOffset = currentX = 0),
              (yOffset = currentY = 0),
              document.querySelector("#boards-messenger").classList.add("boards-messenger-smooth"),
              setTimeout(function () {
                  setTranslate(currentX, currentY, document.getElementById("boards-messenger"));
              }, 100)),
        (active = !1);
    
    if(end() < 500 && e.target.id === 'boards-messenger'){
        if(messengerSnippet.getAttribute('data-visibility') === 'show') {
            B.hide(messengerSnippet);
        } else {
            B.show(messengerSnippet);
        }
    }
}
function drag(e) {
    active &&
        (e.preventDefault(),
        "touchmove" === e.type ? ((currentX = e.touches[0].clientX - initialX), (currentY = e.touches[0].clientY - initialY)) : ((currentX = e.clientX - initialX), (currentY = e.clientY - initialY)),
        (xOffset = currentX),
        (yOffset = currentY),
        Math.abs(w + currentX) < 70 && Math.abs(currentY) < 40 ? document.querySelector("#boards-messenger").classList.add("x") : document.querySelector("#boards-messenger").classList.remove("x"),
        setTranslate(currentX, currentY, dragItem));
}
function setTranslate(e, t, n) {
    n.style.transform = "translate3d(" + e + "px, " + t + "px, 0)";

}

function setConversation(id) {
        messengerSnippet.setAttribute('data-converation-id', id);
}

function postMessage(){
    let id = messengerSnippet.getAttribute('data-converation-id');
    B.ajax({
        method: 'POST',
        url: ajaxUrl,
        data: { 
            module: 'messenger', 
            route: 'post', 
            cid: id,
            body: messengerChatInput.value
        },
        dataType: "json"
    }).then((val) => {
       messengerChatInput.value = '';
       B.appendHtml(messengerChat, JSON.parse(val).html);
    });
}

function drawConverstaion(id) {
     B.ajax({
        method: 'POST',
        url: ajaxUrl,
        data: { 
            module: 'messenger', 
            route: 'get', 
            cid: id, 
        },
        dataType: "json"
    }).then((val) => {
       B.appendHtml(messengerChat, JSON.parse(val).html);
    });
}

function messengerCheck() {
    B.ajax({
        method: 'POST',
        url: ajaxUrl,
        data: { 
            module: 'messenger', 
            route: 'check', 
            id: 1, 
            cid: 1, 
        },
        dataType: "json",
        success: function (response) {
            console.log(response);
        }
    });
}

function msgList() {
    B.ajax({
        method: 'POST',
        url: ajaxUrl,
        data: { 
            module: 'messenger', 
            route: 'list',  
        },
        dataType: "json",
        success: function (response) {
            let setMsgList = document.getElementById('msg-list-ul');
            setMsgList.innerHTML = '';
            B.appendHtml(setMsgList, response);
            setTimeout(function(){
                messengerList = document.querySelectorAll('[data-msg-list-id]');
                for(item of messengerList) {
                item.addEventListener("click", function(){ 
                let cid = this.getAttribute('data-msg-list-id');
                list.classList.remove('direct-chat-contacts-open');
                manager.classList.remove('direct-chat-contacts-open');
                messengerChat.innerHTML = '';
                setConversation(cid);
                drawConverstaion(cid);
                setTimeout(function(){
                    let handler = document.getElementsByClassName('direct-chat-msg');
                    if(handler.length > 0) {
                            messengerLastEl = handler[handler.length - 1].getAttribute('data-messageid');
                        }
                    getNewMessage();
                },5000);
        });
    }
                
            },100);
        }
    });
}

function getNewMessage(){
    
     B.ajax({
        method: 'POST',
        url: ajaxUrl,
        data: { 
            module: 'messenger', 
            route: 'getNew', 
            mid: messengerLastEl,
            cid: messengerSnippet.getAttribute('data-converation-id')
        },
        dataType: "json",
        success: function (response) { console.log(response.length);
            if (response.length !== 0) {
                B.appendHtml(messengerChat, response.html);  
                messengerLastEl = response.id;
            }
            setTimeout(function(){
                    getNewMessage();
                },5000);
        }
    });
}

function messengerUsers(){
    B.getScript(assets+'/scripts/autoComplete.min.js');
    B.ajax({
        method: 'POST',
        url: ajaxUrl,
        data: { 
            module: 'messenger', 
            route: 'find', 
        },
        dataType: "json"
    }).then((v) => {
        B.appendHtml("#msg-userlist", JSON.parse(v));
        let msgFindInput = document.getElementById('msg-find-user');
        let allUsers = document.querySelectorAll('[data-un]');
        msgStartChatBtn.addEventListener('click', function(e){
            let obj = {};
            e.preventDefault();
            let selectedUsers = document.querySelectorAll("[data-msg-checkbox]");
            selectedUsers.forEach(function(v, k){
                if(v.checked) {
                    obj[k] = v.value;
                }
            });
            B.ajax({
                method: 'POST',
                url: ajaxUrl,
                data: { 
                    module: 'messenger', 
                    route: 'start', 
                    users: JSON.stringify(obj)
                },
                dataType: "json",
                success: function (response) {
                    list.classList.remove('direct-chat-contacts-open');
                    manager.classList.remove('direct-chat-contacts-open');
                    messengerChat.innerHTML = '';
                    setConversation(response.cid);
                    drawConverstaion(response.cid);
                    setTimeout(function(){
                        let handler = document.getElementsByClassName('direct-chat-msg');
                        if(handler.length > 0) {
                            messengerLastEl = handler[handler.length - 1].getAttribute('data-messageid');
                        }
                        getNewMessage();
                    },5000);
                }
            });
        });
        
        msgFindInput.addEventListener('keyup', function(e){
            let f = document.querySelectorAll('[data-un^="'+ e.target.value +'"]');
            console.log({find: e.target.value});
            if(e.target.value.length > 0) {
                allUsers.forEach(function(el){
                    el.classList.add('d-none');
                })
                
                f.forEach(function(el){
                    el.classList.remove('d-none');
                });
            } else {
                 allUsers.forEach(function(el){
                    el.classList.remove('d-none');
                })
            }
        });
    });
}



function initMessenger(){
    let getList = false;
    msgList();
    listBtn.addEventListener("click", function(){
        list.classList.toggle('direct-chat-contacts-open');
        manager.classList.remove('direct-chat-contacts-open');
    });

    managerBtn.addEventListener("click", function(){
        if(getList === false) {
            getList = true;
            messengerUsers();
        }
        list.classList.remove('direct-chat-contacts-open');
        manager.classList.toggle('direct-chat-contacts-open');
    });
    
    messengerChatButton.addEventListener("click", function(e) {
        e.preventDefault(),
        postMessage();
    });
}


container.addEventListener("touchstart", dragStart, !1),
container.addEventListener("touchend", dragEnd, !1),
container.addEventListener("touchmove", drag, !1),
container.addEventListener("mousedown", dragStart, !1),
container.addEventListener("mouseup", dragEnd, !1),
container.addEventListener("mousemove", drag, !1);

messageIco.addEventListener("click", function(){
    if(dragItem.getAttribute('data-visibility') === 'show') {
        B.hide('#boards-messenger');
        B.hide('#boards-messenger-snippet');
    } else {
        B.show('#boards-messenger');
        B.show('#boards-messenger-snippet');
    }
});

bReady().then(() => {
    initMessenger();
});

