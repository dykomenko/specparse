<?php
set_time_limit(999);
require('phpQuery-onefile.php'); 
$link = mysqli_connect("localhost", "mysql", "mysql", "host1513206_parser");
if (!$link) print("Ошибка: Невозможно подключиться к MySQL " . mysqli_connect_error());

function g($url){
	$url = trim($url);
	if (!$html = file_get_contents($url)) file_put_contents('error.txt', $url."\n", FILE_APPEND | LOCK_EX);
	$html = mb_convert_encoding ($html, 'HTML-ENTITIES', "UTF-8");
	$dom = phpQuery::newDocument( $html );
	$out = array();

	$breadc = "";
	foreach($dom->find(".breadcrumbs li") as $key => $value){
		 $pq = pq($value);
		 $breadc .= $pq->find("span")->text() . "/";
	}
	
	$images = array();
	foreach($dom->find(".product-zoom__slider img") as $key => $value){
		 $pq = pq($value);
		 array_push ($images, $pq->attr('data-imagefull'));
	}

	$title = 		$dom->find("h1.product__name")->text();
	$price = 		wipe($dom->find(".product__price-price:not(.product__price_black)")->text());
	$price_opt = 	wipe($dom->find(".product__price_black")->text());
	$article = 		$dom->find("#copytext")->text();
	$quantity = 	$dom->find(".SKU_MAX_QTY")->attr('value');
	$description = 	$dom->find(".product-info__content.product-description")->html();
	$images =		json_encode($images);

	$out = array(
		'title' => 			$title,
		'breadcrumbs' => 	$breadc,
		'price' => 			$price,
		'price_opt' => 		$price_opt,
		'article' => 		$article,
		'quantity' => 		$quantity,
		'images' => 		$images,
		'description' => 	$description,
	);
	
	// return $out['title'];
	return $out;
}
function wipe($text){
	return str_replace(array(" ", "р", "."), "", $text);
}
function getsql($data){
	$sql = 'INSERT INTO results SET 
	title =  "' . 		mysqli_real_escape_string($GLOBALS['link'], $data['title']) . '",
	breadcrumbs = "' . 	mysqli_real_escape_string($GLOBALS['link'], $data['breadcrumbs']) . '",
	price = "' . 		mysqli_real_escape_string($GLOBALS['link'], $data['price']) . '",
	price_opt = "' . 	mysqli_real_escape_string($GLOBALS['link'], $data['price_opt']) . '",
	article = "' . 		mysqli_real_escape_string($GLOBALS['link'], $data['article']) . '",
	quantity = "' . 	mysqli_real_escape_string($GLOBALS['link'], $data['quantity']) . '",
	images = "' . 		mysqli_real_escape_string($GLOBALS['link'], $data['images']) . '",
	description = "' . 	mysqli_real_escape_string($GLOBALS['link'], $data['description']) . '"' ;
	return $sql;
}


$f = file('urls.txt');

$br="";
foreach ($f as $key => $value) {
	if ( mysqli_query($link, getsql( g($value) )) ) echo ".";
	// var_dump(getsql( g($value) ));
	usleep(rand(100000, 340000));
	echo " ";
	if (++$br == 10){
	 	echo ("<br>"); $br=0;
	}
	set_time_limit(900);
}


// fclose($f); 