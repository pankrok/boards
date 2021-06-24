let chatDef;

function getNewMessages() { 
    let a = document.getElementById("csrf_name").value,
        e =  document.getElementById("csrf_value").value,
        sb = document.querySelector(chatDef.shoutsContainer);
    let chatboxLi = document.querySelectorAll(chatDef.chatScrollEl);
    let last_element = chatboxLi[chatboxLi.length - 1]; console.log(chatboxLi);
    B.ajax({
        method: "POST",
        url: ajaxUrl,
        data: { module: 'chatbox', route: 'check', lastShout: last_element.value, csrf_name: a, csrf_value: e },
        dataType: "json",
        success: function (t) {
            document.getElementById("csrf_name").value = t.csrf.csrf_name, 
            document.getElementById("csrf_value").value = t.csrf.csrf_value,
            "no new shouts" != t.chatbox[0] && (t.chatbox.forEach( function(e) {
               B.appendHtml(sb, e);
            }), scrollDown()),
            setTimeout(function () {
                getNewMessages();
            }, 30000);
        },
    });
}

function chatboxPrinter(t) {
    B.prependHtml(chatDef.shoutsContainer, t);
}
function chatboxNewsPrinter(t) {
    B.appendHtml(chatDef.shoutsContainer, t);
}

function setCsrf(name, val){
    document.getElementById(name).value = val;
}

function scrollDown() {
        setTimeout(function () {
            let t = 0;
            document.querySelectorAll(chatDef.chatScrollEl).forEach(function (a, e) {
                t += parseInt(B.getHeight(a));
            }),
                (t += ""),
                document.querySelector(chatDef.divChatbox).scroll({
                  top: t + 20,
                  behavior: 'smooth'
                });
        }, 500);
    }

class Chatbox {

    chatScrollEl;
    divChatbox;
    shoutsContainer;
    shoitItemList;
    shoutContent;
    chatboxList;
    chatOffset;
    
    constructor(o) {
        this.chatScrollEl = o.chatScrollEl;
        this.divChatbox = o.divChatbox;
        this.shoutsContainer = o.shoutsContainer;
        this.shoitItemList = o.shoitItemList;
        this.shoutContent = o.shoutContent;
        this.chatboxList = o.chatboxList;   
        this.chatOffset = 0;
    }
    
    loadMoreShouts() {     
        let a = document.getElementById("csrf_name").value,
            e =  document.getElementById("csrf_value").value;
        B.ajax({
            method: "POST",
            url: ajaxUrl,
            data: { module: 'chatbox', route: 'load', offset: this.chatOffset, csrf_name: a, csrf_value: e },
            dataType: "json",
            success: function (t) {
                setCsrf("csrf_name", t.csrf.csrf_name), 
                setCsrf("csrf_value", t.csrf.csrf_value), 
                "no more shouts" != t.chatbox && (t.chatbox.reverse(), t.chatbox.forEach( function(el) {
                    B.prependHtml(document.querySelector(chatDef.shoutsContainer), el);
                }));
            },
        });
        ++this.chatOffset;
    }
    postShout() {
        let n = document.getElementById("csrf_name").value,
            v =  document.getElementById("csrf_value").value,
            sc = document.getElementById(this.shoutContent),
            sb = document.querySelector(this.shoutsContainer);

        B.ajax({
            method: "POST",
            url: ajaxUrl,
            data: { module: 'chatbox', route: 'post', shout: sc.value, csrf_name: n, csrf_value: v },
            dataType: "json",
            success: function (t) {
                setCsrf("csrf_name", t.csrf.csrf_name);
                setCsrf("csrf_value", t.csrf.csrf_value);
                B.appendHtml(sb, t.shout);
                sc.setAttribute("value", ""),
                sc.value = ''
            }
        }).then(() => {
            scrollDown();
        });;
    }
    init() {
        chatDef = this;
        getNewMessages();
        scrollDown();
    }
    
    editMessage(t) {
        let a = $("#csrf_name").val(),
            e = $("#csrf_value").val();
        B.ajax({
            method: "POST",
            url: ajaxUrl,
            data: { 
                module: 'chatbox', 
                route: 'edit', 
                shout_content: document.getElementById("shout_edit_content").value, 
                shout_id: document.getElementById("shout_edit_id").value, 
                csrf_name: a, csrf_value: e 
            },
            dataType: "json",
            success: function (t) {
                setCsrf("csrf_name", t.csrf.csrf_name), 
                setCsrf("csrf_value", t.csrf.csrf_value)
            },
        });
    }

}