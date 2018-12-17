<?php 
	// Class queue
	class tree {
		private $point_routs;
		private $nextPoint;
		
		function __construct($arrs) {
			$this->point_routs = $arrs;
			$this->nextPoint = NULL;
		}
		
		public function getOnePoint($i) {
			return $this->point_routs[$i];
		}
		
		public function getAllPoint() {
			return $this->point_routs;
		}
		
		public function getCountPoint() {
			return count($this->point_routs);
		}
		
		public function setPointRout($element) {
			array_push($this->point_routs, $element);
		}
		
		public function setNextPoint($nxt) {
			$this->nextPoint = $nxt;
		}
		
		public function getNextPoint() {
			return $this->nextPoint;
		}
	}
?>