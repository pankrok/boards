let pid;
let action;
let inputs = document.querySelectorAll('[data-post]');
let postEditor = null;

for (i = 0; i < inputs.length; i++) {
    inputs[i].addEventListener('click', function() {
        
        action = this.getAttribute('data-post');
        if(action === 'like') {
            pid = this.closest('[data-postid]').getAttribute('data-postid');
            B.ajax({
                method: 'POST',
                url: ajaxUrl,
                type: 'json',
                data: {
                    module: 'plot',
                    route: action,
                    id: pid,
                    url: window.location.pathname
                }
            }).then(function(done) {
                let alert = JSON.parse(done);
                B.prependHtml('.container', alert.likeit);
                B.show('#like-modal');
                setTimeout(function(){
                    B.hide('#like-modal');  
                },3000);
                setTimeout(function(){
                  document.getElementById('like-modal').remove();
                }, 4000);
            });
            
        }
        
        if(action === 'editPost') {    
            pid = this.closest('[data-postid]').getAttribute('data-postid');
            if(postEditor === null) {
                postEditor = B.initJodit('#editPostJodit');
            }
            postEditor.then(() => {B.jodit.setEditorValue(document.getElementById('post-'+pid).innerHTML);})
            document.querySelector('#post_id').value = pid;
            B.show('#editPost'); 
        }
        
        if(action === 'editPlot') {
            pid = this.closest('[data-plot_id]').getAttribute('data-plot_id');
            B.show('#editPlot');
        }
    });
}
let stars = document.querySelectorAll('.fa-star');
let rating = document.getElementById('star-rating');
rating.addEventListener("mouseover", function(e){
    let rate = e.target.getAttribute('data-rate');
    if(rate !== null) {
        stars.forEach(function(v, k){
            if(k >= rate) {
                v.classList.remove('fas');
                v.classList.add('far');
            } else {
                v.classList.remove('far');
                v.classList.add('fas');
            }
        });
    }
});

rating.addEventListener("mouseout", function(e){
        stars.forEach(function(v, k){
                v.classList.remove('fas');
                v.classList.add('far');
        });
});
rating.addEventListener("click", function(e){
    pid = e.target.closest('[data-plot_id]').getAttribute('data-plot_id');
        B.ajax({
        method: 'POST',
        url: ajaxUrl,
        type: 'json',
        data: {
            module: 'plot',
            route: 'rate',
            plot_id: pid,
            rate: e.target.getAttribute('data-rate')
        }
    }).then(function(done) { console.log(done);
        let data = JSON.parse(done);
        B.prependHtml('.container', data.message);
        B.show('#rate');
        setTimeout(function(){
            B.hide('#rate');
            
        },3000);
        setTimeout(function(){
          document.getElementById('rate').remove();
        }, 4000);
    });
});

