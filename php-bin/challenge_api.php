<?php
	require_once('db.inc.php');
	
	error_reporting(0);
	@ini_set('display_errors', 0);
	
	session_start();
	if( !isset($_SESSION['User']) ){
		header('Location: index.php');
		exit();
	}
	
	function load_user_data( $type ){
		$conn = new mysqli( DB_HOST, DB_USER, DB_PASS, 'users' );
		$user = $conn -> real_escape_string( $_SESSION['User'] );
		$query = "SELECT * FROM `users` WHERE name='$user'";
		$res = $conn -> query($query);
		if( !$res ){
			return '';
		}
		
		$data = $res -> fetch_assoc();
		if( !$data ){
			return '';
		}
		
		if( isset( $data[$type] ) ){
			return $data[$type];
		}
		
		return '';
	}
	function load_challenge( $id ){
		$conn = new mysqli( DB_HOST, DB_USER, DB_PASS, 'users' );
		$eid = $conn -> real_escape_string( $id );
		$query = "SELECT * FROM `challenges` WHERE challenge_id=$eid";
		
		$res = $conn -> query($query);
		if( !$res ){
			return 'Could not fetch challenge.';
		}
		$data = $res -> fetch_assoc();
		if( !$data ){
			return 'Could not fetch challenge.';
		}
		$qtext = $data['qtext'];
		return $qtext;
	}
	function load_full_challenge( $id ){
		$conn = new mysqli( DB_HOST, DB_USER, DB_PASS, 'users' );
		$eid = $conn -> real_escape_string( $id );
		$query = "SELECT * FROM `challenges` WHERE challenge_id=$eid";
		
		$res = $conn -> query($query);
		if( !$res ){
			return 'Could not fetch challenge.';
		}
		$data = $res -> fetch_assoc();
		if( !$data ){
			return 'Could not fetch challenge.';
		}
		return $data;
	}
	function load_challenges(){
		$conn = new mysqli( DB_HOST, DB_USER, DB_PASS, 'users' );
		$query = "SELECT * FROM `challenges`";
		
		$res = $conn -> query($query);
		if( !$res ){
			return [];
		}
		$data = $res -> fetch_assoc();
		if( !$data ){
			return [];
		}
		$output = [];
		do{     //This is a landmark. It is the first time I have **EVER** -effectively- used a do-while loop.
			$output[] = $data['challenge_id'];
		}while( ($data = $res -> fetch_assoc()) );
		
		return $output;
	}
	function verify_challenge( $id, $attempt ){
		$conn = new mysqli( DB_HOST, DB_USER, DB_PASS, 'users' );
		
		$eid = $conn -> real_escape_string( $id );
		$query = "SELECT * FROM `challenges` WHERE challenge_id=$eid";
		
		$res = $conn -> query( $query );
		
		if( !$res ){
			return false;
		}
		
		$data = $res -> fetch_assoc();
		if( !$data ){
			return false;
		}
		
		$ans = $data['answer'];
		
		if( $ans == $attempt ){
			return true;
		}
		
		return false;
	}
	function check_has_solved( $id ){
		$conn = new mysqli( DB_HOST, DB_USER, DB_PASS, 'users' );
		
		$team = $conn -> real_escape_string(load_user_data('team'));
		if( !$team ){
			die('You must be on a team to view progress on problems.');
		}
		
		$query = "SELECT solved FROM `teams` WHERE name='$team'";
		$res = $conn -> query($query);
		
		if( !$res ){
			die('That team does not exist!');
		}
		
		$team = $res -> fetch_assoc();
		if( !$team ){
			die('That team does not exist!');
		}
		
		$solved = $team['solved'];
		if( !$solved ){
			return false;
		}
		
		$lst = explode(',', $solved);
		foreach($lst as $q){
			if( $q == $id ){
				return true;
			}
		}
		return false;
	}
	function set_has_solved( $id ){
		$conn = new mysqli( DB_HOST, DB_USER, DB_PASS, 'users' );
		
		$team = $conn -> real_escape_string(load_user_data('team'));
		$query = "SELECT solved FROM `teams` WHERE name='$team'";
		$res = $conn -> query($query);
		
		if( !$res ){
			die('That team does not exist!');
		}
		
		$rset = $res -> fetch_assoc();
		if( !$rset ){
			die('That team does not exist!');
		}
		
		$solved = $rset['solved'];
		
		$eid = $conn -> real_escape_string($id);
		if( !$solved ){
			$solved = $eid;
		}else{
			$solved .= ",$eid";
		}
		
		$esolved = $conn -> real_escape_string($solved);
		$eteam = $conn -> real_escape_string(load_user_data('team'));
		$updatequery = "UPDATE `teams` SET solved='$esolved' WHERE name='$eteam'";
		$conn -> query($updatequery);
		if( $conn -> error ){
			die('A database error occurred.');
		}
		
		$getpoints = "SELECT points FROM `challenges` WHERE challenge_id=$eid";
		$pnt_res = $conn -> query($getpoints);
		if( !$pnt_res ){
			return "System malfunction! Challenge disappeared, like, it just went poof!";
		}
		$pnts = $pnt_res -> fetch_assoc();
		if( !$pnts ){
			return "System malfunction! Challenge disappeared, like, it just went poof!";
		}
		
		$points = $conn -> real_escape_string($pnts['points']);
		
		$addpq = "UPDATE `teams` SET points = points + $points WHERE name='$eteam'";
		$conn -> query($addpq);
	}
	
	if( isset($_POST['action']) ){
	
		$action = $_POST['action'];
		
		$conn = new mysqli( DB_HOST, DB_USER, DB_PASS, 'users' );
		
		switch($action){
		case 'fetch':
			if( !isset($_POST['id']) ){
				die("No ID provided for challenge fetch.");
			}
			echo load_challenge($_POST['id']);
			break;
		case 'list':
			$cs = load_challenges();
			$qs = [];
			foreach( $cs as $item ){
				$fc = load_full_challenge($item);
				$qs[] = json_encode([ 'qtext' => $fc['qtext'], 'challenge_id' => $fc['challenge_id'], 'points' => $fc['points'], 'category' => $fc['category'] ]);
			}
			echo json_encode($qs);
			break;
		case 'verify':
			if( !( isset($_POST['id']) && isset($_POST['answer']) ) ){
				die('ID or answer not provided to verify question.');
			}
			$id = $conn -> real_escape_string($_POST['id']);
			$ans = $conn -> real_escape_string($_POST['answer']);
			
			$hassolved = check_has_solved($id);
			if( $hassolved ){
				die('You have already solved this question!');
			}
			
			$v = verify_challenge( $id, $ans );
			if( $v ){
				set_has_solved( $id );
				echo 'OK';
			}else{
				echo 'Incorrect.';
			}
			break;
		}
	}
?>
