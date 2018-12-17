var map;
var poliLine = [];
//                     red,     green,      blue,     black
var colorLine = ['#FF0000', '#008000', '#0000FF', '#0000000'];
//				  lightred, lightgreen, lightblue,     gray
var colorFill = ['#FF6347', '#90EE90', '#ADD8E6', '#808080'];
// Transport
var allTransport = [];
// tem transport
var temporary = [];
// number routs
var numbers = [];
// types routs
var types = [];
// for makers
var labels = ['../img/a.png', '../img/b.png'];
var labelIndex = 0;
// flags
var flag = 0;
//flag_vipad 
var flag_vipad = '';
// begin and end point
var arrPoint = [];
// markers parking
var markPark = [];
// makers routs
var markRouts = [];
// arr marhrut one
var constract;
// варианты маршрутов
var marshruts;
// Элемент для проверки
var variant = [];
// arr info
var inf = [];
function send(keyT) {
	//console.log(keyT);
	$.ajax({
		type: "POST",
		data: {transportRout1: numbers[keyT],
			   transportType1: types[keyT],
			   id: keyT
		},
		url: "../www/path.php",
		//async: false,
		success: function(data){
			var obj = JSON.parse(data);
			var color, fill, i;
			
			transportDel(keyT);
			if((keyT == 1) || (keyT == 2)) {
				color = colorLine[0];
				fill = colorFill[0];
			} else if((keyT == 3) || (keyT == 4)) {
				color = colorLine[1];
				fill = colorFill[1];
			} else if((keyT == 5) || (keyT == 6)) {
				color = colorLine[2];
				fill = colorFill[2];
			} else if(keyT == 7) {
				color = colorLine[3];
				fill = colorFill[3];
			}
			var i = 0;
			var arrVariant = [];
			for (var key in obj["marsh"]) {
				var cours = obj["marsh"][key]["COURSE"] + 90;
				if (cours > 360) cours -= 360;
					
				var image = {
					path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,  
					anchor: new google.maps.Point(1, 1),
					fillColor: fill,
					strokeColor: color,
					fillOpacity: 0.8,
					scale: 4,
					rotation: cours
				};
				
				arrVariant[i] = new google.maps.Marker({
					position: {lat: obj["marsh"][key]["LAT"], lng: obj["marsh"][key]["LON"]},
					map: map,
					icon: image,
					title: 'Бортовой номер '+obj["marsh"][key]["BOARD_NUM"]
				});
				i++;
			}
			allTransport[keyT] = arrVariant;
			i = 0;			
			console.log(obj["point"]);
			for (var i = 0; i < obj["point"].length; i++) {
				if(typeof(obj["point"][i]["time"]) != "undefined") {
					t = 'Время ожидания '+parseInt(parseFloat(obj["point"][i]["time"]) * 60, 10)+' минут';
				} else {
					t = 'Транспорта нет';
				}
				var contentString = '<div id="content"><div id="siteNotice"></div><div id="bodyContent"><p>'+t+'</p></div></div>';
				
				inf[keyT][i].setContent(contentString);
				
			}
			if(variant.length > 0) send(variant.shift());
		}
	});
} 

var timerId = setInterval(function() {
	if(allTransport.length > 0) {
		console.log('запрос');
		console.log(allTransport);
		for (var keyT in allTransport) {
			variant.push(keyT);
		}
		send(variant.shift());
		//for (var keyT in allTransport) {
			/*$.ajax({
				type: "POST",
				data: {transportRout: numbers[keyT],
					   transportType: types[keyT]
				},
				url: "../www/path.php",
				//async: false,
				success: function(data){
					var obj = JSON.parse(data);
					var color, fill, i;
					transportDel(keyT);
					if((keyT == 1) || (keyT == 2)) {
						color = colorLine[0];
						fill = colorFill[0];
					} else if((keyT == 3) || (keyT == 4)) {
						color = colorLine[1];
						fill = colorFill[1];
					} else if((keyT == 5) || (keyT == 6)) {
						color = colorLine[2];
						fill = colorFill[2];
					} else if(keyT == 7) {
						color = colorLine[3];
						fill = colorFill[3];
					}
					var i = 0;
					var arrVariant = [];
					for (var key in obj) {
						var cours = obj[key]["COURSE"] + 90;
						if (cours > 360) cours -= 360;
							
						var image = {
							path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,  
							anchor: new google.maps.Point(1, 1),
							fillColor: fill,
							strokeColor: color,
							fillOpacity: 0.8,
							scale: 4,
							rotation: cours
						};
						
						arrVariant[i] = new google.maps.Marker({
							position: {lat: obj[key]["LAT"], lng: obj[key]["LON"]},
							map: map,
							icon: image,
							title: 'Бортовой номер '+obj[key]["BOARD_NUM"]
						});
						i++;
					}
					allTransport[keyT] = arrVariant;
				}
			});*/
		//}
	} else {
		console.log('не запрос');
	}
}, 15000);

