<?php
class products {
	private $conn;
	public function __construct() {
		include_once dirname ( __FILE__ ) . "/database.php";
		$db = new database ();
		$this->conn = $db->db_connect ();
	}
	/*
	 * Function for add product @return bool if user is add
	 */
	public function addProduct($name, $amount, $value, $id = NULL) {
		if (! $this->hasEmpty ( array (
				$name,
				$amount,
				$value 
		) )) {
			echo $id;
			$value = $this->value_formatter("us", $value);
			if ($id == null || $id = "") {
				$id = $this->getLastProductID() + 1;
				
				if ($id == null)
					$id = 0;
			}
			try {
				$db = $this->conn->prepare ( "INSERT INTO products(id,name,amount,value) VALUES(:id,:name,:amount,:value)" );
				$db->bindParam ( ":id", $id );
				$db->bindParam ( ":name", $name );
				$db->bindParam ( ":amount", $amount );
				$db->bindParam ( ":value", $value );
				if ($db->execute ()) {
					$db = null;
					return true;
				}
			} catch ( PDOException $e ) {
				echo $e->getMessage ();
			}
			$db = null;
			return false;
		}
	}
	
	/*
	 * Function to Edit a product @return Array of product data
	 */
	public function editProduct($id, $name, $amount, $value) {
		try {
			$db = $this->conn->prepare ( "UPDATE users SET id=:id, name=:name, amount=:amount, value=:value where id=:id" );
			$db->bindParam ( ":id", $id );
			$db->bindParam ( ":name", $name );
			$db->bindParam ( ":amount", $amount );
			$db->bindParam ( ":value", $value );
			if ($db->execute ()) {
				$db = null;
				return true;
			}
		} catch ( PDOException $e ) {
			echo $e->getMessage ();
		}
		$db = null;
		return false;
	}
	
	/*
	 * Function for get Product data. @return Array with product data
	 */
	public function getProduct($id) {
		$result = null;
		$db = $this->conn->prepare ( "SELECT id, name, amount, value FROM users WHERE keyP=:id" );
		$db->bindParam ( ":id", $id );
		try {
			if ($db->execute ()) {
				$result = $db->fetch ( PDO::FETCH_OBJ );
			}
		} catch ( PDOException $e ) {
			echo $e->getMessage ();
		}
		$result = $this->value_formatter("br", $result->value);
		$db = null;
		return $result;
	}
	/*
	 * Function for get All products in Database @return Array with all products data
	 */
	public function getAllProducts() {
		$db = $this->conn->prepare ( "SELECT id, name, amount, value FROM products WHERE 1 ORDER BY keyP DESC" );
		try {
			if ($db->execute ()) {
				$result = $db->fetchAll ( PDO::FETCH_OBJ );
			}
		} catch ( PDOException $e ) {
			echo $e->getMessage ();
		}
		foreach ($result as $r){
			$r->value = $this->value_formatter("br", $r->value);
		}
		$db = null;
		return $result;
	}
	/*
	 * Function to remove a product from database
	 */
	public function removeProduct($id) {
		$db = $this->conn->prepare ( "DELETE FROM products WHERE keyP=:id" );
		$db->bindParam ( ":id", $id );
		try {
			if ($db->execute ())
				return true;
		} catch ( PDOException $e ) {
			echo $e->getMessage ();
		}
		return false;
	}
	public function totalValue() {
		$totalValue = 0;
		try {
			$db = $this->conn->prepare ( "SELECT value, amount FROM products WHERE 1" );
			if ($db->execute ()) {
				foreach ( $db->fetchAll ( PDO::FETCH_OBJ ) as $r ) {
					$totalValue += $r->value*$r->amount;
				}
			}
		} catch ( PDOException $e ) {
			echo $e->getMessage ();
		}
		$db = null;
		return $totalValue;
	}
	public function totalAmount() {
		$totalAmount = 0;
		try {
			$db = $this->conn->prepare ( "SELECT amount FROM products WHERE 1" );
			if ($db->execute ()) {
				foreach ($db->fetchAll ( PDO::FETCH_OBJ ) as $r ) {
					$totalAmount += $r->amount;
				}
			}
		} catch ( PDOException $e ) {
			echo $e->getMessage ();
		}
		$db = null;
		return $totalAmount;
	}
	/*
	 * Function to get how many products in database
	 */
	public function getProductsCount() {
		$result = 0;
		$db = $this->conn->query ( "SELECT count(*) from products WHERE 1" );
		$result = $db->fetchColumn ();
		return $result;
	}
	/*
	 * Function to get last product addusers
	 */
	public function getLastProduct() {
		return $this->getProduct ( $this->getLastProductID () );
	}
	public function value_formatter($lang, $value){
		if($lang == "br"){
			return number_format($value, 2, ',', '.');
		} else {
			$value = str_replace(".", "", $value);
			$value = str_replace(",", ".", $value);
			return $value;
		}
		return null;
	}
	public function getLastProductID() {
		$db = $this->conn->prepare ( "SELECT keyP FROM products WHERE 1 ORDER BY keyP DESC LIMIT 1" );
		try {
			if($db->execute ())
			return $db->fetch ( PDO::FETCH_OBJ )->keyP;
			
		} catch ( PDOException $e ) {
			echo $e->getMessage ();
		}
		return null;
	}
	private function hasEmpty($fields) {
		foreach ( $fields as $f ) {
			if (empty ( $f )) {
				return true;
			}
		}
		return false;
	}
}