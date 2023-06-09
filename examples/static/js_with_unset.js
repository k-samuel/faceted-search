var ajax = {};
ajax.x = function () {
    if (typeof XMLHttpRequest !== 'undefined') {
        return new XMLHttpRequest();
    }
    var versions = [
        "MSXML2.XmlHttp.6.0",
        "MSXML2.XmlHttp.5.0",
        "MSXML2.XmlHttp.4.0",
        "MSXML2.XmlHttp.3.0",
        "MSXML2.XmlHttp.2.0",
        "Microsoft.XmlHttp"
    ];

    var xhr;
    for (var i = 0; i < versions.length; i++) {
        try {
            xhr = new ActiveXObject(versions[i]);
            break;
        } catch (e) {
        }
    }
    return xhr;
};

ajax.send = function (url, callback, method, data, async) {
    if (async === undefined) {
        async = true;
    }
    var x = ajax.x();
    x.open(method, url, async);
    x.onreadystatechange = function () {
        if (x.readyState == 4) {
            callback(x.responseText)
        }
    };
    if (method == 'POST') {
        x.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    }
    x.send(data)
};

ajax.get = function (url, data, callback, async) {
    var query = [];
    for (var key in data) {
        query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
    }
    ajax.send(url + (query.length ? '?' + query.join('&') : ''), callback, 'GET', null, async)
};

ajax.post = function (url, data, callback, async) {
    var query = [];
    for (var key in data) {
        query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
    }
    ajax.send(url, callback, 'POST', query.join('&'), async)
};


/**
 * Show product filters
 */
function showFilters(containerId, result) {
    var s = '';

    let stepSize = parseInt(result.price_step);

    for (var field in result.data) {
        s += '<div class="filterLabel">' + field + ' :</div><div class="filterGrid">';
        s += '<div style="width:100%">' + 
                 '<div style="float:left;font-size:10px;">include</div>' +
                 '<div style="float:right;font-size:10px;padding-right:15px;">exclude</div>' +
             '</div>' +
             '<div class="clear"></div>' ;

        for (var value in result.data[field]) {
            let count = result.data[field][value];

            let valueLabel = value;

            // customization for price ranges
            if(field === 'price'){
                valueLabel =  value + ' - ' + (parseInt(value) + stepSize - 1);
            }

            s += '<div style="width:100%">' + 
                    '<div style="float:left">' + 
                        '<label class="filterValue">' + 
                            '<input type="checkbox" autocomplete="off" onchange="javascript:filterChange(this)" data-type="include" name="' + field + '" value="' + value + '" /> ' + 
                               '<span class="label">' + valueLabel + ' (' + count + ') </span>' +
                        '</label>' +
                    '</div>' +
                    '<div style="float:right;padding-right:20px;">' +
                        '<input type="checkbox" autocomplete="off" onchange="javascript:filterChange(this)" data-type="exclude" name="' + field + '" value="' + value + '">' +
                    '</div>' +
                '</div><div class="clear"></div>' ;
        }
        s += '</div>'
    }
    document.getElementById(containerId).innerHTML = s;
}

function updateFilters(containerId, result, initialFilters) {
    let checked = getChecked(containerId);
    let stepSize = parseInt(result.price_step);
    cmp = document.getElementById(containerId);
    for (let field in initialFilters) {

        for (let value in initialFilters[field]){


            let valueLabel = value;
            // customization for price ranges
            if(field === 'price'){
                valueLabel =  value + ' - ' + (parseInt(value) + stepSize - 1);
            }

            if(result.data[field] && result.data[field][value]){
                cmp.parentNode.parentNode.querySelectorAll('input[type="checkbox"][name="' + field + '"][value="' + value + '"][data-type="include"]').forEach(function (el) {
                   
                   if(checked['exclude'] && checked['exclude'][field] && checked['exclude'][field][value]){
                    el.disabled = true;
                    el.parentNode.classList.add("crossed");

                   }else{
                    el.disabled = false;
                    el.parentNode.classList.remove("crossed");
                   }

                    el.parentNode.querySelectorAll('span[class="label"]').forEach(function(el){
                        el.innerHTML = valueLabel + ' (' + result.data[field][value] + ')';
                    });
                });

            }else{
                cmp.parentNode.parentNode.querySelectorAll('input[type="checkbox"][name="' + field + '"][value="' + value + '"][data-type="include"]').forEach(function (el) {
                    el.checked = false;
                    el.disabled = true;
                    el.parentNode.querySelectorAll('span[class="label"]').forEach(function(el){
                        el.innerHTML = valueLabel + ' (0)';
                    });
                });
            }

        }
    }
}

function getChecked(containerId) {
    let el = document.getElementById(containerId);
    if (!el) {
        return [];
    }
    let checkboxes = el.querySelectorAll('input[type="checkbox"]');
    let values = {include:{},exclude:{}};
    for (let index = 0; index < checkboxes.length; index++) {
        if (checkboxes[index].checked) {

            let dataType = checkboxes[index].dataset.type;
            let val = checkboxes[index].value

            if (!values[dataType][checkboxes[index].name]) {
                values[dataType][checkboxes[index].name] = {}
            }
            values[dataType][checkboxes[index].name][val] = true;
        }
    }
    return values;
}

function showLoader(containerId) {
    document.getElementById(containerId).style.display = 'block';
}

function hideLoader(containerId) {
    document.getElementById(containerId).style.display = 'none';
}