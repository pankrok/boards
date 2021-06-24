async function bReady() {
    return new Promise(resolve => {
        if(typeof(B) === 'object')    {
            resolve('B is ready');
        }
        if(typeof(B) !== 'object') {
            setTimeout(function(){
                resolve( bReady() );
            });
        }
       
    });   
}