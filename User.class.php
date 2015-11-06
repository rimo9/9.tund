<?php
class User{
	//privaatne muutuja
	private $connection;
	//käivitub kui tuleb new User();
	function __construct($mysqli){
		//selle klassi muutuja
		$this->connection = $mysqli;
	}
	function createUser($create_email, $password_hash){
		//objekt et saata tagasi kas errori(id,message) või success(message)
		$response = new StdClass();
		
		$stmt = $this->connection->prepare("SELECT email FROM user_sample WHERE email = ?");
		$stmt->bind_param("s", $create_email);
		$stmt->execute();
		if($stmt->fetch()){
			//saadan errori
			$error = new StdClass();
			$error->id = 0;
			$error->message = "email already used";
			//error responsele külge
			$response->error = $error;
			//peale returni koodi ei vaadata enam funktsioonis
			return $response;
		}
		////////NB! - panen eelmise käsu kinni////////////
		$stmt->close();
		
		$stmt = $this->connection->prepare("INSERT INTO user_sample (email, password) VALUES (?, ?)");
		$stmt->bind_param("ss", $create_email, $password_hash);
		if($stmt->execute()){
			//salvestas edukalt
			$success = new StdClass();
			$success->message = "Sucessfully created new user";
			$response->success = $success;
		}else{
			//ei läinud edukalt
			//saadan errori
			$error = new StdClass();
			$error->id = 1;
			$error->message = "Something broke";
			//error responsele külge
			$response->error = $error;
		}
		$stmt->close();
		return $response;
	}
	function loginUser($email, $password_hash){
		$response = new StdClass();
		$stmt = $this->connection->prepare("SELECT id, email FROM user_sample WHERE email=?");
		$stmt->bind_param("s", $email);
		$stmt->execute();
		if(!$stmt->fetch()){
			// saadan tagasi errori
			$error = new StdClass();
			$error->id = 2;
			$error->message = "This email doesen't exist";
			
			//panen errori responsile külge
			$response->error = $error;
			// pärast returni enam koodi edasi ei vaadata funktsioonis
			return $response;
		}
		$stmt->close();
		$stmt = $this->connection->prepare("SELECT id, email FROM user_sample WHERE email=? AND password=?");
		$stmt->bind_param("ss", $email, $password_hash);
		$stmt->bind_result($id_from_db, $email_from_db);
		$stmt->execute();
		if($stmt->fetch()){
			$success = new StdClass();
			$success->message = "Sucessfully logged in";
			$user = new StdClass();
			$user->id = $id_from_db;
			$user->email = $email_from_db;
			$success->user = $user;
			$response->success = $success;
		}else{
			$error = new StdClass();
			$error->id = 3;
			$error->message = "Wrong password";
			$response->error = $error;
		}
		$stmt->close();
		return $response;
	}
}?>