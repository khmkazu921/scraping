<?php
$pdo = new PDO("mysql:dbname=db-name;host=host","user","pass");
$stmt = $pdo->prepare("SELECT * FROM olddata");
$stmt->execute();
$all = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Result list</title>
	<!--	<link rel="stylesheet" type="text/css" href="style.css"> -->
	<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	<script>
	 let numRow = 20;
	 var all = <?=json_encode($all)?>;
	 all = all.sort((v0,v1) => (v0.updated < v1.updated)?1:-1);
	 var json = all;
	 var page = 1;
	 var echa = filterJson("ECHA",    all);
	 var ec   = filterJson("EC"  ,    all);
	 var epa  = filterJson("EPA" ,    all);
	 var mep  = filterJson("MEP-SCC", all);
	 var ncis = filterJson("NCIS",    all);
	 var osha = filterJson("労働部職業安全衛生署", all);
	 var chem = filterJson("Chem-reg",all);
	 var nite = filterJson("NITE",    all);
	 var meti = filterJson("化審法",  all);
	 var meti2 = filterJson("化管法", all);
	 var mhlw = filterJson("安衛法",  all);
	 var isall = true;
	 $(function(){
	     fleshPager(all);
	     $('#nav-all-tab'  ).click( function(){ isall = true;  fleshPager(all  ); } );
	     $('#nav-echa-tab' ).click( function(){ isall = false; fleshPager(echa ); } );
	     $('#nav-ec-tab'   ).click( function(){ isall = false; fleshPager(ec   ); } );
	     $('#nav-epa-tab'  ).click( function(){ isall = false; fleshPager(epa  ); } );
	     $('#nav-mep-tab'  ).click( function(){ isall = false; fleshPager(mep  ); } );
	     $('#nav-ncis-tab' ).click( function(){ isall = false; fleshPager(ncis ); } );
	     $('#nav-osha-tab' ).click( function(){ isall = false; fleshPager(osha ); } );
	     $('#nav-chem-tab' ).click( function(){ isall = false; fleshPager(chem ); } );
	     $('#nav-nite-tab' ).click( function(){ isall = false; fleshPager(nite ); } );
	     $('#nav-meti-tab' ).click( function(){ isall = false; fleshPager(meti ); } );
	     $('#nav-meti2-tab').click( function(){ isall = false; fleshPager(meti2); } );
	     $('#nav-mhlw-tab' ).click( function(){ isall = false; fleshPager(mhlw ); } );
	 });
	 function filterJson(id, data) {
	     return data.filter(d => d.site === id);
	 };
	 function fleshPager(data) {
	     page = 1;
	     json = data;
	     if(json!=null) {displayTable();};
	     generatePager(json.length);
	 };
	 function displayTable(){
	     var t ="<tr><th scope='col' style='width:7em;'>時間</th>";
	     if(isall) t += "<th scope='col' style='width:6em;'>サイト</th>";
	     t += "<th scope='col'>タイトル</th></tr>";
	     $('#show-table-head').empty();
	     $('#show-table-head').append(t);
	     $('#show-table').empty();
	     $('#show-table').append(build());
	 };
	 function build() {
	     var i;
	     var o = numRow*(page-1); /*offset*/
	     var t = "";
	     for(i = o; i < numRow + o; i++) {
		 if( json==null || json[i]==null ) break;
		 t += "<tr><td>" + json[i]["updated"] + "</td>";
		 if(isall) t += "<td>" + json[i]["site"] + "</td>";
		 t += "<td><a href='" + json[i]["link"] + "'>"
		    + json[i]["title"] + "</a></td></tr>"
	     }
	     return t;
	 };
	 function generatePager(l){
	     var i;
	     var t = "<li class='pre'><a href='#'";
	     if(page != 1) t += " onclick='flipPage(" + (page-1) + ");'";
	     t += "><span><</span></a></li>";
	     var x = parseInt((l-1)/numRow)+1;
	     if(x>12){
		 t+=pagerBlock(1);
		 if(page > 5)
		     t+="<li class='skip'><a><span>...</span></a></li>";
		 if(page <= 5)
		     for(i=2;i<=7;i++)t+=pagerBlock(i);
		 if(page > 5 && page < x-4)
		     for(i=page-2;i<=page+2;i++)t+=pagerBlock(i);
		 if(page >= x-4)
		     for(i=x-6;i<=x-1;i++)t+=pagerBlock(i);
		 if(page < x-4)
		     t+="<li class='skip'><a><span>...</span></a></li>";
		 t+=pagerBlock(x);
	     } else {
		 for(i=1;i<=x;i++) t+=pagerBlock(i);
	     }
	     t+="<li class='next'><a href='#'";
	     if(page!=x) t+=" onclick='flipPage("+(page+1)+");'";
	     t+="><span>></span></a></li>";
	     $('#pager').empty();
	     $('#pager').append(t);
	 };
	 function pagerBlock(i) {
	     var t = "<li><a href='#' onclick='flipPage("+i+");'";
	     if(page==i) t+=" class='active'";
	     t+="><span>"+i+"</span></a></li>";
	     return t;
	 };
	 function flipPage(pager) {
	     page = pager;
	     generatePager(json.length);
	     if(json!=null) { displayTable(json) };
	 };
	</script>
    </head>
    <body>
	<h1>Result list</h1>

	<div class="container">
	    <div class="d-flex align-items-end flex-column">
		<a class="btn btn-primary p-1" id="update" href="./script/update.php" role="button">
		    <svg version="1.1" x="0px" y="0px" viewBox="-11.0 -1 37.0 17.0" style="width:50px; opacity: 1.0;" xml:space="preserve">
			<g><path style="fill:#ffffff;"
				 d="M12.083,1.887c-0.795-0.794-1.73-1.359-2.727-1.697v2.135c0.48,0.239,0.935,0.55,1.334,0.95
					c1.993,1.994,1.993,5.236,0,7.229c-1.993,1.99-5.233,1.99-7.229,0c-1.991-1.995-1.991-5.235,0-7.229
					C3.466,3.269,3.482,3.259,3.489,3.25h0.002l1.181,1.179L4.665,0.685L0.923,0.68l1.176,1.176C2.092,1.868,2.081,1.88,2.072,1.887
					c-2.763,2.762-2.763,7.243,0,10.005c2.767,2.765,7.245,2.765,10.011,0C14.844,9.13,14.847,4.649,12.083,1.887z"/></g>
		    </svg>
		</a>
	    </div>
	    <nav>
		<div class="nav nav-tabs" id="nav-tab" role="tablist">
		    <a class="nav-item nav-link active" id="nav-all-tab" data-toggle="tab" href="#" >全て</a>
		    <a class="nav-item nav-link" id="nav-echa-tab"       data-toggle="tab" href="#" >ECHA</a>
		    <a class="nav-item nav-link" id="nav-ec-tab"         data-toggle="tab" href="#" >EC</a>
		    <a class="nav-item nav-link" id="nav-epa-tab"        data-toggle="tab" href="#" >EPA</a>
		    <a class="nav-item nav-link" id="nav-mep-tab"        data-toggle="tab" href="#" >MEP-SCC</a>
		    <a class="nav-item nav-link" id="nav-ncis-tab"       data-toggle="tab" href="#" >NCIS</a>
		    <a class="nav-item nav-link" id="nav-osha-tab"       data-toggle="tab" href="#" >労働部職業安全衛生署</a>
		    <a class="nav-item nav-link" id="nav-chem-tab"       data-toggle="tab" href="#" >Chem-reg</a>
		    <a class="nav-item nav-link" id="nav-nite-tab"       data-toggle="tab" href="#" >NITE</a>
		    <a class="nav-item nav-link" id="nav-meti-tab"       data-toggle="tab" href="#" >化審法</a>
		    <a class="nav-item nav-link" id="nav-meti2-tab"      data-toggle="tab" href="#" >化管法</a>
		    <a class="nav-item nav-link" id="nav-mhlw-tab"       data-toggle="tab" href="#" >安衛法</a>
		</div>
	    </nav>
	    <div class="pager d-flex align-self-center justify-content-center">
		<ul class="pagination"><span id="pager"></span></ul>
	    </div>
	    <div>
		<table class="table table-striped table-sm">
		    <thead id="show-table-head">
		    </thead>
		    <tbody id="show-table">
		    </tbody>
		</table>
	    </div>
	</div>
    </body>
    <style>
	*{
	    font-family: "YuGothic","Yu Gothic","Meiryo","sans-serif";
	}
	h1 {
	    text-align: center;
	    padding: 0.5em;
	    color: white;
	    background: #003399;
	}
	.pager ul.pagination {
	    margin: 0;
	    padding: 0;
	}
	.pager .pagination li {
	    display: inline;
	    margin: 0 2px;
	    padding: 0;
	    display: inline-block;
	    background: #d8b2ff;
	    width: 25px;
	    height: 25px;
	    text-align: center;
	    position: relative;
	}
	.pager .pagination li.skip {
	    display: inline;
	    margin: 0 2px;
	    padding: 0;
	    display: inline-block;
	    background: transparent;
	    width: 25px;
	    height: 25px;
	    text-align: center;
	    position: relative;
	}
	.pager .pagination li.skip span{
	    color: #000;
	    background: #ead6ff;
	}
	.pager .pagination li a{
	    vertical-align: middle;
	    position: absolute;
	    top: 0;
	    left: 0;
	    width: 100%;
	    height: 100%;
	    text-align: center;
	    display:table;
	    color: #000;
	    text-decoration: none;
	}
	.pager .pagination li a span{
	    display:table-cell;
	    vertical-align:middle;
	}
	.pager .pagination li a:hover,
	.pager .pagination li a.active{
	    color: #000;
	    background: #ead6ff;
	}
    </style>
</html>
