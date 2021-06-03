<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Police Emergency Service System</title>
<link href="header_style.css" rel="stylesheet" type="text/css">
<link href="content_style.css" rel="stylesheet" type="text/css">
<link href="customs.css" rel="stylesheet" type="text/css">
<?php // validate if request comes from logcall.php or post back
if (!isset($_POST["btnProcessCall"]) && !isset($_POST["btnDispatch"]))
	header("Location: logcall.php");
?>

<?php // if post back
if (isset($_POST["btnDispatch"]))
{
	require_once 'db.php';
	// create database connection
	$mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
		// Check connection
	if ($mysqli->connect_errno) {
		die("Failed to connect to MySQL: ".$mysqli->connect_errno);
	}
	
	$patrolcarDispatched = $_POST["chkPatrolcar"];
	// array of patrolcar being dispatched from post back
	$numOfPatrocarDispatched = count ($patrolcarDispatched);
	// INSERT NEW INCIDENT
	$incidentStatus;
	if ($numofPatrocarDispatched > 0) {
		$incidentStatus='2'; 	// incident status to be set as Dispatched
	} else {
		$incidentStatus='1'; 	// incident status to be set as Pending
	}
	
	$sql = "INSERT INTO incident(caller_name, phone_number, incident_type_id, incident_location, incident_Desc,
	incident_status_id) VALUES (?, ?, ?, ?, ?, ?)";
	
	if (!($stmt = $mysqli->prepare($sql))) {
		die("Prepare FAILED: ".$mysqli->errno);
	}
	
	if (!$stmt->bind_param('ssssss', $_POST['callerName'],
									$_POST['contactNo'],
									$_POST['incidentType'],
									$_POST['location'],
									$_POST['incidentDesc'],
									$incidentStatus)){
		die("Binding parameters failed: ".$stmt->errno);
	}
	
	if (!$stmt->execute()) {
		die("Insert incident table failed: ".$stmt->errno);
	}
	
	// RETRIEVE INCIDENT_ID FOR THE NEWLY INSERTED INCIDENT
	$incidentId=mysqli_insert_id($mysqli);;
	
	// UPDATE PATROLCAR STATUS TABLE AND ADD INTO DISPATCH TABLE
	for($i=0; $i < $numofPatrocarDispatched; $i++)
	{
		// UPDATE PATROL CAR STATUS /////////////
		$sql = "UPDATE patrolcar SET patrolcar_status_id='1' WHERE patrolcar_id = ?";
		
		if(!($stmt = $mysqli->prepare($sql))) {
			die("Prepare FAILED: ".$mysqli->errno);
		}
		
		if(!$stmt->bind_param('s', $patrolcarDispatched['$i'])){
			die("Binding parameters failed: ".$stmt->errno);
		}
		
		if (!$stmt->execute()) {
			die("Update patrolcar_status table failed: ".$stmt->errno);
		}
		
		// INSERT DISPATCH DATA ////////////
		$sql = "INSERT INTO dispatch (incident_id, patrolcar_id, time_dispatched) VALUES (?, ?, NOW())";
		
		if (!($stmt = $mysqli->prepare($sql))) {
			die("Prepare FAILING: ".$mysqli->errno);
		}
		
		if (!$stmt->bind_param('ss', $incidentId,
										$patrolcarDispatched[$i])){
			die("Binding parameters failed: ".$stmt->errno);
		}
		
		if (!$stmt->execute()) {
			die("Insert dispatch table failed: ".$stmt->errno);
		}
	}
	$stmt->close();
	$mysqli->close();
?>

<script type="text/javascript">window.location="./logcall.php";</script>
<?php } ?>
</head>
<body>
<?php require_once 'nav.php'; ?>
<br><br>
<form name="form1" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?> ">
<table class="ContentStyle">
		<tr>
			<td colspan="2">Incident Detail</td>
		</tr>
		
	<tr>
		<td>Caller's Name :</td>
		<td><?php echo $_POST['callerName'] ?>
			<input type="hidden" name="callerName" id="callerName"
			value="<?php echo $_POST['callerName'] ?>"></td>
	</tr>
	
	<tr>
		<td>Contact No :</td>
		<td><?php echo $_POST['contactNo'] ?>
			<input type="hidden" name="contactNo" id="contactNo"
			value="<?php echo $_POST['contactNo'] ?>"></td>
	</tr>
	
	<tr>
		<td>Location :</td>
		<td><?php echo $_POST['location'] ?>
			<input type="hidden" name="location" id="location"
			value="<?php echo $_POST['location'] ?>"></td>
	</tr>
	
	<tr>
		<td>Incident Type :</td>
		<td><?php echo $_POST['incidentType'] ?>
			<input type="hidden" name="incidentType" id="incidentType"
			value="<?php echo $_POST['incidentType'] ?>"></td>
	</tr>
	
	<tr>
		<td>Description :</td>
		<td><textarea name="incidentDesc" cols="45"
				rows="5" readonly id="incidentDesc"><?php echo $_POST['incidentDesc'] ?></textarea>
			<input name="incidentDesc" type="hidden" id="incidentDesc"
			value="<?php echo $_POST['incidentDesc'] ?>"></td>
	</tr>
</table>
<br><br>
<?php
// connect to a database
require_once 'db.php';
// create database connection
$mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
// check connection
if ($mysqli->connect_errno) {
	die("Failed to connect to MySQL: ".$mysqli->connect_errno);
}
// retrieve from patrolcar table those patrol cars that are 2:Patrol or 3:Free
$sql= "SELECT patrolcar_id, patrolcar_status_desc FROM patrolcar JOIN patrolcar_status 
ON patrolcar.patrolcar_status_id=patrolcar_status.patrolcar_status_id
WHERE patrolcar.patrolcar_status_id='2' OR patrolcar.patrolcar_status_id='3'";

if (!($stmt = $mysqli->prepare($sql))) {
	die("Prepare failed: ".$mysqli->errno);
}

if (!$stmt->execute()) {
	die("Execute failed: ".$stmt->errno);
}

if (!($resultset = $stmt->get_result())) {
	die("Getting result set failed: ".$stmt->errno);
}
$patrolcarArray;

while ($row = $resultset->fetch_assoc()) {
	$patrolcarArray[$row['patrolcar_id']] = $row['patrolcar_status_desc'];
}
$stmt->close();
$resultset->close();
$mysqli->close();
?>
<table class="ContentStyle">
	<tr>
		<td colspan="3">Dispatch Patrolcar Panel</td>
	</tr>
	
	<?php 
		foreach($patrolcarArray as $key=>$value) {
	?>
	
	<tr>
		<td><input type="checkbox" name="chkPatrolcar[]"
			value="<?php echo $key?>"></td>
		<td><?php echo $key ?></td>
		<td><?php echo $value ?></td>
	</tr>
	
	<?php } ?>
	<tr>
		<td><input type="reset"
			name="btnCancel" id="btnCancel" value="Reset"></td>
		<td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input
		type="submit" name="btnDispatch" id="btnDispatch" value="Dispatch">
		</td>
	</tr>
</table>
</form>
</body>
</html>