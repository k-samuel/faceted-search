<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" type="text/css" href="static/style.css"/>
    <title>Faceted Search example</title>
    <script src="static/js.js"></script>
</head>
<body>
<i>Not structured Dataset from https://data.world/datafiniti/mens-shoe-prices</i>
<div class="catBox">
    Category:
    <ul class="menu">
        <li><a href="./oils.php">Oils Catalog</a></li>
        <li class="selected">Clothing Catalog</li>
    </ul>
</div>
<div class="clear"></div>
<hr>
<div id="content" class="content">
    <div>
        <div class="boxLabel">Filters</div>
        <div class="treePanel">
            <div id="filters" class="shoe"></div>
        </div>
    </div>
    <div style="width: 100%">
        <div id="contentLoader" style="width:100%; display: none" align="center"><img src="static/ajax-loader.gif">
        </div>
        <div id="results"></div>
    </div>
</div>

<script language="JavaScript">
    /**
     * Show product cards
     * @param containerId
     * @param result
     */
    function showResults(containerId, result) {
        let s = '';
        s += '<div style="clear: both; text-align: center;"> ' + result.data.length + ' items from <b>' + result.count + '</b> results.</div>';
        result.data.forEach(function (value) {
            s += '<div class="card shoe">';
            s += '<div class="title">' + value.brand + '</div>';
            if (value.image.length) {
                s += '<div class="img"><img src="' + value.image + '" onerror="if (this.src != \'static/shoe_icon.png\') this.src = \'static/shoe_icon.png\';" align="left" width="150" hspace="2" height="150"/></div>'
            } else {
                s += '<div class="img"><img src="static/shoe_icon.png" align="left" width="150" hspace="2" height="150"/></div>'
            }
            s += '<div class="title">' + value.name + '</div>';
            s += '<div class="properties">';
            s += '<span>Price: $' + value.price + '<br>';
            s += '</div>';
            s += '</div>'
        });
        document.getElementById(containerId).innerHTML = s;
    }

    function filterChange() {
        let filters = JSON.stringify(getChecked('content'));
        showLoader('contentLoader');
        ajax.post('./query.php?cat=shoe', {'filters': filters}, function (data) {
            let result = JSON.parse(data);
            updateFilters('filters', result.filters);
            showResults('results', result.results);
            hideLoader('contentLoader');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        filterChange();
    });
</script>
</body>
</html>