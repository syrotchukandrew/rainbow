
function showResult(str) {
    if (str.length == 0) {
        document.getElementById("livesearch").innerHTML = "";
        document.getElementById("livesearch").style.border = "0px";
        return;
    }
    if (window.XMLHttpRequest) {
        // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {  // code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var parsed = JSON.parse(xmlhttp.responseText);
            var textDiv = '';
            for (var item in parsed) {
                if (!parsed.hasOwnProperty(item)) continue;
                var title = parsed[item];
                var url = Routing.generate('show_estate', {slug: item});
                textDiv = textDiv + "<a href=" + url + ">" + title + "</a><br>";
            }
            document.getElementById("livesearch").innerHTML = textDiv;
            document.getElementById("livesearch").style.border = "1px solid #A5ACB2";
        }
    };
    var route = Routing.generate('livesearch');
    xmlhttp.open("GET", route + "?slug="+str, true);
    xmlhttp.send();
}
