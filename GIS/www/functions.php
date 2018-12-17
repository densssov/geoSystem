<?php 
	// Функция для получения информации о приближении транспорта
	function getTimeTransportForClient($id_rout) {
		$urm = 111.19426645;
		$link = mysqli_connect('127.0.0.1', 'root', '', 'transport_system');
		mysqli_set_charset($link , 'utf8');
		
		$arrStation = getRoutForPrint($id_rout);
		
		$transports = getTransportTime($id_rout, $link);
		if(count($transports) > 0) {
			//for($i = 0; $i < count($transports); $i++) {
			foreach($transports as $k => $v) {	
				if($v["ON_ROUTE"] == 1) {
					
					$urp = getUrp($v["LAT"]);
					
					$text_query = "(SELECT DISTINCT p.point_name, t.id_routs, p.latitude, t.station_status,
						p.longitude, t.orders, p.id_points, t.direction,
						SQRT(POW((".$v["LAT"]." - p.latitude) * ".$urm.", 2) + POW((".$v["LON"]." - p.longitude) * ".$urp.", 2)) as result
					FROM points p, transport_station t
					WHERE p.id_points = t.id_points 
					AND station_status > 0
					AND t.id_routs = ".$id_rout;
					
					$sql_query = $text_query;
					
					// Пошагово определяем точки
					if(($v["COURSE"] > 270) && ($v["COURSE"] < 360)) {
						//1
						$course = 1;
						// Главная четверть
						$sql_query.= " AND ((p.latitude >= ".($v["LAT"])." AND p.longitude >= ".($v["LON"])."))";
						$sql = $sql_query;	
						$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
						$sql .= " UNION ";		
						$sql .= $sql_query;
						$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";	
						$result = mysqli_query($link, $sql);	
						// Второстепенные четверти		
						if(mysqli_num_rows($result) == 0) {
							$sql_query = $text_query;
							$sql_query.= " AND (
											(p.latitude >= ".($v["LAT"])." AND p.longitude <= ".($v["LON"]).") OR
											(p.latitude <= ".($v["LAT"])." AND p.longitude >= ".($v["LON"]).")
										)";
							$sql = $sql_query;	
							$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
							$sql .= " UNION ";		
							$sql .= $sql_query;
							$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";	
							$result = mysqli_query($link, $sql);						
						}				
					} else if(($v["COURSE"] > 180) && ($v["COURSE"] < 270)) {
						//2
						$course = 2;
						// Главная четверть
						$sql_query.= " AND ((p.latitude >= ".($v["LAT"])." AND p.longitude <= ".($v["LON"])."))";
						$sql = $sql_query;	
						$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
						$sql .= " UNION ";		
						$sql .= $sql_query;
						$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";	
						$result = mysqli_query($link, $sql);	
						// Второстепенные четверти		
						if(mysqli_num_rows($result) == 0) {
							$sql_query = $text_query;
							$sql_query.= " AND (
											(p.latitude >= ".($v["LAT"])." AND p.longitude >= ".($v["LON"]).") OR
											(p.latitude <= ".($v["LAT"])." AND p.longitude <= ".($v["LON"]).")
										)";
							$sql = $sql_query;	
							$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
							$sql .= " UNION ";		
							$sql .= $sql_query;
							$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";	
							$result = mysqli_query($link, $sql);						
						}			
					} else if(($v["COURSE"] > 90) && ($v["COURSE"] < 180)) {
						//3
						$course = 3;
						// Главная четверть
						$sql_query.= " AND ((p.latitude <= ".($v["LAT"])." AND p.longitude <= ".($v["LON"])."))";
						$sql = $sql_query;	
						$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
						$sql .= " UNION ";		
						$sql .= $sql_query;
						$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";	
						$result = mysqli_query($link, $sql);	
						// Второстепенные четверти		
						if(mysqli_num_rows($result) == 0) {
							$sql_query = $text_query;
							$sql_query.= " AND (
											(p.latitude >= ".($v["LAT"])." AND p.longitude <= ".($v["LON"]).") OR
											(p.latitude <= ".($v["LAT"])." AND p.longitude >= ".($v["LON"]).")
										)";
							$sql = $sql_query;	
							$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
							$sql .= " UNION ";		
							$sql .= $sql_query;
							$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";	
							$result = mysqli_query($link, $sql);						
						}				
					} else if(($v["COURSE"] > 0) && ($v["COURSE"] < 90)) {
						//4
						$course = 4;
						// Главная четверть
						$sql_query.= " AND ((p.latitude <= ".($v["LAT"])." AND p.longitude >= ".($v["LON"])."))";
						$sql = $sql_query;	
						$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
						$sql .= " UNION ";		
						$sql .= $sql_query;
						$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";	
						$result = mysqli_query($link, $sql);	
						// Второстепенные четверти		
						if(mysqli_num_rows($result) == 0) {
							$sql_query = $text_query;
							$sql_query.= " AND (
											(p.latitude >= ".($v["LAT"])." AND p.longitude >= ".($v["LON"]).") OR
											(p.latitude <= ".($v["LAT"])." AND p.longitude <= ".($v["LON"]).")
										)";
							$sql = $sql_query;	
							$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
							$sql .= " UNION ";		
							$sql .= $sql_query;
							$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";	
							$result = mysqli_query($link, $sql);						
						}				
					} else if($v["COURSE"] == 270) {
						//1-2
						$course = 12;
						$sql_query .= " AND p.latitude >= ".($v["LAT"]);
						$sql = $sql_query;	
						$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
						$sql .= " UNION ";		
						$sql .= $sql_query;
						$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";
						$result = mysqli_query($link, $sql);
					} else if($v["COURSE"]== 180) {
						//2-3
						$course = 23;
						$sql_query .= " AND p.longitude <= ".($v["LON"]);
						$sql = $sql_query;	
						$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
						$sql .= " UNION ";		
						$sql .= $sql_query;
						$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";
						$result = mysqli_query($link, $sql);
					} else if($v["COURSE"]== 90) {
						//3-4
						$course = 34;
						$sql_query .= " AND p.latitude <= ".($v["LAT"]);
						$sql = $sql_query;	
						$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
						$sql .= " UNION ";		
						$sql .= $sql_query;
						$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";
						$result = mysqli_query($link, $sql);
					} else if(($v["COURSE"]== 0) || ($v["COURSE"]== 360)) {
						//1-4
						$course = 14;
						$sql_query .= " AND p.longitude >= ".($v["LON"]);
						$sql = $sql_query;	
						$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
						$sql .= " UNION ";		
						$sql .= $sql_query;
						$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";
						$result = mysqli_query($link, $sql);
					}
				}
				$point;
				$direction = -1;
				switch(mysqli_num_rows($result)) {
					case 0:
						$direction = -1;
					break;
					case 1:
						$point = mysqli_fetch_assoc($result);
						$direction = $point["direction"];
					break;
					case 2:
						// Определяем точки направлений
						$point_direction[0][0] = mysqli_fetch_assoc($result);
						$point_direction[1][0] = mysqli_fetch_assoc($result);
						
						// Проверяем точку направления 0 (перевести в вид функции)
						$index = searchIndex($fullrout, $point_direction[0][0]["orders"]);
						if(isset($fullrout[$index + 1])) {
							$point_direction[0][1] = $fullrout[$index + 1];		
						} else {
							$point_direction[0][1] = $fullrout[0];
						}
						
						// Проверяем точку направления 1 (перевести в вид функции)
						$index = searchIndex($fullrout, $point_direction[1][0]["orders"]);
						if(isset($fullrout[$index + 1])) {
							$point_direction[1][1] = $fullrout[$index + 1];		
						} else {
							$point_direction[1][1] = $fullrout[0];
						}
						
						// Устанавливаем направление транспортного средства
						switch($course) {
							case 1:
								if(($point_direction[0][0]["latitude"] <= $point_direction[0][1]["latitude"]) || ($point_direction[0][0]["longitude"] <= $point_direction[0][1]["longitude"])) {
									$point = $point_direction[0][0];
									$direction = $point_direction[0][0]["direction"];
								} elseif(($point_direction[1][0]["latitude"] <= $point_direction[1][1]["latitude"]) || ($point_direction[1][0]["longitude"] <= $point_direction[1][1]["longitude"])) {
									$point = $point_direction[1][0];
									$direction = $point_direction[1][0]["direction"];
								} else {
									$direction = -1;
								}
							break;
							case 2:
								if(($point_direction[0][0]["latitude"] <= $point_direction[0][1]["latitude"]) || ($point_direction[0][0]["longitude"] >= $point_direction[0][1]["longitude"])) {
									$point = $point_direction[0][0];
									$direction = $point_direction[0][0]["direction"];
								} elseif(($point_direction[1][0]["latitude"] <= $point_direction[1][1]["latitude"]) || ($point_direction[1][0]["longitude"] >= $point_direction[1][1]["longitude"])) {
									$point = $point_direction[1][0];
									$direction = $point_direction[1][0]["direction"];
								} else {
									$direction = -1;
								}
							break;
							case 3:
								if(($point_direction[0][0]["latitude"] >= $point_direction[0][1]["latitude"]) || ($point_direction[0][0]["longitude"] >= $point_direction[0][1]["longitude"])) {
									$point = $point_direction[0][0];
									$direction = $point_direction[0][0]["direction"];
								} elseif(($point_direction[1][0]["latitude"] >= $point_direction[1][1]["latitude"]) || ($point_direction[1][0]["longitude"] >= $point_direction[1][1]["longitude"])) {
									$point = $point_direction[1][0];
									$direction = $point_direction[1][0]["direction"];
								} else {
									$direction = -1;
								}
							break;
							case 4:
								if(($point_direction[0][0]["latitude"] >= $point_direction[0][1]["latitude"]) || ($point_direction[0][0]["longitude"] <= $point_direction[0][1]["longitude"])) {
									$point = $point_direction[0][0];
									$direction = $point_direction[0][0]["direction"];
								} elseif(($point_direction[1][0]["latitude"] >= $point_direction[1][1]["latitude"]) || ($point_direction[1][0]["longitude"] <= $point_direction[1][1]["longitude"])) {
									$point = $point_direction[1][0];
									$direction = $point_direction[1][0]["direction"];
								} else {
									$direction = -1;
								}
							break;
							case 12:
								if($point_direction[0][0]["latitude"] <= $point_direction[0][1]["latitude"]) {
									$point = $point_direction[0][0];
									$direction = $point_direction[0][0]["direction"];
								} elseif($point_direction[1][0]["latitude"] <= $point_direction[1][1]["latitude"]) {
									$point = $point_direction[1][0];
									$direction = $point_direction[1][0]["direction"];
								} else {
									$direction = -1;
								}
							break;
							case 23:
								if($point_direction[0][0]["longitude"] >= $point_direction[0][1]["longitude"]) {
									$point = $point_direction[0][0];
									$direction = $point_direction[0][0]["direction"];
								} elseif($point_direction[1][0]["longitude"] >= $point_direction[1][1]["longitude"]) {
									$point = $point_direction[1][0];
									$direction = $point_direction[1][0]["direction"];
								} else {
									$direction = -1;
								}
							break;
							case 34:
								if($point_direction[0][0]["latitude"] >= $point_direction[0][1]["latitude"]) {
									$point = $point_direction[0][0];
									$direction = $point_direction[0][0]["direction"];
								} elseif($point_direction[1][0]["latitude"] >= $point_direction[1][1]["latitude"]) {
									$point = $point_direction[1][0];
									$direction = $point_direction[1][0]["direction"];
								} else {
									$direction = -1;
								}
							break;
							case 14:
								if($point_direction[0][0]["longitude"] <= $point_direction[0][1]["longitude"]) {
									$point = $point_direction[0][0];
									$direction = $point_direction[0][0]["direction"];
								} elseif($point_direction[1][0]["longitude"] <= $point_direction[1][1]["longitude"]) {
									$point = $point_direction[1][0];
									$direction = $point_direction[1][0]["direction"];
								} else {
									$direction = -1;
								}
							break;
						}
					break;
				}
				
				if($direction = -1) {
					$arrStation[0]["time"] = 0;
					$arrStation[0]["vel"] = 1;
				}
				if($point["orders"] != "") {
					$i_begin = searchIndex($arrStation, $point["orders"]);
					$arrStation[$i_begin]["time"] = $point["result"] / ((10 + $v["VELOCITY"]) / 2);
					$arrStation[$i_begin]["vel"] = (15 + $v["VELOCITY"]) / 2;
				}	
			}
			$array_range = getRangePoint(0.5, $id_rout);
			$i = 0;
			while(!isset($arrStation[$i]["time"])) {
				$i++;
			}			
			$index = $i + 1;
			if($index == count($arrStation)) $index = 0;
			$prev = $i;
			while($index != $i) {
				//
				if(!isset($arrStation[$index]["time"])) {
					
					$arrStation[$index]["time"] = $arrStation[$prev]["time"] + $array_range[$arrStation[$prev]["id_points"]][$arrStation[$index]["id_points"]] / $arrStation[$prev]["vel"];
					$arrStation[$index]["vel"] = $arrStation[$prev]["vel"];
				} else {
					if($arrStation[$index]["time"] > $arrStation[$prev]["time"]) {
						$arrStation[$index]["time"] = $arrStation[$prev]["time"] + $array_range[$arrStation[$prev]["id_points"]][$arrStation[$index]["id_points"]] / $arrStation[$prev]["vel"];
						$arrStation[$index]["vel"] = $arrStation[$prev]["vel"];
					}
				}
				
				$prev = $index;
				if($index + 2 == count($arrStation)) {
					$index = -1;
				}
				$index++;
			}
		} 
		return $arrStation;
	}
	// Функция получения маршрута для вывода 
	function getRoutForPrint($id_rout) {
		$md5 = md5("rout".$id_rout);
		if(file_exists("cache/".$md5)) {
			$rout = unserialize(file_get_contents("cache/".$md5));			
		} else {
			$link = mysqli_connect('127.0.0.1', 'root', '', 'transport_system');
			mysqli_set_charset($link , 'utf8');
			
			$rout = array();
			$result = mysqli_query($link, "SELECT p.id_points, p.point_name, p.latitude, p.longitude, t.orders, t.station_status, t.id_routs, t.direction
				FROM points p, transport_station t 
				WHERE p.id_points = t.id_points
				AND t.id_routs = ".$id_rout."
				AND t.station_status > 0
				ORDER BY t.orders"
			);
			while($result_rout = mysqli_fetch_assoc($result)) {
				array_push($rout, $result_rout);	
			}
			file_put_contents("cache/".$md5, serialize($rout));
		}
		return $rout;
	}
	// Function get full routs for print
	function getForPrint($id_rout) {
		$md5 = md5("printrout".$id_rout);
		if(file_exists("cache/".$md5)) {
			$rout = unserialize(file_get_contents("cache/".$md5));			
		} else {
			$rout = array();
			$link = mysqli_connect('127.0.0.1', 'root', '', 'transport_system');
			mysqli_set_charset($link , 'utf8');
			
			$result = mysqli_query($link, "SELECT p.latitude as lat, p.longitude as lng
				FROM points p, transport_station t 
				WHERE p.id_points = t.id_points
				AND t.id_routs = ".$id_rout."
				ORDER BY t.orders"
			);
			while($result_rout = mysqli_fetch_assoc($result)) {
				$result_rout["lat"] = (double)$result_rout["lat"];
				$result_rout["lng"] = (double)$result_rout["lng"];
				array_push($rout, $result_rout);	
			}
			file_put_contents("cache/".$md5, serialize($rout));
		}
		return $rout;
	}
	// Function output class to screen
	function printClass($root, $level) {
		echo "Level - ".$level."</br>";
		
		echo "<pre>";
		print_r($root->getAllPoint());
		echo "</pre>";

		if($root->getNextPoint() != NULL) {
			printClass($root->getNextPoint(), ++$level);
		}
	}
	// Функция взятия типа транспортного средства
	function getTypeTransport($idRout, $link) {
		//global $link;
		$md5 = md5("type".$idRout);
		if(file_exists("cache/".$md5)) {
			$rout = unserialize(file_get_contents("cache/".$md5));			
		} else {
			$rout = array();
			$result = mysqli_query($link, "SELECT num_rout, id_type
				FROM routs
				WHERE id_routs = ".$idRout
			);
			while($result_rout = mysqli_fetch_assoc($result)) {
				array_push($rout, $result_rout);	
			}
			file_put_contents("cache/".$md5, serialize($rout));
		}
		return $rout;
	}
	// Функция получения транспорта 
	function getTransportOtherService($idRout, $url) {
		global $rout;
		global $prefix;
		$rout = $idRout;
		// Получаем координаты транспортных средств
		$myCurl = curl_init();
		curl_setopt_array($myCurl, array(
			CURLOPT_URL => $url,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array('Accept: application/json, text/javascript, */*; q=0.01'),
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_REFERER => 'http://edu-ekb.ru/gmap/'		
		));
		$info = json_decode(curl_exec($myCurl));
		curl_close($myCurl);
		
		
		foreach($info as $k => $v) {
			$info[$k] = (array)$v;
		}
		// Временная маленькая проверка на маршрутку и автобус
		if($rout == 18) {
			$prefix = "avtb_";
		} elseif($rout == 21) {
			$prefix = "avtm_0";
		} elseif(($rout == 3) || ($rout == 22)) {
			$prefix = "tram_";
		} elseif(($rout == 7) || ($rout == 11)) {
			$prefix = "trol_";
		}

		// Отсеиваем ненужные транспортные средства
		$result_times_rout = array_filter($info, function($innerArray){
			global $rout;
			global $prefix;
			return ($innerArray["marshrut"] == $prefix.$rout); 
		});
		
		
		
		$result_rout = array();
		foreach($result_times_rout as $k => $v) {
			$result_rout[$k]["LAT"] = $v["latitude"] / 600000;
			$result_rout[$k]["LON"] = $v["longitude"] / 600000;
			$v["azimuth"] /= 100;
			$v["azimuth"] -= 90;
			if($v["azimuth"] < 0) $v["azimuth"] += 360;
			$result_rout[$k]["COURSE"] = $v["azimuth"];
			$result_rout[$k]["ROUTE"] = $rout;
			if($v["speed"] > 100) $v["speed"] /= 10;  
			$result_rout[$k]["VELOCITY"] = $v["speed"];
			$result_rout[$k]["ON_ROUTE"] = 1;
			$result_rout[$k]["BOARD_NUM"] = $v["pe"];
		}
		return $result_rout;
	}	
	// Функция для запроса данных для клиента
	function getTransportTimeClient($num_rout, $type) {
		global $rout;

		$rout = $num_rout;
		
		$url = "";
		$is_exist = -1;
		switch($type) {
			//case 1 сделать для автобуса и переделать формат под трамвайный и троллейбусный
			case 1:
				$is_exist = 2;
				$url = 'http://edu-ekb.ru/gmap/resources/entities.vgeopoint/mar/,avtb_18,avtm_021,';
			break;
			case 2:
				$is_exist = 1;
				$url = "http://map.ettu.ru/api/v2/troll/boards/?apiKey=111&order=1";
			break;
			case 3:
				$is_exist = 1;
				$url = "http://map.ettu.ru/api/v2/tram/boards/?apiKey=111&order=1";
			break;
			default :
				$is_exist = 0;
		}
		
		if($is_exist == 1) {
			// Получаем координаты транспортных средств
			$result = file_get_contents($url, false, stream_context_create(array(
				'http' => array(
					'method'  => 'GET',
					'header'  => 'Content-type: application/x-www-form-urlencoded'
				)
			)));
			
			$array = json_decode($result,TRUE);
			
			if(!isset($array["error"])) {
				// Отсеиваем ненужные транспортные средства
				$result_rout = array_filter($array["vehicles"], function($innerArray){
					global $rout;
					return ($innerArray["ROUTE"] == $rout); 
				});
			} else {
				$url = "http://edu-ekb.ru/gmap/resources/entities.vgeopoint/mar/,tram_3,tram_22,trol_7,trol_11,";
				$result_rout = getTransportOtherService($rout, $url);
			}
			
		} 
		if($is_exist == 2) {
			$result_rout = getTransportOtherService($rout, $url); 
		}
		return $result_rout;
	}
	// Функция определения типа транспотрного средства, запрос на сревер о местонахождении типа т.с. и выборка нужно т.с.
	function getTransportTime($idRout, $link) {
		global $rout;
		$types = getTypeTransport($idRout, $link);

		$rout = $types[0]["num_rout"];
		
		$url = "";
		$is_exist = -1;
		switch($types[0]["id_type"]) {
			//case 1 сделать для автобуса и переделать формат под трамвайный и троллейбусный
			case 1:
				$is_exist = 2;
				$url = 'http://edu-ekb.ru/gmap/resources/entities.vgeopoint/mar/,avtb_18,avtm_021,';
			break;
			case 2:
				$is_exist = 1;
				$url = "http://map.ettu.ru/api/v2/troll/boards/?apiKey=111&order=1";
			break;
			case 3:
				$is_exist = 1;
				$url = "http://map.ettu.ru/api/v2/tram/boards/?apiKey=111&order=1";
			break;
			default :
				$is_exist = 0;
		}
		
		if($is_exist == 1) {
			// Получаем координаты транспортных средств
			$result = file_get_contents($url, false, stream_context_create(array(
				'http' => array(
					'method'  => 'GET',
					'header'  => 'Content-type: application/x-www-form-urlencoded'
				)
			)));
			
			$array = json_decode($result,TRUE);
			
			if(!isset($array["error"])) {
				// Отсеиваем ненужные транспортные средства
				$result_rout = array_filter($array["vehicles"], function($innerArray){
					global $rout;
					return ($innerArray["ROUTE"] == $rout); 
				});
			} else {
				$url = "http://edu-ekb.ru/gmap/resources/entities.vgeopoint/mar/,tram_3,tram_22,trol_7,trol_11,";
				$result_rout = getTransportOtherService($rout, $url);
			}
			
		} 
		if($is_exist == 2) {
			$result_rout = getTransportOtherService($rout, $url); 
		}
		return $result_rout;
	}
	// Функция определения метро
	function queryCircleMetro($lat, $hlat, $lon, $hlon) {
		global $link;
		$result = mysqli_query($link, "
			SELECT DISTINCT t.id_routs FROM points p, transport_station t
			WHERE p.id_points = t.id_points
			AND p.latitude > ".($lat - $hlat)." 	 
			AND p.latitude < ".($lat + $hlat)."
			AND p.longitude > ".($lon - $hlon)." 	 
			AND p.longitude < ".($lon + $hlon)."
			AND t.id_routs = 7
			AND t.station_status > 0
		");	
		return $result;
	}
	// Функция получения расстояния между остановками
	function getLength($begin, $end, $link) {
		//global $link;
		$result = mysqli_query($link, "
			SELECT Length FROM lengths
			WHERE point_1 = ".$begin."
			AND point_2 = ".$end."
		");
		$range = mysqli_fetch_assoc($result);
		return $range["Length"];
	}
	// Функция получения полного маршрута 
	function getFullRout($id_rout, $link) {
		//global $link;

		$md5 = md5("fullrout".$id_rout);
		if(file_exists("cache/".$md5)) {
			$rout = unserialize(file_get_contents("cache/".$md5));			
		} else {
			$rout = array();
			$result = mysqli_query($link, "SELECT p.id_points, p.point_name, p.latitude, p.longitude, t.orders, t.station_status, t.id_routs, t.direction
				FROM points p, transport_station t 
				WHERE p.id_points = t.id_points
				AND t.id_routs = ".$id_rout."
				ORDER BY t.orders"
			);
			while($result_rout = mysqli_fetch_assoc($result)) {
				array_push($rout, $result_rout);	
			}
			file_put_contents("cache/".$md5, serialize($rout));
		}
		return $rout;
	}	
	// Функция получения маршрута 
	function getRout($id_rout, $link) {
		//global $link;

		$md5 = md5("rout".$id_rout);
		if(file_exists("cache/".$md5)) {
			$rout = unserialize(file_get_contents("cache/".$md5));			
		} else {
			$rout = array();
			$result = mysqli_query($link, "SELECT p.id_points, p.point_name, p.latitude, p.longitude, t.orders, t.station_status, t.id_routs, t.direction
				FROM points p, transport_station t 
				WHERE p.id_points = t.id_points
				AND t.id_routs = ".$id_rout."
				AND t.station_status > 0
				ORDER BY t.orders"
			);
			while($result_rout = mysqli_fetch_assoc($result)) {
				array_push($rout, $result_rout);	
			}
			file_put_contents("cache/".$md5, serialize($rout));
		}
		return $rout;
	}	
	// Функция для определения маршрутов
	function queryCircle($lat, $hlat, $lon, $hlon) {
		global $link;
		$result = mysqli_query($link, "
			SELECT DISTINCT t.id_routs FROM points p, transport_station t
			WHERE p.id_points = t.id_points
			AND p.latitude > ".($lat - $hlat)." 	 
			AND p.latitude < ".($lat + $hlat)."
			AND p.longitude > ".($lon - $hlon)." 	 
			AND p.longitude < ".($lon + $hlon)."
			AND t.station_status > 0
		");	
		return $result;
	}
	// Получаем urp
	function getUrp($lat) {
		global $urm;
		global $pi;
		return cos(($lat * $pi) / 180) * $urm;
	}	
	// Получаем погрешности
	function getH($urp, $r) {
		global $urm;
		$h["lat"] = $r / $urm;
		$h["lon"] = $r / $urp;
		return $h;
	}
	// Определяем маршруты
	function getResult($lat, $lon, $range) {
		$d_r = 0.5;
		$d_i = 1;
		
		$h = getH(getUrp($lat), $range);	
		$result = queryCircle($lat, $h['lat'], $lon, $h['lon']);
		
		while(mysqli_num_rows($result) == 0) {
			$h_end = getH(getUrp($lat), $range + $d_r * $d_i);	
			$result = queryCircle($lat, $h['lat'], $lon, $h['lon']);
			$d_i++;
		}
		
		return $result;
	}
	// Функция создания и заполнения пересечения маршрутов
	function createMatrix() {
		global $r;
		global $link;
		// Просматриваем маршруты в базе данных
		$result = mysqli_query($link, "
			SELECT * FROM routs 
		");
		// Определяем количество маршрутов
		$count = mysqli_num_rows($result);
		
		$md5 = md5("matrix".$count.$r);
		// Проверяем существует ли такая же матрица
		if(file_exists("cache/".$md5)) {
			$matrix = unserialize(file_get_contents("cache/".$md5));			
		} else {
			$matrix_routs = array();
			while($rout = mysqli_fetch_assoc($result)) {
				$matrix_routs[$rout["id_routs"]] = 0;	
			}
			foreach($matrix_routs as $k1=>$v1) {
				foreach($matrix_routs as $k2=>$v2) {
					$matrix[$k1][$k2] = 0;
				}	
			}
			// Заполнение таблицы маршрутов
			$result = mysqli_query($link, "
				SELECT DISTINCT p.latitude, p.longitude 
				FROM `points` p, `transport_station` t 
				WHERE t.station_status > 0 AND 
				p.id_points = t.id_points
			");

			while($point = mysqli_fetch_assoc($result)) {
				$h = getH(getUrp($point["latitude"]), 0.1);
				// Получаем пересечения маршрутов
				$result_rout = queryCircle($point["latitude"], $h["lat"], $point['longitude'], $h["lon"]);
				$massive = array();
				
				while($rout = mysqli_fetch_assoc($result_rout)) {
					array_push($massive, $rout["id_routs"]);
				}
				
				// Записываем пересечение маршрутов
				if(count($massive) > 1) {
					foreach($massive as $k1=>$v1) {
						foreach($massive as $k2=>$v2) {
							if($v1 != $v2) $matrix[$v1][$v2] = 1;
						}	
					}
				}
			}
			file_put_contents("cache/".$md5, serialize($matrix));
		}	
		return $matrix;
	}
	// Функция получения остановок маршрута начала и конца
	function getStationBE($lat, $hlat, $lon, $hlon, $id_rout) {
		global $link;
		
		$md5 = md5($lat.$hlat.$lon.$hlon.$id_rout);
	
		if(file_exists("cacheTime/".$md5)) {
			$arr = unserialize(file_get_contents("cacheTime/".$md5));			
		} else {			
			$result = mysqli_query($link, "
				SELECT DISTINCT p.point_name, t.id_routs, p.latitude, p.longitude, t.orders, p.id_points, t.direction
				FROM points p, transport_station t
				WHERE p.id_points = t.id_points
				AND t.id_routs = ".$id_rout."
				AND p.latitude > ".($lat - $hlat)." 	 
				AND p.latitude < ".($lat + $hlat)."
				AND p.longitude > ".($lon - $hlon)." 	 
				AND p.longitude < ".($lon + $hlon)."
				AND t.station_status > 0
			");
			
			$arr = array();
			while($name_point = mysqli_fetch_assoc($result)) {
				array_push($arr, $name_point);
			}		
			file_put_contents("cacheTime/".$md5, serialize($arr));
		}
		return $arr;
	}
	// Функция пересечения маршрутов
	function getIntersept($idEndRout, $idBeginRout) {
		global $link;
		global $r;
		global $urm;
		global $pi;
		
		$md5 = md5($idBeginRout.$idEndRout);
	
		if(file_exists("cache/".$md5)) {
			$two_array = unserialize(file_get_contents("cache/".$md5));			
		} else {
		
			$result = mysqli_query($link, "
				SELECT DISTINCT t1.id_routs as routs1, p1.point_name as name1, p1.latitude as latitude1, p1.longitude as longitude1, t1.orders as orders1, t1.id_points as id_points1, t1.direction as direction1,
								t2.id_routs as routs2, p2.point_name as name2, p2.latitude as latitude2, p2.longitude as longitude2, t2.orders as orders2, t2.id_points as id_points2, t2.direction as direction2
				FROM points p1, transport_station t1, points p2, transport_station t2
				WHERE p1.id_points = t1.id_points
				AND p2.id_points = t2.id_points
				AND ABS(p1.latitude - p2.latitude) <= ".$r / $urm."  
				AND ABS(p1.longitude - p2.longitude) <= ".$r." / (cos((p1.longitude * ".$pi.") / 180) * ".$urm.")
				AND t1.station_status > 0
				AND t2.station_status > 0
				AND t1.id_routs = ".$idBeginRout."
				AND t2.id_routs = ".$idEndRout."
			");
			
			$array1 = array();
			$array2 = array();
			$str1 = array();
			$str2 = array();
			$i = 0;
			
			while($name_point = mysqli_fetch_assoc($result)) {
				
				// Получаем остановки 1 маршрута
				$station1["point_name"] = $name_point["name1"];
				$station1["latitude"] = $name_point["latitude1"];
				$station1["longitude"] = $name_point["longitude1"];
				$station1["orders"] = $name_point["orders1"];
				$station1["id_routs"] = $name_point["routs1"];
				$station1["id_points"] = $name_point["id_points1"];
				$station1["direction"] = $name_point["direction1"];
				
				// Получаем остановки 2 маршрута
				$station2["point_name"] = $name_point["name2"];
				$station2["latitude"] = $name_point["latitude2"];
				$station2["longitude"] = $name_point["longitude2"];
				$station2["orders"] = $name_point["orders2"];
				$station2["id_routs"] = $name_point["routs2"];
				$station2["id_points"] = $name_point["id_points2"];
				$station2["direction"] = $name_point["direction2"];
				
				// Проверка 1 остановок
				if(!isset($str1[$station1["orders"]])) {
					$str1[$station1["orders"]] = 1;
					array_push($array1, $station1);
					$i++;
				}
				// Проверка 2 остановок
				if(!isset($str2[$station2["orders"]])) {
					$str2[$station2["orders"]] = 1;
					array_push($array2, $station2);
					$i++;
				}
			}		
			$two_array[0] = $array1;
			$two_array[1] = $array2;
			file_put_contents("cache/".$md5, serialize($two_array));
		}
		return $two_array;
	}
	// Функция поиска индекса
	function searchIndex($arr, $order) {
		// bin search
		$left = 0;
		$right = count($arr);	
		while($left < $right) {
			$middle = (int)($left  + ($right - $left) / 2);		
			if($arr[$middle]["orders"] >=  $order) {
				$right = $middle;
			} else {
				$left = $middle + 1;
			}
		}
		if($arr[$right]["orders"] ==  $order) {
			return $right;
		} else {
			return NULL;
		}
	}	
	// Функция записи расстояния в массив
	function getRangePoint($array_point, $rout) {
		global $link;
		
		$md5 = md5("range_rout".$rout);
	
		if(file_exists("cache/".$md5)) {
			$range = unserialize(file_get_contents("cache/".$md5));			
		} else {			
			
			for($i = 1; $i < count($array_point); $i++) {
				$range[$array_point[$i - 1]["id_points"]][$array_point[$i]["id_points"]] = getLength($array_point[$i - 1]["id_points"], $array_point[$i]["id_points"], $link);
			}
			file_put_contents("cache/".$md5, serialize($range));
		}
		return $range;
	}
	// Функция разбора дерева OverTimeHard при 0-1 пересадках 1.22 секунды, при 3-х пересадках 6-7 секунд
	function viewTreeReserve($roots, $arr) {
		global $urm;
		global $pi;
		global $r;
		global $range_mass;
		global $counts;
		global $link; 
		global $static;
		global $avgVelosity;
		$name = array();
		$range = array();
		for($i = 0; $i < $roots->getCountPoint(); $i++) {
			$is_flg = 1;
			array_push($arr, $roots->getOnePoint($i));
			if($roots->getNextPoint() != NULL) {
				$time_range = 0.0;
				// Пересмотреть разбор
				if(count($arr) > 1) {
					if($arr[count($arr) - 1]["id_routs"] != $arr[count($arr) - 2]["id_routs"]) {
						$urp = getUrp($arr[count($arr) - 2]["latitude"]);
						$h = getH($urp, $r);
						
						$time_range = sqrt(pow(($arr[count($arr) - 2]["latitude"] - $arr[count($arr) - 1]["latitude"]) * $urm, 2) + pow(($arr[count($arr) - 2]["longitude"] - $arr[count($arr) - 1]["longitude"]) * $urp, 2));
						if($time_range <= $r) {
							viewTree($roots->getNextPoint(), $arr);	
						}
					} else if($arr[count($arr) - 1]["id_routs"] == $arr[count($arr) - 2]["id_routs"]) {
						if(($arr[count($arr) - 2]["direction"] == $arr[count($arr) - 1]["direction"]) && ($arr[count($arr) - 2]["orders"] < $arr[count($arr) - 1]["orders"])) {
							viewTree($roots->getNextPoint(), $arr);
						}
						if(($arr[count($arr) - 2]["direction"] != $arr[count($arr) - 1]["direction"]) && ($arr[count($arr) - 2]["orders"] > $arr[count($arr) - 1]["orders"])) {
							viewTree($roots->getNextPoint(), $arr);
						}
					}
				} else {
					viewTree($roots->getNextPoint(), $arr);
				}
			} else {		
				$full_path = array();
				$full_count = 0;
				$max_range = 0;
				$temp_array = array();
				$close = 0;
				
				for($t = 1; $t < count($arr); $t += 2) {
					$temp_full_array = array();
					$temp_full_array = getRout($arr[$t - 1]["id_routs"], $link);
					
					if(isset($name[$arr[$t - 1]["id_routs"]][$arr[$t - 1]["orders"]])) {
						$j_begin = $name[$arr[$t - 1]["id_routs"]][$arr[$t - 1]["orders"]];
					} else {
						$j_begin = searchIndex($temp_full_array, $arr[$t - 1]["orders"]);
						$name[$arr[$t - 1]["id_routs"]][$arr[$t - 1]["orders"]] = $j_begin;
					}
					
					if(isset($name[$arr[$t - 1]["id_routs"]][$arr[$t]["orders"]])) {
						$j_end = $name[$arr[$t - 1]["id_routs"]][$arr[$t]["orders"]];
					} else {
						$j_end = searchIndex($temp_full_array, $arr[$t]["orders"]);
						$name[$arr[$t - 1]["id_routs"]][$arr[$t]["orders"]] = $j_end;
					}
					
					if(($temp_full_array[$j_begin]["direction"] == $temp_full_array[$j_end]["direction"]) && ($temp_full_array[$j_begin]["orders"] > $temp_full_array[$j_end]["orders"])) {
						$close = 1;
						$max_range = 999.0;
					}
					
					if($close == 0) {
						$c = $j_begin;
						$variant = 0;
						// Вычисление пути и его расстояния
						while($c != $j_end) {		
							array_push($temp_array, $temp_full_array[$c]);
							$c++;
							if($c == count($temp_full_array)) $c = 0;
							
							if(($temp_full_array[$c]["point_name"] == $temp_full_array[$j_end]["point_name"]) && ($temp_full_array[$c]["orders"] != $temp_full_array[$j_end]["orders"])) {
								$variant = 100.0;
								break;
							}
							
							if(($temp_array[count($temp_array) - 1]["station_status"] > 0) && ($temp_array[count($temp_array) - 2]["id_routs"] == $temp_array[count($temp_array) - 1]["id_routs"])) {	
								if(!isset($range[$temp_array[count($temp_array) - 2]["id_points"]][$temp_array[count($temp_array) - 1]["id_points"]])) {
									$range[$temp_array[count($temp_array) - 2]["id_points"]][$temp_array[count($temp_array) - 1]["id_points"]] = getLength($temp_array[count($temp_array) - 2]["id_points"], $temp_array[count($temp_array) - 1]["id_points"], $link);
								}
								$variant += $range[$temp_array[count($temp_array) - 2]["id_points"]][$temp_array[count($temp_array) - 1]["id_points"]];
							}
							
						} 
						array_push($temp_array, $temp_full_array[$c]);

						if(($temp_array[count($temp_array) - 1]["station_status"] > 0) && ($temp_array[count($temp_array) - 2]["id_routs"] == $temp_array[count($temp_array) - 1]["id_routs"])) {	
							if(!isset($range[$temp_array[count($temp_array) - 2]["id_points"]][$temp_array[count($temp_array) - 1]["id_points"]])) {
								$range[$temp_array[count($temp_array) - 2]["id_points"]][$temp_array[count($temp_array) - 1]["id_points"]] = getLength($temp_array[count($temp_array) - 2]["id_points"], $temp_array[count($temp_array) - 1]["id_points"], $link);
							}
							$variant += $range[$temp_array[count($temp_array) - 2]["id_points"]][$temp_array[count($temp_array) - 1]["id_points"]];
						}
						$max_range += $variant;
						if($static < $max_range) break; 
					}			
					
				}
				if($static > $max_range) {
					$range_mass[$counts]["range"] = $max_range;
					$range_mass[$counts]["array"] = $temp_array;
					$static = $max_range;
				}

			}
			array_pop($arr);
		}
	}
	// Функция разбора дерева OverTimeHard при 0-1 пересадках 1.16 секунды, при 3-х пересадках 4-4.5 секунд warning not edit
	function viewTree($roots, $arr) {
		global $urm;
		global $pi;
		global $r;
		global $range_mass;
		global $counts;
		global $link; 
		global $static;   
		global $gl_time;
		global $point_begin;
		global $point_end;
		global $avgVelosity;
		for($i = 0; $i < $roots->getCountPoint(); $i++) {
			array_push($arr, $roots->getOnePoint($i));
			if($roots->getNextPoint() != NULL) {
				if(count($arr) > 1) {
					if($arr[count($arr) - 1]["id_routs"] != $arr[count($arr) - 2]["id_routs"]) {
						$h = getH(getUrp($arr[count($arr) - 2]["latitude"]), $r + 0.2);
						$time_range = sqrt(pow(($arr[count($arr) - 2]["latitude"] - $arr[count($arr) - 1]["latitude"]) * $urm, 2) + pow(($arr[count($arr) - 2]["longitude"] - $arr[count($arr) - 1]["longitude"]) * $urp, 2));		
						if($time_range <= ($r - 0.2)) {
							viewTree($roots->getNextPoint(), $arr);	
						}
					// Проверка проезда всего или почти всего пути
					} else {
						if(($arr[count($arr) - 2]["direction"] == $arr[count($arr) - 1]["direction"]) && ($arr[count($arr) - 2]["orders"] < $arr[count($arr) - 1]["orders"])) {
							viewTree($roots->getNextPoint(), $arr);
						} else if (($arr[count($arr) - 2]["direction"] != $arr[count($arr) - 1]["direction"])) {
							viewTree($roots->getNextPoint(), $arr);
						}
					}
				} else {
					viewTree($roots->getNextPoint(), $arr);
				}
			} else {
				$max_range = 0;
				$times = 0;
				$temp_array = array();
				$close = 0;
				$range_for_rout = array();
			
				for($t = 1; $t < count($arr); $t += 2) {
					$temp_full_array = array();
					
					// Получаем маршрут
					$temp_full_array = getRout($arr[$t - 1]["id_routs"], $link);
					
					//Получаем расстояния 
					$array_range = getRangePoint($temp_full_array, $arr[$t - 1]["id_routs"]);
					
					// Получаем начальный порядковый номер
					if(isset($name[$arr[$t - 1]["id_routs"]][$arr[$t - 1]["orders"]])) {
						$j_begin = $name[$arr[$t - 1]["id_routs"]][$arr[$t - 1]["orders"]];
					} else {
						$j_begin = searchIndex($temp_full_array, $arr[$t - 1]["orders"]);
						$name[$arr[$t - 1]["id_routs"]][$arr[$t - 1]["orders"]] = $j_begin;
					}

					// Получаем конечный порядковый номер
					if(isset($name[$arr[$t - 1]["id_routs"]][$arr[$t]["orders"]])) {
						$j_end = $name[$arr[$t - 1]["id_routs"]][$arr[$t]["orders"]];
					} else {
						$j_end = searchIndex($temp_full_array, $arr[$t]["orders"]);
						$name[$arr[$t - 1]["id_routs"]][$arr[$t]["orders"]] = $j_end;
					}
										
					
					$c = $j_begin;
					$variant = 0;
					$time_variant = 0.0;
					
					// Получаем время хотьбы пешком
					$time_variant += sqrt(pow(($temp_full_array[$j_begin]["latitude"] - $point_begin["lat"]) * $urm, 2) + pow(($temp_full_array[$j_begin]["longitude"] - $point_begin["lon"]) * getUrp($temp_full_array[$j_begin]["latitude"]), 2)) / 5;
					
					// Вычисление пути и его расстояния
					while($c != $j_end) {		
						array_push($temp_array, $temp_full_array[$c]);
						$c++;
						if($c == count($temp_full_array)) $c = 0;
						
						// Проверяем на ту же остановку
						if(($temp_full_array[$c]["point_name"] == $temp_full_array[$j_end]["point_name"]) && ($temp_full_array[$c]["orders"] != $temp_full_array[$j_end]["orders"])) {
							$variant = 100.0;
							break;
						}	
							
						// Получаем расстояние
						if(isset($array_range[$temp_array[count($temp_array) - 2]["id_points"]][$temp_array[count($temp_array) - 1]["id_points"]])) {
							$variant += $array_range[$temp_array[count($temp_array) - 2]["id_points"]][$temp_array[count($temp_array) - 1]["id_points"]];	
							/*if($gl_time < ($variant)) {
								$variant = 100.0;
								break;
							}	*/					
						}
						
					} 
					array_push($temp_array, $temp_full_array[$c]);
					if(isset($array_range[$temp_array[count($temp_array) - 2]["id_points"]][$temp_array[count($temp_array) - 1]["id_points"]])) {
						$variant += $array_range[$temp_array[count($temp_array) - 2]["id_points"]][$temp_array[count($temp_array) - 1]["id_points"]];						
					}
					$max_range += $variant;
					
					if($arr[$t - 1]["id_routs"] == 7) {
						$time_variant += ($variant / ($avgVelosity + 50));
					} else {
						$time_variant += ($variant / $avgVelosity);
					}
						
					// Получаем время хотьбы пешком
					$time_variant += (sqrt(pow(($temp_full_array[$j_end]["latitude"] - $point_end["lat"]) * $urm, 2) + pow(($temp_full_array[$j_end]["longitude"] - $point_end["lon"]) * getUrp($temp_full_array[$j_end]["latitude"]), 2))) / 5;
					$times += $time_variant;
					if($gl_time < $times) break; 
					
					$range_for_rout[$arr[$t - 1]["id_routs"]]["begin"] = $temp_full_array[$j_begin];
					$range_for_rout[$arr[$t - 1]["id_routs"]]["end"] = $temp_full_array[$j_end];
					$range_for_rout[$arr[$t - 1]["id_routs"]]["range"] = $variant;
					$range_for_rout[$arr[$t - 1]["id_routs"]]["time"] = $time_variant;	
				}
				if($gl_time > $times) {	
					$range_mass[$counts]["range"] = $max_range;
					$range_mass[$counts]["times"] = $times;
					$range_mass[$counts]["rout_range"] = $range_for_rout;					
					$static = $max_range;
					$gl_time = $times;
				}
			}
			array_pop($arr);
		}
	}
	// Функция получения среднего времени пути
	function avgTime($idRout) {
		global $link;
		$md5 = md5("avgtime".$idRout);
		if(file_exists("cache/".$md5)) {
			$rout = unserialize(file_get_contents("cache/".$md5));			
		} else {
			$result = mysqli_query($link, "SELECT *
				FROM routs
				WHERE id_routs = ".$idRout
			);
			$rout = mysqli_fetch_assoc($result);
			file_put_contents("cache/".$md5, serialize($rout));
		}
		return $rout["duration_plan"];
	}	
	// Функция получения имени маршрута
	function nameRout($idRout) {
		global $link;
		$md5 = md5("avgtime".$idRout);
		if(file_exists("cache/".$md5)) {
			$rout = unserialize(file_get_contents("cache/".$md5));			
		} else {
			$result = mysqli_query($link, "SELECT *
				FROM routs
				WHERE id_routs = ".$idRout
			);
			$rout = mysqli_fetch_assoc($result);
			file_put_contents("cache/".$md5, serialize($rout));
		}
		return $rout["name"];
	}	
	// Функция разбора дерева 2
	function transitTransport($roots, $fullPath, $time) {
		while($roots != NULL) {
			$elements = $roots->getAllPoint();
			if($elements[0]["BOARD"] == 0) {
				$time += $elements[0]["TIME"];
				$name = nameRout($elements[0]["FROM"]["id_routs"]);
				array_push($fullPath, array("wolk" => $elements[0]["FROM"], "name"=> $name));
			} elseif ($elements[0]["BOARD"] == -1){
				$time += $elements[0]["TIME"];
				$name = nameRout($elements[0]["FROM"]["id_routs"]);
				array_push($fullPath, array("traffic" => $elements[0]["FROM"], "name"=> $name));
			} else {
				$is_transport = 0;
				foreach($elements as $k => $v) {
					if($v["TIME"] > $time) {
						$time += $v["TIME"] - $time;
						$dop = "";
						if($v["TIME"] >= 1.0) {
							$dop = " ".(int)$v["TIME"]."час(а) ";
							$v["TIME"] -= (int)$v["TIME"];
						} 
						// перевести в минуты и в часы
						array_push($fullPath, array("transport" => "через".$dop." ".(int)(60 * $v["TIME"])." минут"));
						$is_transport = 1;
						break;	
					}
				}
				if($is_transport == 0) {
					$avg = avgTime($elements[0]["ROUT"]);
					
					$i = 0;
					$wait = 0.0;
					$circle = 1;
					while($wait < $time) {
						if($i = count($elements)) {
							$i = 0;
							$circle++;
						}
						$wait = $elements[$i]["TIME"] + ($avg * $circle);
						$i++;
					}
					$time += $wait - $time;
					$dop = "";
					if($wait >= 1.0) {
						$dop = " ".(int)$wait."час(а) ";
						$wait -= (int)$wait;
					} 
					array_push($fullPath, array("transport" => "через".$dop." ".(int)(60 * $wait)." минут"));
				}
			}
			$roots = $roots->getNextPoint();
		}
		$result["path"] = $fullPath;
		$result["time"] = $time;
		return $result;
	}
	// Функция определения времени ожидания метро
	function getWaitTime() {
		if((strftime("%w", time()) == 0) || (strftime("%w", time()) == 6)) {
			$data = 0.20;
		} else {
			$data = 0.116;
		}
		return $data;
	}	
	// Функция получения всех расстояний
	function getPointLength($pt1, $pt2) {
		global $link;
		
		$md5 = md5($pt1."".$pt2.""."ppLength");
	
		if(file_exists("cache/".$md5)) {
			$lgt = unserialize(file_get_contents("cache/".$md5));			
		} else {	
			$lgt = array();
			$result = mysqli_query($link, "
				SELECT Length FROM lengths WHERE
				point_2 = ".$pt2."
			");
			$lgt = mysqli_fetch_assoc($result);
			file_put_contents("cache/".$md5, serialize($lgt));
		}
		return $lgt;
	}
	// Функция получения расстояния части пути 
	function getDistationPoints($rout, $pt1, $pt2) {
		global $link;
		
		$md5 = md5($rout."".$pt1."".$pt2.""."dist");
	
		if(file_exists("cache/".$md5)) {
			$lgt = unserialize(file_get_contents("cache/".$md5));			
		} else {	
			$lgt = array();
			$result = mysqli_query($link, "
				SELECT SUM(Length) as len 
				FROM transport_station INNER JOIN Lengths 
				ON id_points = point_2 AND station_status > 0 
				AND id_routs = ".$rout." AND id_points > ".$pt1." 
				AND id_points <= ".$pt2."
			");
			$lgt = mysqli_fetch_assoc($result);
			file_put_contents("cache/".$md5, serialize($lgt));
		}
		return $lgt;
	}
?>