function initMap() {
	var myPosition = {lat: 56.8359209, lng: 60.6114863};
	map = new google.maps.Map(document.getElementById('map'), {
		center: myPosition,
		zoom: 12
	});
	
	google.maps.event.addListener(map, 'click', function(event) {
		console.log(labelIndex);
		console.log(flag);
		if(labelIndex < 2 && flag == 1) addMarker(event.latLng, map);
		
	});
	
}

function addMarker(location, map) {
	
	var image = {
		url: labels[labelIndex],
		scaledSize: new google.maps.Size(32, 32)
	};
	
	arrPoint[labelIndex] = new google.maps.Marker({
		position: location,
		icon: image,
		draggable: true,
		map: map
	});
	labelIndex++;
	arrPoint[labelIndex - 1].addListener('click', toggleBounce);
}

function toggleBounce() {
	if (arrPoint[labelIndex - 1].getAnimation() !== null) {
		arrPoint[labelIndex - 1].setAnimation(null);
	} else {
		arrPoint[labelIndex - 1].setAnimation(google.maps.Animation.BOUNCE);
	}
}

function transportView(id_routs, num_rout,type_routs) {
	$.ajax({
		type: "POST",
		data: {transportRout: num_rout, 
			   transportType: type_routs
		},
		url: "../www/path.php",
		success: function(data){
			var obj = JSON.parse(data);
			var color, fill, i;
			if((id_routs == 1) || (id_routs == 2)) {
				
				color = colorLine[0];
				fill = colorFill[0];
			} else if((id_routs == 3) || (id_routs == 4)) {
				
				color = colorLine[1];
				fill = colorFill[1];
			} else if((id_routs == 5) || (id_routs == 6)) {
				
				color = colorLine[2];
				fill = colorFill[2];
			} else if(id_routs == 7) {
				
				color = colorLine[3];
				fill = colorFill[3];
			}
			var i = 0;
			var arrVariant = [];
			for (var key in obj) {
				var cours = obj[key]["COURSE"] + 90;
				if (cours > 360) cours -= 360;
					
				var image = {
					path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,  
					anchor: new google.maps.Point(1, 1),
					fillColor: fill,
					strokeColor: color,
					fillOpacity: 0.8,
					scale: 4,
					rotation: cours
				};
				
				arrVariant[i] = new google.maps.Marker({
					position: {lat: obj[key]["LAT"], lng: obj[key]["LON"]},
					map: map,
					icon: image,
					title: 'Бортовой номер '+obj[key]["BOARD_NUM"]
				});
				i++;
			}
			allTransport[id_routs] = arrVariant;
			console.log(allTransport);
		}
	});
}

function transportDel(id_routs) {
	if(allTransport[id_routs] != null) {
		for (var i = 0; i < allTransport[id_routs].length; i++) {
			allTransport[id_routs][i].setMap(null);
		}
		delete allTransport[id_routs];
	}
}

function markerDel(id_routs) {
	if(markRouts[id_routs] != null) {
		for (var i = 0; i < markRouts[id_routs].length; i++) {
			markRouts[id_routs][i].setMap(null);
		}
		delete markRouts[id_routs];
	}
}

