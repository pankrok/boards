function getNewMessages(list) {
    let a = document.getElementById("csrf_name").value,
        e =  document.getElementById("csrf_value").value;
    let chatboxLi = document.querySelector(list)    
    let last_element = chatboxLi[chatboxLi.length - 1];
    B.ajax({
        method: "POST",
        url: ajaxUrl,
        data: { module: 'chatbox', route: 'check', lastShout: last_element.value, csrf_name: t, csrf_value: a },
        dataType: "json",
        success: function (t) {
            document.getElementById("csrf_name").value = t.csrf.csrf_name, 
            document.getElementById("csrf_value").value = t.csrf.csrf_value,
            "no new shouts" != t.chatbox[0] && (t.chatbox.forEach(chatboxNewsPrinter), scrollDown()),
            setTimeout(function () {
                getNewMessages(list);
            }, 30000);
        },
    });
}

   function chatboxPrinter(t, shoutsContainer , shoitItemList) {
        B.prependHtml(shoutsContainer, t), B.show(shoitItemList);
    }
   function chatboxNewsPrinter(t, shoutsContainer , shoitItemList) {
        B.appendHtml(shoutsContainer, t), B.show(shoitItemList);
    }

class Chatbox {

    chatScrollEl;
    divChatbox;
    shoutsContainer;
    shoitItemList;
    shoutContent;
    chatboxList;

    
    constructor(o) {
        this.chatScrollEl = o.chatScrollEl;
        this.divChatbox = o.divChatbox;
        this.shoutsContainer = o.shoutsContainer;
        this.shoitItemList = o.shoitItemList;
        this.shoutContent = o.shoutContent;
        this.chatboxList = o.chatboxList;

    }
    
    setCsrf(name, val){
        document.getElementById(name).value = val;
    }

    scrollDown() {
        setTimeout(function () {
            let t = 0;
            document.querySelector(this.chatScrollEl).each(function (a, e) {
                t += parseInt(document.querySelector(this).height());
            }),
                (t += ""),
                document.querySelector(this.divChatbox).animate({ scrollTop: t });
        }, 500);
    }

    loadMoreShouts(t) {
        let a = document.getElementById("csrf_name").value,
            e =  document.getElementById("csrf_value").value;
        B.ajax({
            method: "POST",
            url: ajaxUrl,
            data: { module: 'chatbox', route: 'load', offset: t, csrf_name: a, csrf_value: e },
            dataType: "json",
            success: function (t) {
                setCsrf("csrf_name", t.csrf.csrf_name), 
                setCsrf("csrf_value", t.csrf.csrf_value), 
                "no more shouts" != t.chatbox && (t.chatbox.reverse(), t.chatbox.forEach(this.chatboxPrinter));
            },
        });
    }
    postShout() {
        let a = document.getElementById("csrf_name").value,
            e =  document.getElementById("csrf_value").value;
        B.ajax({
            method: "POST",
            url: ajaxUrl,
            data: { module: 'chatbox', route: 'post', shout: document.getElementById("shout-content").value, csrf_name: t, csrf_value: a },
            dataType: "json",
            success: function (t) {
                setCsrf("csrf_name", t.csrf.csrf_name), 
                setCsrf("csrf_value", t.csrf.csrf_value), 
                 chatboxNewsPrinter(t.shout, this.shoutsContainer, this.shoitItemList), 
                document.getElementById(this.shoutContent).setAttribute("value", ""), 
                document.getElementById(this.shoutContent).value = '', 
                scrollDown();
            },
        });
    }
    getNewMessages() {
        setTimeout(function () {
                getNewMessages(this.chatboxList);
            }, 300);
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
                setCsrf("csrf_value", t.csrf.csrf_value), 
                // $("#shout-content-" + t.id).html(t.content), 
                // $("#chatMessageEdit").modal("hide");
            },
        });
    }

}