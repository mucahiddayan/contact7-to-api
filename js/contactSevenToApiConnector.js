var url = 'http://plaqee.42one.com';
var create = {
    m:'MyCard',
    v:'json',
    act:'insert',
    Kunde:'M Sener',
    Telefon:'+491776009876',
    Amount:'10.00',
    sendSmsInfo:1
};

var show = {
    m:'MyCard',
    v:'json',
    act:'show',
    Telefon:'+491776009876',
};
var req = {
    method:'POST',
    mode:'no-cors',
    headers:{
        'Content-Type':'application/json',
        'Authorization':'Basic '+btoa('fluxapi:i6h7sshr4q6f5vfve2v6hd12v7')
    },
    body:JSON.stringify(show)
};
fetch(url,req)
.then(res=>{console.log(res);return JSON.parse(res)})
.then(res=>console.log(res));