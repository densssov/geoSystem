<?php
	//$start = microtime(true);
	// Connection 
	session_start();
	$link = mysqli_connect('127.0.0.1', 'root', '', 'transport_system');
	mysqli_set_charset($link , 'utf8');
	
	include "const.php";
	include "class.php";
	include "functions.php";
	
	///////////////////////////////////////////////////////
	// Координаты для тестирования 
	// Sov kino
	$point_begin["lat"] = $_POST["latBeg"];//56.839691;
	$point_begin["lon"] = $_POST["lngBeg"]; //60.611432;
	// posadskaya
	$point_end["lat"] = $_POST["latEnd"]; //56.822674;
	$point_end["lon"] = $_POST["lngEnd"]; //60.571859;
	/////////////////////////////////////////////////////////////////
		
	// Определяем конечные маршруты
	$result = getResult($point_end['lat'], $point_end['lon'], $range);
	while($rout_end = mysqli_fetch_assoc($result)) {
		array_push($last_routs, $rout_end["id_routs"]);
	}
	// Определяем начальные маршруты
	$result = getResult($point_begin['lat'], $point_begin['lon'], $range);
	
	$i = 0;
	$metro = 0;
	while($rout_begin = mysqli_fetch_assoc($result)) {
		array_push($first_routs, $rout_begin["id_routs"]);
		$intersection[$i]["transfer"] = 0;
		$intersection[$i]["oldOrderId"] = -1;
		$intersection[$i]["curOrder"] = $rout_begin["id_routs"];
		// Существование станции метрополитена
		if($rout_begin["id_routs"] == 7) { 
			$metro = 1;
		}
		$i++;
	}
	// Дополнительное определяем для метро в радиусе 1 км
	if($metro == 0) {
		$h_metro = getH(getUrp($point_begin["lat"]), 1.0);
		$result = queryCircleMetro($point_begin['lat'], $h_metro['lat'], $point_begin['lon'], $h_metro['lon']);
		while($rout = mysqli_fetch_assoc($result)) {
			$intersection[$i]["transfer"] = 0;
			$intersection[$i]["oldOrderId"] = -1;
			$intersection[$i]["curOrder"] = $rout["id_routs"];
			$i++;
		}
	}

	// Строим матриу маршрутов
	$matrix_routs = createMatrix();

	// Переменные для ограничений
	$i = 0;
	$transfer = 10;
	$is_break = 0;
	// Поиск маршрутов
	while(($intersection[$i]["transfer"] <= $transfer) && (isset($intersection[$i]))) {
		if(array_search($intersection[$i]["curOrder"], $last_routs) !== false) {
			if($is_break == 0) {
				$transfer = $intersection[$i]["transfer"] + 1;
				$is_break = 1;
			} 
			$sel_order = $intersection[$i]["oldOrderId"];
			$routs_all[$current_rout] = array();
			
			array_push($routs_all[$current_rout], $intersection[$i]["curOrder"]);
			while($sel_order != -1) {
				array_push($routs_all[$current_rout], $intersection[$sel_order]["curOrder"]);
				$sel_order = $intersection[$sel_order]["oldOrderId"];	
			} 
			$current_rout++;
		} else {		

			foreach($matrix_routs[$intersection[$i]["curOrder"]] as $key => $value) {	
				if(($value != 0) && ($key != $intersection[$i]["oldOrderId"])) {
					if(array_search($key, $first_routs) === false) {		
						array_push($intersection, array("transfer" => $intersection[$i]["transfer"] + 1, "oldOrderId" => $i, "curOrder" => $key));
					}
				}
			}		
		}
		
		$i++;	
	}
	
	// Расчет длины маршрутов
	$name_points = array();
	$index_point = 0;
	// Получение погрешностей радиусов начала и конца
	$h_begin = getH(getUrp($point_begin['lat']), $range);
	$h_end = getH(getUrp($point_end['lat']), $range);
	
	foreach($routs_all as $k1 => $v1) {
		$temp_arr = array();
		$name_points[$index_point] = array();
		
		// Начальные остановки
		if($v1[count($v1) - 1] == 7) {
			$temp_arr = getStationBE($point_begin['lat'], $h_metro['lat'], $point_begin['lon'], $h_metro['lon'], $v1[count($v1) - 1]); 
		} else {
			$temp_arr = getStationBE($point_begin['lat'], $h_begin['lat'], $point_begin['lon'], $h_begin['lon'], $v1[count($v1) - 1]); 
		}
		
		// Записываем начальные остановки
		$root = new tree($temp_arr);
		// Ищем пересечения маршрутов
		if(count($v1) > 1) {	
			for($i = count($v1) - 1; $i > 0; $i--) {
				$temp_arr = array();
				$temp = $root;
				
				while($temp->getNextPoint() != NULL) {
					$temp = $temp->getNextPoint();
				}
				$temp_arr = getIntersept($v1[$i - 1], $v1[$i]);
				$temp->setNextPoint(new tree($temp_arr[0]));
				$temp = $temp->getNextPoint();
				$temp->setNextPoint(new tree($temp_arr[1]));
			}	
		}
		// Конечные остановки
		$temp_arr = getStationBE($point_end['lat'], $h_end['lat'], $point_end['lon'], $h_end['lon'], $v1[0]); 
		if(count($temp_arr) == 0) {
			$urp = getUrp($point_end["lat"]);
			$h_metro = getH($urp, $range);
			$temp_arr = getStationBE($point_end['lat'], $h_metro['lat'], $point_end['lon'], $h_metro['lon'], $v1[0]); 
		}

		$temp = $root;
		while($temp->getNextPoint() != NULL) {
			$temp = $temp->getNextPoint();
		}
		// Записали конечные остановки
		$temp->setNextPoint(new tree($temp_arr));
		// Разбор недодеревца	
		$static = 999.0;
		$gl_time = 999.0;
		viewTree($root, array());
		$counts++;
	}

	$avgTime = array();
	$begining = 0;
	// Массив хранящий те же маршруты
	$mass_transport = array();

	foreach($range_mass as $k => $v) {
		$root = 0;
		$begining = 0;
		$arr_prev = 0;
		foreach($v["rout_range"] as $rk => $rv) {		
			// Определяем и записываем время пути до остановки
			if($begining == 0) {
				$urp = getUrp($point_begin["lat"]);
				$time_range = sqrt(pow(($rv["begin"]["latitude"] - $point_begin["lat"]) * $urm, 2) + pow(($rv["begin"]["longitude"] - $point_begin["lon"]) * $urp, 2));
				$root = new tree(array(array("BOARD" => 0, "TIME" => ($time_range / 5), "FROM" => $rv["begin"])));
				$begining = 1;
			} 
			// Записываем места нахождения т.с.
			$temp = $root;
			while($temp->getNextPoint() != NULL) {
				$temp = $temp->getNextPoint();
			}
			
			if(!isset($mass_transport[$rk])) {
				$mass_transport[$rk] = getTransportTime($rk, $link);				
			}
			$transports = $mass_transport[$rk];
			
			// Получение т.с.
			if($arr_prev != 0) {
				$urp = getUrp($arr_prev["end"]["latitude"]);
				$times = sqrt(pow(($rv["begin"]["latitude"] - $arr_prev["end"]["latitude"]) * $urm, 2) + pow(($rv["begin"]["longitude"] - $arr_prev["end"]["longitude"]) * $urp, 2));
				$temp->setNextPoint(new tree(array(array("BOARD1" => 0, "TIME" => $times / $avgBoots, "FROM" => $rv["begin"]))));
				$temp = $temp->getNextPoint();				
			} 
			$arr_prev = $rv;
			// Массив для сортировок
			$arr = array();
			$speeds = array();
			$array_points = array();
			// Получение времени их прибытие до начальной точки и их бортовые индексы
			if(count($transports) > 0) {
				foreach($transports as $kt => $vt) {
					
					$fullrout = getFullRout($rk, $link);
					
					$rout = getRout($rk, $link);
					$urp = getUrp($vt["LAT"]);
					
					if($vt["ON_ROUTE"] == 1) {
						$text_query = "(SELECT DISTINCT p.point_name, t.id_routs, p.latitude, t.station_status,
							p.longitude, t.orders, p.id_points, t.direction,
							SQRT(POW((".$vt["LAT"]." - p.latitude) * ".$urm.", 2) + POW((".$vt["LON"]." - p.longitude) * ".$urp.", 2)) as result
						FROM points p, transport_station t
						WHERE p.id_points = t.id_points 
						AND t.id_routs = ".$rk;
						
						$sql_query = $text_query;
						
						// Пошагово определяем точки
						if(($vt["COURSE"] > 270) && ($vt["COURSE"] < 360)) {
							//1
							$course = 1;
							// Главная четверть
							$sql_query.= " AND ((p.latitude >= ".($vt["LAT"])." AND p.longitude >= ".($vt["LON"])."))";
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
												(p.latitude >= ".($vt["LAT"])." AND p.longitude <= ".($vt["LON"]).") OR
												(p.latitude <= ".($vt["LAT"])." AND p.longitude >= ".($vt["LON"]).")
											)";
								$sql = $sql_query;	
								$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
								$sql .= " UNION ";		
								$sql .= $sql_query;
								$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";	
								$result = mysqli_query($link, $sql);						
							}				
						} else if(($vt["COURSE"] > 180) && ($vt["COURSE"] < 270)) {
							//2
							$course = 2;
							// Главная четверть
							$sql_query.= " AND ((p.latitude >= ".($vt["LAT"])." AND p.longitude <= ".($vt["LON"])."))";
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
												(p.latitude >= ".($vt["LAT"])." AND p.longitude >= ".($vt["LON"]).") OR
												(p.latitude <= ".($vt["LAT"])." AND p.longitude <= ".($vt["LON"]).")
											)";
								$sql = $sql_query;	
								$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
								$sql .= " UNION ";		
								$sql .= $sql_query;
								$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";	
								$result = mysqli_query($link, $sql);						
							}			
						} else if(($vt["COURSE"] > 90) && ($vt["COURSE"] < 180)) {
							//3
							$course = 3;
							// Главная четверть
							$sql_query.= " AND ((p.latitude <= ".($vt["LAT"])." AND p.longitude <= ".($vt["LON"])."))";
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
												(p.latitude >= ".($vt["LAT"])." AND p.longitude <= ".($vt["LON"]).") OR
												(p.latitude <= ".($vt["LAT"])." AND p.longitude >= ".($vt["LON"]).")
											)";
								$sql = $sql_query;	
								$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
								$sql .= " UNION ";		
								$sql .= $sql_query;
								$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";	
								$result = mysqli_query($link, $sql);						
							}				
						} else if(($vt["COURSE"] > 0) && ($vt["COURSE"] < 90)) {
							//4
							$course = 4;
							// Главная четверть
							$sql_query.= " AND ((p.latitude <= ".($vt["LAT"])." AND p.longitude >= ".($vt["LON"])."))";
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
												(p.latitude >= ".($vt["LAT"])." AND p.longitude >= ".($vt["LON"]).") OR
												(p.latitude <= ".($vt["LAT"])." AND p.longitude <= ".($vt["LON"]).")
											)";
								$sql = $sql_query;	
								$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
								$sql .= " UNION ";		
								$sql .= $sql_query;
								$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";	
								$result = mysqli_query($link, $sql);						
							}				
						} else if($vt["COURSE"] == 270) {
							//1-2
							$course = 12;
							$sql_query .= " AND p.latitude >= ".($vt["LAT"]);
							$sql = $sql_query;	
							$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
							$sql .= " UNION ";		
							$sql .= $sql_query;
							$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";
							$result = mysqli_query($link, $sql);
						} else if($vt["COURSE"]== 180) {
							//2-3
							$course = 23;
							$sql_query .= " AND p.longitude <= ".($vt["LON"]);
							$sql = $sql_query;	
							$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
							$sql .= " UNION ";		
							$sql .= $sql_query;
							$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";
							$result = mysqli_query($link, $sql);
						} else if($vt["COURSE"]== 90) {
							//3-4
							$course = 34;
							$sql_query .= " AND p.latitude <= ".($vt["LAT"]);
							$sql = $sql_query;	
							$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
							$sql .= " UNION ";		
							$sql .= $sql_query;
							$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";
							$result = mysqli_query($link, $sql);
						} else if(($vt["COURSE"]== 0) || ($vt["COURSE"]== 360)) {
							//1-4
							$course = 14;
							$sql_query .= " AND p.longitude >= ".($vt["LON"]);
							$sql = $sql_query;	
							$sql .= " AND direction = 0 ORDER BY result ASC LIMIT 1)";
							$sql .= " UNION ";		
							$sql .= $sql_query;
							$sql .= " AND direction = 1 ORDER BY result ASC LIMIT 1)";
							$result = mysqli_query($link, $sql);
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

						$array_range = getRangePoint($range, $rk);
						
						$range_transport = 0.0;
						// Обработка расстояния
						$pplgt = array();
						if($point["station_status"] != 0) {
							$pplgt = getDistationPoints($point["id_routs"], $point["id_points"], $rv["begin"]["id_points"]);
							$range_transport = $pplgt["Length"];
						} else {
							
							$pplgt = getPointLength($rv["begin"]["id_points"], $point["id_points"]);
							
							$range_transport = $pplgt["Length"];
							
							$pplgt = getDistationPoints($point["id_routs"], $point["id_points"], $rv["begin"]["id_points"]);
							$range_transport += $pplgt["len"];

						}		

					}
					$vel = ($avgVelosity + $vt["VELOCITY"]) / 2;
					//$vel = $vt["VELOCITY"];
				
					array_push($array_points, array("BOARD" => $vt["BOARD_NUM"], "TIME" => ($range_transport / $vel), "ROUT" => $rk));		
					array_push($arr, ($range_transport / $vel));
					array_push($speeds, array("vel" => $vt["VELOCITY"],"lat" => $vt["LAT"],"lon" => $vt["LON"]));
				
				}
			} else{
				// Функция определения времени ожидания для метрополитена
				if($rk == 7) {
					$t_metro = getWaitTime();
					array_push($array_points, array("BOARD" => 1, "TIME" => $t_metro, "ROUT" => $rk));		
					array_push($arr, $t_metro);
					array_push($speeds, array("vel" => 50));
				}
				
				array_push($array_points, array("BOARD" => 1, "TIME" => "7.0", "ROUT" => $rk));		
				array_push($arr, "7.0");
				array_push($speeds, array("vel" => 1));
				
			}
			array_multisort($arr, SORT_ASC, $array_points);
			
			if(count($transports) > 0) {
				$up;
				$down;
				$left;
				$right;
				if($rv["begin"]["latitude"] >= $rv["end"]["latitude"]) {
					$up = $rv["begin"]["latitude"];
					$down = $rv["end"]["latitude"];
				} else {
					$up = $rv["end"]["latitude"];
					$down = $rv["begin"]["latitude"];
				}
				
				if($rv["begin"]["longitude"] >= $rv["end"]["longitude"]) {
					$left = $rv["end"]["longitude"];
					$right = $rv["begin"]["longitude"];
				} else {
					$left = $rv["begin"]["longitude"];
					$right = $rv["end"]["longitude"];
				}
				
				$avgspeed = 0;
				$ispeed = 1;
				foreach($speeds as $ksped => $vsped) {
					if((($up >= $vsped["lat"]) && ($down <= $vsped["lat"])) && (($left <= $vsped["lon"]) && ($right >= $vsped["lon"]))) {
						$avgspeed += $vsped["vel"];
						$ispeed += 2;
					}
					
				}
				$avgspeed += 1; //$avgVelosity;
				$avgspeed /= ($ispeed + 1);
				
				if($range_transport / $avgspeed > 5.0) {
					$avgspeed += 10;
				}
				
				$temp->setNextPoint(new tree($array_points));
				$temp = $temp->getNextPoint();
				$temp->setNextPoint(new tree(array(array("BOARD" => -1, "TIME" => $range_transport / $avgspeed, "FROM" => $rv["end"]))));
				
			} else {
				$temp->setNextPoint(new tree($array_points));
				$temp = $temp->getNextPoint();
				$temp->setNextPoint(new tree(array(array("BOARD" => -1, "TIME" => $rv["time"], "FROM" => $rv["end"]))));
			}
		}
		
		$urp = getUrp($point_end["lat"]);
		$times = sqrt(pow(($arr_prev["end"]["latitude"] - $point_end["lat"]) * $urm, 2) + pow(($arr_prev["end"]["longitude"] - $point_end["lon"]) * $urp, 2));
		$temp = $temp->getNextPoint();
		$temp->setNextPoint(new tree(array(array("BOARD" => 0, "TIME" => $times / $avgBoots, "FROM" => "END"))));
		
		//Разбор дерева
		$time_transport = transitTransport($root, array(), 0.0);
		
		$range_mass[$k]["realpath"] = $time_transport["path"];
		$range_mass[$k]["avgtime"] = $time_transport["time"];
		
		array_push($avgTime, $time_transport["time"]);
		
	}
	// Сортировка и отправка 3-х вариантов
	array_multisort($avgTime, SORT_ASC, $range_mass);
	
	if(count($range_mass) < 3) {
		$comm = count($range_mass);	
	} else {
		$comm = 3;
	}

	for($i = 0; $i < $comm; $i++) {
		// Массив для полного пути
		$arrFull = array();
		foreach($range_mass[$i]["rout_range"] as $k => $v) {
			$temp = getFullRout($k, $link);
			$begin = searchIndex($temp, $v["begin"]["orders"]);
			$end = searchIndex($temp, $v["end"]["orders"]);
			
			$c = $begin;
			while($c != $end + 1) {
				if($c == count($temp)) $c = 0;
				array_push($arrFull, array("lat" => (double)$temp[$c]["latitude"], "lng" => (double)$temp[$c]["longitude"]));
				$c++;
			}

		}
		
		$range_mass[$i]["fullPath"] = $arrFull;
	}

	echo json_encode($range_mass);
	
	//printf("</br>Время выполнения файла %.4F</br>", microtime(true) - $start);
?>