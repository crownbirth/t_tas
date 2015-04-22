// JavaScript Document
$( function(){
	$('#new a').click(function(e) {
		$('#newCategory').show();		
        e.preventDefault();
    })
});

function colFilter( elem ){
	doFilter("col", elem);
}

function catFilter( elem, dpd ){
	if( dpd == "dept")
		doFilter("catd", elem);
	else if( dpd == "col")
		doFilter("catc", elem);
	else
		doFilter("cat", elem);
}

function deptFilter( elem ){
	doFilter("dept", elem);
}

function doFilter( action, elem ){
	var selectedOption = elem.options[elem.selectedIndex];
	if( action == "dept")
		window.location = "?filter="+action+"&did="+selectedOption.value;
	else if( action == "col")
		window.location = "?filter="+action+"&cid="+selectedOption.value;	
	/*else if( action == "catd"){
		var params = getQueryStringArgs();
		if( params['did'] )
			window.location = "?filter="+action+"&catid="+selectedOption.value+"&did="+params['did'];
	}
	else if( action == "catc"){
		var params = getQueryStringArgs();
		window.location = "?filter="+action+"&catid="+selectedOption.value+"&cid="+params['cid'];
	}*/
	else{
		var params = getQueryStringArgs();
		if( params['did'] )
			window.location = "?filter="+action+"&catid="+selectedOption.value+"&did="+params['did'];
		else if( params['cid'] )
		window.location = "?filter="+action+"&catid="+selectedOption.value+"&cid="+params['cid'];
		else
			window.location = "?filter="+action+"&catid="+selectedOption.value;
	}
}

function getQueryStringArgs(){
	//get query string without the initial ?
	var qs = location.search.length > 0 ? location.search.substring(1) : "",
	//object to hold data
	args = {},
	//get individual items
	items = qs.length ? qs.split("&") : [],
	item = null,
	name = null,
	value = null,
	//used in for loop
	i = 0,
	len = items.length;
	//assign each item onto the args object
	for (i=0; i < len; i++){
		item = items[i].split("=");		
		name = decodeURIComponent(item[0]);
		value = decodeURIComponent(item[1]);
		if (name.length) {
		args[name] = value;
		}
	}
	return args;
}