function routs(id_routs, num_routs, type_routs) {
	
	if($("#"+id_routs).prop("checked")) {
		console.log(1);
		numbers[id_routs] = num_routs;
		types[id_routs] = type_routs;
		$.ajax({
			type: "POST",
			data: {rout: id_routs},
			url: "../www/path.php",
			success: function(data){
				console.log(data);
				var obj = JSON.parse(data);
				
				var color, img;
				if((id_routs == 1) || (id_routs == 2)) {
					img = '../img/bus.png';
					color = colorLine[0];
				} else if((id_routs == 3) || (id_routs == 4)) {
					img = '../img/bus.png';
					color = colorLine[1];
				} else if((id_routs == 5) || (id_routs == 6)) {
					img = '../img/train.png';
					color = colorLine[2];
				} else if(id_routs == 7) {
					img = '../img/metro.png';
					color = colorLine[3];
				}
				console.log(obj);
				poliLine[id_routs] = new google.maps.Polyline({
					path: obj["marsh"],
					geodesic: true,
					strokeColor: color,
					strokeOpacity: 1.0,
					strokeWeight: 2
				});
				poliLine[id_routs].setMap(map);

				transportView(id_routs, num_routs, type_routs);
				var image = {
					url: img,
					scaledSize: new google.maps.Size(25, 25)
				};
				markRouts[id_routs] = [];
				inf[id_routs] = [];
				console.log(obj["point"].length);
				for(var i = 0; i < obj["point"].length; i++) {
					var t = 0;
					if(typeof(obj["point"][i]["time"]) != "undefined") {
						t = 'Время ожидания '+parseInt(parseFloat(obj["point"][i]["time"]) * 60, 10)+' минут';
					} else {
						t = 'Транспорта нет';
					}
					var contentString = '<div id="content"><div id="siteNotice"></div><div id="bodyContent"><p>'+t+'</p></div></div>';
					
					markRouts[id_routs].push(new google.maps.Marker({
						position: {lat: parseFloat(obj["point"][i]["latitude"]), lng: parseFloat(obj["point"][i]["longitude"])},
						map: map,
						icon: image,
						title: obj["point"][i]["point_name"],
						index: i
					}));
					
					inf[id_routs][i] = new google.maps.InfoWindow({
						content: contentString
					});
					
					(markRouts[id_routs])[i].addListener('click', function() {
						inf[id_routs][this.index].open(map, markRouts[id_routs][this.index]);
					});
					
				}
				
			}
		});	
		
	} else {
		poliLine[id_routs].setMap(null);
		transportDel(id_routs);
		markerDel(id_routs);
	}
}

