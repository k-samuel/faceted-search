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
    for (var field in result.data) {
        s += '<div class="filterLabel">' + field + ' :</div><div class="filterGrid">';
        for (var value in result.data[field]) {
            let count = result.data[field][value];
            s += '<label class="filterValue"><input type="checkbox" autocomplete="off" onchange="javascript:filterChange()" name="' + field + '" value="' + value + '" /> ' + value + ' (' + count + ')</label>';
        }
        s += '</div>'
    }
    document.getElementById(containerId).innerHTML = s;
}

function updateFilters(containerId, result) {
    let checked = getChecked(containerId);
    showFilters(containerId, result);
    for (let field in checked) {
        checked[field].forEach(function (item) {
            document.getElementById('filters').querySelectorAll('input[type="checkbox"][name="' + field + '"][value="' + item + '"]').forEach(function (el) {
                el.checked = true;
                el.focus();
            });
        })
    }
}

function getChecked(containerId) {
    let el = document.getElementById(containerId);
    if (!el) {
        return [];
    }
    let checkboxes = el.querySelectorAll('input[type="checkbox"]');
    let values = {};
    for (let index = 0; index < checkboxes.length; index++) {
        if (checkboxes[index].checked) {
            if (values[checkboxes[index].name]) {
                values[checkboxes[index].name].push(checkboxes[index].value);
            } else {
                values[checkboxes[index].name] = [checkboxes[index].value];
            }
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