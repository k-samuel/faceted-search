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
function showFilters(containerId, result, titles) {
    var s = '';

    let stepSize = parseInt(result.price_step);
    let count = 0;
    let menuCls = 'hideButton vis';
    let dataState ='vis';
    let blockStyle = 'display:block';

    for (var field in result.data) {
        let fieldLabel = field;
       
        
        if(titles && titles[field] !=undefined){
            fieldLabel = titles[field];
        }

        if (count  > 0 ){
            menuCls = 'hideButton hid';
            dataState = 'hid';
            blockStyle = 'display:none';
        }

        s +='<div class="filterBox">' + 
                '<div class="filterHeader" data-field="'+field+'" data-state="' + dataState + '" onClick="javascript:menuClick(this);">' + 
                    '<div class="filterLabel">' + fieldLabel + '</div>' + 
                    '<div class="' + menuCls + '"></div>' +
                '</div>' +
                '<div class="clear"></div>' +
                '<div class="filterGrid" data-field="' + field + '" style="' + blockStyle + '">' +
                    '<div style="width:100%">' + 
                        '<div style="float:left;font-size:10px;">include</div>' +
                        '<div style="float:right;font-size:10px;padding-right:15px;">exclude</div>' +
                    '</div>' +
                    '<div class="clear"></div>' ;

        for (var value in result.data[field]) {
            let count = result.data[field][value];

            let valueLabel = value;

            // customization for price ranges
            if(field === 'price_range'){
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
        s += '</div></div><div class="clear"></div>';
        count++;
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
            if(field === 'price_range'){
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

function showSort(containerId, result, titles){
    let selectBox = document.getElementById(containerId);
    if(!selectBox){
        return;
    } 
    while (selectBox.options.length > 0) {
        selectBox.remove(0);
    }

    selectBox.add(new Option('---', '', true));
    selectBox.add(new Option('Price', 'price', true));

    for (let field in result) {
        if(field === 'price_range'){
            continue;
        }
        selectBox.add(new Option(titles[field],field));
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

// Filter menu show/hide
function menuClick(el){
    let state = el.dataset.state;
    let field = el.dataset.field;

    if(state === 'vis'){
        el.dataset.state = 'hid';
        el.querySelectorAll('.hideButton').forEach(function(el){
            el.classList.add("hid");
            el.classList.remove("vis");
        });
        
        document.querySelectorAll('.filterGrid[data-field="' + field + '"]').forEach(function(el){
            el.style.display = 'none';
        });
       
    }else{
        el.dataset.state = 'vis';
        el.querySelectorAll('.hideButton').forEach(function(el){
            el.classList.add("vis");
            el.classList.remove("hid");
        });

        document.querySelectorAll('.filterGrid[data-field="' + field + '"]').forEach(function(el){
            el.style.display = 'block';
        });
    }
}