<?php
	include "functions.php";
	if(isset($_POST["rout"])) {
		$arr = array();
		$arr["marsh"] = getForPrint($_POST["rout"]);
		//$arr["point"] = getRoutForPrint($_POST["rout"]);
		$arr["point"] = getTimeTransportForClient($_POST["rout"]);
		echo json_encode($arr);
	}
	
	if(isset($_POST["transportRout"]) && isset($_POST["transportType"])) {
		echo json_encode(getTransportTimeClient($_POST["transportRout"], $_POST["transportType"]));
	}
	
	if(isset($_POST["transportRout1"]) && isset($_POST["transportType1"]) && isset($_POST["id"])) {
		$arr = array();
		$arr["marsh"] = getTransportTimeClient($_POST["transportRout1"], $_POST["transportType1"]);
		$arr["point"] = getTimeTransportForClient($_POST["id"]);
		//echo json_encode(getTransportTimeClient($_POST["transportRout"], $_POST["transportType"]));
		echo json_encode($arr);
	}
?>