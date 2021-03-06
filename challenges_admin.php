<?php
	require('php-bin/challenge_api.php');

	session_start();
	$is_logged = isset($_SESSION['User']);
	
	if( !$is_logged ){
		header('Location: index.php');
		exit();
	}
	
	$role = load_user_data('role');
	if( $role != "admin" ){
		header('Location: index.php');
		exit();
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Disparity CTF -- Challenges</title>
		<link href="http://fonts.googleapis.com/css?family=Lato&subset=latin,latin-ext" rel="stylesheet" type="text/css" />
		<link rel="stylesheet" href="style.css" />
		<link rel="stylesheet" href="challenge.css" />
		<script src="home.js"></script>
		<script src="challenges_admin.js"></script>
	</head>
	<body>
		<div id="head">
			<span class="title">
				Disparity CTF
			</span>
			<ul id="nav">
				<?php
					if( !$is_logged ){
				?>
					<li onclick="Data.ShowDialog('Register')"><span class="text">Register</span></li>
					<li onclick="Data.ShowDialog('Login')"><span class="text">Log In</span></li>
				<?php
					}else{
				?>
					<?php
						if( load_user_data('role') === "admin" ){
					?>
						<li onclick="location.assign('dashboard.php')"><span class="text">Dashboard</span></li>
					<?php
						}
					?>
					<li onclick="location.assign('account.php')"><span class="text">Account</span></li>
					<li onclick="location.assign('index.php')"><span class="text">Home</span></li>
					<li onclick="location.assign('scoreboard.php')"><span class="text">Scoreboard</span></li>
					<li onclick="Data.SignOut()"><span class="text">Log Out</span></li>
				<?php
					}
				?>
			</ul>
		</div>
		<div id="challenges">
			<?php
				if( load_user_data('team') ){
					$all = load_challenges();
					$lst = [];
					foreach( $all as $chal ){
						$lst[] = load_full_challenge( $chal );
					}
					$dsp = [];
					foreach( $lst as $item ){
						$cat = $item['category'];
						$dsp[$cat][] = ["points" => $item['points'], "id" => $item['challenge_id']];
					}
					
					foreach( $dsp as $cname => $category ){
						echo "<div class=\"row\">";
						echo "<div class=\"cname\">" . htmlentities($cname) . "</div>";
						foreach( $category as $questiondata ){
							$question = htmlentities($questiondata['points']);
							$id = htmlentities($questiondata['id']);
							$cat = htmlentities($cname);
							echo <<<HTML
<div class="question solved" onclick="load_question($id)" data-resend="$question;$cat" id="question{$id}">
	$question
</div>
HTML;
						}
						echo "</div>";
					}
				}else{
					echo "<h1>You must be on a team to compete!</h1>";
				}
			?>
			<button onclick="create_question()" id="create">+</button>
		</div>
		<div id="dialogs" class="modal">
			<div class="popup" data-dlg="Login">
				<div class="title">
					Log In
					<img src="x.png" onclick="Data.HideDialog('Login')" alt="" class="closer" />
				</div>
				<form onsubmit="Data.SignIn(event)">
					<table>
						<tbody>
							<tr>
								<td>Username:</td>
								<td><input type="text" name="user" /></td>
							</tr>
							<tr>
								<td>Password:</td>
								<td><input type="password" name="pwd" /></td>
							</tr>
						</tbody>
					</table>
					<button type="submit" class="submit">Log In</button>
				</form>
			</div>
			<div class="popup" data-dlg="Register">
				<div class="title">
					Register
					<img src="x.png" onclick="Data.HideDialog('Register')" alt="" class="closer" />
				</div>
				<form onsubmit="Data.SignUp(event)">
					<table>
						<tbody>
							<tr>
								<td>Username:</td>
								<td><input type="text" name="user" placeholder="Username" /></td>
							</tr>
							<tr>
								<td>Password:</td>
								<td><input type="password" name="pwd" placeholder="Password123" /></td>
							</tr>
							<tr>
								<td>Email:</td>
								<td><input type="email" name="email" placeholder="example@example.org" /></td>
							</tr>
							<tr>
								<td>
									Why are you signing up?
								</td>
								<td>
									<select name="role">
										<option value="competitor" selected>To compete as a Student</option>
										<option value="spectator">To spectate</option>
									</select>
								</td>
							</tr>
						</tbody>
					</table>
					<button type="submit" class="submit">Register</button>
				</form>
			</div>
			<div class="popup" data-dlg="Success">
				<div class="title">
					Success
				</div>
				<span id="success-msg">Operation succeeded.</span>
				<button onclick="location.reload()" class="submit">OK</button>
			</div>
			<div class="popup" data-dlg="Failure">
				<div class="title">
					Failure
				</div>
				<span id="failure-msg">Operation failed.</span>
				<button onclick="location.reload()" class="submit">OK</button>
			</div>
			<div class="popup lower" data-dlg="Question">
				<div class="title">
					Question
					<img src="x.png" alt="" class="closer" onclick="Data.HideDialog('Question')" />
				</div>
				<form onsubmit="set_question(event)">
					<input type="text" name="category" placeholder="Category" />
					<input type="text" name="points" placeholder="Points" />
					<textarea rows="5" name="question" placeholder="Question"></textarea>
					<input type="text" placeholder="Answer" name="answer" />
					<button type="submit" class="submit">Submit</button>
				</form>
			</div>
		</div>
	</body>
</html>
