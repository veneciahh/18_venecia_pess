<!doctype html>
<html>
<head>
<title>Police Emergency Service System</title>
<link href="header_style.css" rel="stylesheet" type="text/css">
<link href="content_style.css" rel="stylesheet" type="text/css">
<link href="customs.css" rel="stylesheet" type="text/css">
<script type="text/javascript">
function validateForm()
{
	var x=document.forms["frmLogCall"]["callerName"].value;
	if (x==null || x=="")
  	{
  		alert("Caller Name is required.");
  		return false;
  	}
  	// may add code for validating other inputs
	var x=document.forms["frmLogCall"]["contactNo"].value;
	if (x==null || x=="")
  	{
  		alert("Contact Number is required.");
  		return false;
  	}
	
	var x=document.forms["frmLogCall"]["location"].value;
	if (x==null || x=="")
  	{
  		alert("Location is required.");
  		return false;
  	}
	
	var x=document.forms["frmLogCall"]["incidentDesc"].value;
	if (x==null || x=="")
  	{
  		alert("Description is required.");
  		return false;
  	}
}
</script>
</head>
<body>
<?php 
// import nav.php
require_once 'nav.php';
 // import db.php
require_once 'db.php';

// Create connection
$mysqli = mysqli_connect (DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
// Check connection
if ($mysqli->connect_errno) {
	die("Failed to connect to MySQL: ". $mysqli->connect_errno);
}

$sql = "SELECT * FROM incident_type";

if (!($stmt = $mysqli->prepare($sql)))
{
	die("Prepare FAILED:" .$sqli->errno);
}

if (!$stmt->execute()){
	die("Execute Failed: ".$stmt->errno);
}
	
if (!($resultset = $stmt->get_result())){
	die("Getting result set failed: ".$stmt->errno);
}
	
$incidentType; // an array variable
	while ($row = $resultset->fetch_assoc()){
		/* create an associative array of $incidentType {incident_type_id, incident_type_desc] */
		$incidentType[$row['incident_type_id']] = $row['incident_type_desc'];
	}

$stmt->close();
$resultset->close();
$mysqli->close();
?>
<br><br>
<form name="frmLogCall" method="post"
	  onSubmit="return validateForm()" action="dispatch.php">
<table class="ContentStyle">
	<tr>
		<td colspan="2">Log Call Panel</td>
	</tr>
	
	<tr>
		<td>Caller's Name :</td>
		<td><input type="text" name="callerName" id="callerName"></td>
	</tr>
	
	<tr>
		<td>Contact No :</td>
		<td><input type="text" name="contactNo" id="contactNo"></td>
	</tr>
	
	<tr>
		<td>Location :</td>
		<td><input type="text" name="location" id="location"></td>
	</tr>
	
	<tr>
		<td>Incident Type :</td>
		<td><select name="incidentType" id="incidentType">
			<?php // populate a combo box with $incidentType
				foreach( $incidentType as $key => $value) {
			?>
				<option value="<?php echo $key ?>">
					<?php echo $value ?>
			    </option>
			<?php
				}
			?>
			</select>
		</td>
	</tr>
	
	<tr>
		<td>Description :</td>
		<td><textarea name="incidentDesc" id="incidentDesc" cols="45" 
					  rows="5"></textarea>
		</td>
	</tr>
	
	<tr>
		<td><input type="reset" name="btnCancel" id="btnCancel" 
				   value="Reset">
		</td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit"
			name="btnProcessCall" id="btnProcessCall" value="Process Call...">
		</td>
	</tr>
</table>
</form>
</body>
</html>