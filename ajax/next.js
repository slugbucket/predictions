// http://localhost/predictions/index.php?do=rpc&where=home&team=63&lge=4&opp=93
function createRequestObject() {
    var ro;
    var browser = navigator.appName;
    if(browser == "Microsoft Internet Explorer"){
        ro = new ActiveXObject("Microsoft.XMLHTTP");
    }else{
        ro = new XMLHttpRequest();
    }
    return ro;
}

var http = createRequestObject();

function sndReq(lid) {
    var usel = document.forms['userlist'].predict_users;
    var uid = usel.options[usel.selectedIndex].value;
    http.open('get', 'index.php?do=rpc&puid='+uid);
    http.onreadystatechange = handleResponse;
    http.send(null);
}


var fixtid;
function sndFormReq(where,tid,lid,oppid) {
    http.open('get', 'index.php?do=rpc&where='+where+"&team="+tid+"&lge="+lid+"&opp="+oppid);
    http.onreadystatechange = showFormGuide;
    http.send(null);
}

function handleResponse() {
    if(http.readyState == 4){
        var response = http.responseText;
        var update = new Array();

        if(response.indexOf('|' != -1)) {
            update = response.split('|');
            document.getElementById(update[0]).innerHTML = update[1];
        }
    }
}

function showFormGuide() {

    if(http.readyState == 4){
        var response = http.responseText;
        var update = new Array();

        if(response.indexOf('|' != -1)) {
            update = response.split('|');
            document.getElementById(update[0]).innerHTML = update[1];
            setIdProperty (update[0], "display", "inline");
            document.getElementById(update[2]).innerHTML = update[3];
            setIdProperty (update[2], "display", "inline");
        }
    }
}