function start() {
	console.log(arrPoint[0].getPosition().lat());
	console.log(arrPoint[0].getPosition().lng());
	console.log(arrPoint[1].getPosition().lat());
	console.log(arrPoint[1].getPosition().lng());
	if(arrPoint.length == 2) {
		$.ajax({
			type: "POST",
			data: {
				latBeg: arrPoint[0].getPosition().lat(),
				lngBeg: arrPoint[0].getPosition().lng(),
				latEnd: arrPoint[1].getPosition().lat(),
				lngEnd: arrPoint[1].getPosition().lng()
			},
			url: "../www/newAlg.php",
			success: function(data){
				//console.log(data);
				var obj = JSON.parse(data);
				var begins = {lat:arrPoint[0].getPosition().lat(), lng:arrPoint[0].getPosition().lng()};
				var endins = {lat:arrPoint[1].getPosition().lat(), lng:arrPoint[1].getPosition().lng()};
				
				obj[0]["fullPath"].unshift(begins);
				obj[0]["fullPath"].push(endins);
				
				/////////////////
				console.log(obj);
				/////////////////
				constract = new google.maps.Polyline({
					path: obj[0]["fullPath"],
					geodesic: true,
					strokeColor: "#FF8C00",
					strokeOpacity: 1.0,
					strokeWeight: 2
				});
				constract.setMap(map);
				var path = obj[0]["realpath"];

				var i = 0;
				var text;
				var point;
				var img = 1;
				while(i < path.length - 1) {
					if(path[i]["wolk"] !== undefined) {
						if(path[i + 1]["transport"] !== undefined) {
							point = path[i]["wolk"];
							i++;
						}
					} else if(path[i]["traffic"] !== undefined) {
						point = path[i]["traffic"];
						img++;
					}
					
					var image = {
						url: '../img/'+img+'.png',
						scaledSize: new google.maps.Size(30, 30)
					};
					
					markPark.push(new google.maps.Marker({
						position: {lat: Number(point["latitude"]), lng: Number(point["longitude"])},
						map: map,
						icon: image
					}));
					i++;
				}
				
				var count;
				if(obj.length > 3) {
					count = 3;
				} else {
					count = obj.length;
				}
				
				var idDiv = document.getElementById("vipad-3");
				idDiv.innerHTML = "";
				var common = "";
				// Отображение вариантов
				for(var j = 0; j < count; j++) {
					if(j != 0) {
						obj[j]["fullPath"].unshift(begins);
						obj[j]["fullPath"].push(endins);
					}
					var house = ""; //id='path-"+j+"'
					text = "<div class='texts' style='text-align:left; margin-left:5px;'>";
					if(Number((obj[j]["avgtime"])) >= 1.0) house = parseInt((obj[j]["avgtime"]), 10)+" ч. "; 
					text += "<p class='viewInf' onclick="+'"'+"marhToggle('"+j+"')"+'"'+";><span class='blues' >Время в пути</br></span><span style='font-size:16pt;'>"+house+parseInt(((obj[j]["avgtime"] - parseInt(obj[j]["avgtime"])) * 60), 10)+" мин.";
					text += "</span></p></div>";
					//common += text;
					
					text += "<div id='path-"+j+"'>";
					text += "<div><p style='text-align: center; color:rgba(255,255,255,0.3);'>маршрут</p></div>";
					text += "<table style='font-size:14pt;'>";
					path = obj[j]["realpath"];
					var rout = 1;
					i = 0;
					console.log(path);
					while(i < path.length - 1) {
						if(path[i]["wolk"] !== undefined) {
							if(path[i + 1]["transport"] !== undefined) {
								text += "<tr><td style='border-right:1px solid rgba(255,255,255,0.3);'><img width='30px' height='30px' src='../img/"+rout+".png' /></td>";
								text += "<td><span class='blues'>"+path[i]["name"]+"</span></br>"+"<span class='texts'>ост. "+path[i]["wolk"]["point_name"]+"</br>"+path[i + 1]["transport"]+"</br>";
								i++;
							}
						} else if(path[i]["traffic"] !== undefined) {
							text += "до ост. "+path[i]["traffic"]["point_name"]+"</span></td></tr>";
							img++;
							rout++;
						}
						i++;
					}
					text += "</table></div>";
					common += text;
				}

				idDiv.innerHTML += common;
				marshruts = obj;
				$("div[id*='path-']").hide();
				marhToggle("0");
				//////////////////////////////////////////////////////////////////////
				var element = $("#vipad-3"),
					blocks = $("div[id*='vipad-']");
				if (element.css("display") != "none") {
					element.animate({ height: 'hide' }, 200);
				} else {
					var visibleBlocks = $("div[id*='vipad-']:visible");
					if (visibleBlocks.length < 1) {
						element.animate({ height: 'show' }, 400);
					} else {
						$(visibleBlocks).animate({ height: 'hide' }, 200, function() {
							element.animate({ height: 'show' }, 400);
						});            
					}  
				}
				flag_vipad = "#vipad-3";
				//////////////////////////////////////////////////////////////////////
				 
			}
		});	
	} else {
		console.log("Точки не выбраны");
	}
}

function otherPath(numRout) {
	constract.setMap(null);
	for(var i = 0; i < markPark.length; i++) {
		markPark[i].setMap(null);
	}
	constract = new google.maps.Polyline({
		path: marshruts[numRout]["fullPath"],
		geodesic: true,
		strokeColor: "#FF8C00",
		strokeOpacity: 1.0,
		strokeWeight: 2
	});
	constract.setMap(map);
	var path = marshruts[numRout]["realpath"];
	var i = 0;
	var text;
	var point;
	var img = 1;
	while(i < path.length - 1) {
		if(path[i]["wolk"] !== undefined) {
			if(path[i + 1]["transport"] !== undefined) {
				point = path[i]["wolk"];
				i++;
			}
		} else if(path[i]["traffic"] !== undefined) {
			point = path[i]["traffic"];
			img++;
		}
		var image = {
			url: '../img/'+img+'.png',
			scaledSize: new google.maps.Size(30, 30)
		};
		markPark.push(new google.maps.Marker({
			position: {lat: Number(point["latitude"]), lng: Number(point["longitude"])},
			map: map,
			icon: image
		}));
		i++;
	}
}

function clears() {
	constract.setMap(null);
	for(var i = 0; i < markPark.length; i++) {
		markPark[i].setMap(null);
	}
	arrPoint[0].setMap(null);
	arrPoint[1].setMap(null);

	labelIndex = 0;
	arrPoint = [];
	/*for(var i = 0; i < marshruts.length; i++) {
		var t = document.getElementById("infoRout");
		t.children[0].remove();
	}*/
}

function test() {
	console.log("Button down");
}
