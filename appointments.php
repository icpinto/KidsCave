<?php
session_start();
require_once 'class.user.php';
$user_login = new USER();
include_once($_SERVER['DOCUMENT_ROOT'].'/KidsCave/backend/dbconfig.php');

if(!$user_login->is_logged_in())
{
	$user_login->redirect('index.php');
}

$stmt = $user_login->runQuery("SELECT * FROM tbl_users WHERE userID=:uid");
$stmt->execute(array(":uid"=>$_SESSION['userSession']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$userID = $row['userID'];
if ($_SESSION['userRole']=="Parent"){
	$sql = ("SELECT student_guardianID FROM student_guardian WHERE userID='".$userID."'");
	$result = mysqli_query($connect,$sql);
	if (mysqli_num_rows($result)>0){
		$row1 = mysqli_fetch_assoc($result);
		$student_guardianID = $row1['student_guardianID'];
	}
}elseif ($_SESSION['userRole']=="Teacher"){
	$sql = ("SELECT teacherID FROM teacher WHERE userID='".$userID."'");
	$result = mysqli_query($connect,$sql);
	if (mysqli_num_rows($result)>0){
		$row1 = mysqli_fetch_assoc($result);
		$teacherID = $row1['teacherID'];
	}
}
?>
<?php
	if ($_SERVER["REQUEST_METHOD"]=="POST"){
		if ($_POST['submit_form']=="parent"){
			$teacherID = $_POST["teacherID"];
			$guardianID = $student_guardianID ;
			$timestamp_date =($_POST["date"]);
			$timestamp_time =($_POST["time"]);
			$com = ($timestamp_date." ".$timestamp_time.":00");
			$timestamp = date('Y-m-d H:i:s',strtotime($com));
	
			$stmt = $connect->prepare("INSERT INTO teacher_appointment(teacherID,askedBy , guardianID, message, appointmentDateTime) VALUES(?, ?, ?, ?, ? )");
			$stmt->bind_param("isiss", $teacherID, $userRole, $guardianID, $message, $dateTime);
			$userRole = $_SESSION['userRole'];
			$guardianID = $student_guardianID ;
			$message = $_POST["message"];
			$dateTime = $timestamp;
	
			$stmt->execute();
			$stmt->close();
		}
		elseif($_POST['submit_form']=='guaridanApprove'){
			$appointmentID = $_POST['appointmentID'];
			$approve = $_POST['approve'];
			if ($approve=="approve"){
				$sql=("UPDATE teacher_appointment SET approvalGuardian='1' WHERE appointmentID='".$appointmentID."'");
				if ($connect->query($sql) === TRUE) {
					echo "Record updated successfully";
				}else {
					echo "Error updating record: " . $connect->error;
				}
			}elseif ($approve=="disapprove"){
				$sql=("UPDATE teacher_appointment SET approvalGuardian='2' WHERE appointmentID='".$appointmentID."'");
				if ($connect->query($sql) === TRUE) {
					echo "Record updated successfully";
				}else {
					echo "Error updating record: " . $connect->error;
				}
			}
		}
		elseif ($_POST['submit_form']=="teacherApprove"){
			$appointmentID = $_POST['appointmentID'];
			$approve=$_POST['approve'];
			if ($approve=="approve"){
				$sql=("UPDATE teacher_appointment SET approvalTeacher='1' WHERE appointmentID='".$appointmentID."'");
				if ($connect->query($sql) === TRUE) {
					echo "Record updated successfully";
				}else {
					echo "Error updating record: " . $connect->error;
				}
			}elseif ($approve=="disapprove"){
				$sql=("UPDATE teacher_appointment SET approvalTeacher='2' WHERE appointmentID='".$appointmentID."'");
				if ($connect->query($sql) === TRUE) {
					echo "Record updated successfully";
				}else {
					echo "Error updating record: " . $connect->error;
				}
			}
		}elseif ($_POST['submit_form']=="teacherAppoinment"){
			$timestamp_date =($_POST["date"]);
			$timestamp_time =($_POST["time"]);
			$com = ($timestamp_date." ".$timestamp_time.":00");
			$timestamp = date('Y-m-d H:i:s',strtotime($com));
	
			$stmt = $connect->prepare("INSERT INTO teacher_appointment(teacherID,askedBy , guardianID, message, appointmentDateTime) VALUES(?, ?, ?, ?, ? )");
			$stmt->bind_param("isiss", $teacherID, $userRole, $guardianID, $message, $dateTime);
			$userRole = $_SESSION['userRole'];
			$guardianID = $_POST['guardianID'];
			$message = $_POST["message"];
			$dateTime = $timestamp;
	
			$stmt->execute();
			$stmt->close();
		}	
	}
?>

<!-- top html header -->
<?php include('includes/top-header.php'); ?>
<!-- //top html header -->
<!-- header -->
<?php include('includes/header.php'); ?>
<!-- //header -->
<div style="min-height: calc(100vh - 190px); margin-top: 1em; margin-bottom:3em;">
	<div class="container" ><div style =" border-bottom:2px solid #14a1ff;"><h1>Appointments</h1></div><br>
		<div class="row" >
			<!-- left panel -->
			<?php include('includes/left-panel.php'); ?>
			<!-- //left panel -->
			<!-- right panel -->
			<div class="col-md-9">
				<!-- parent------------------------------------->
				<?php if($_SESSION['userRole']=="Parent") { ?>
					<!--parent received appointments---------------->
					<div >
						<h2 style="display:inline">Received appointments </h2>
						<button type='submit' class='btn btn-primary' style=" display:inline ; float:right" onclick="showElement('table1');"> Hide </button>
					</div>
					<br>
					<div id="table1">
						<form action = "<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ;?>" method="post">
							<table class="table table-bordered" style="font-size:14px">
								<tr>  
									<th width="10%" class="text-center">Appointment Id</th>
									<th width="10%" class="text-center">Teacher Id</th>  
									<th width="25%" class="text-center">Date of appointment</th>
									<th width="10%" class="text-center">Time</th>
									<th width="20%" class="text-center">message</th>
									<th width="30%" class="text-center">Status</th>
								</tr> 
							<?php 
						
								$sql = ("SELECT appointmentID,teacherID,appointmentDateTime,approvalGuardian,message FROM teacher_appointment WHERE (guardianID='".$student_guardianID."' AND askedBy='Teacher')");
								$result = mysqli_query($connect,$sql);
								if (!$result || mysqli_num_rows($result)==0){
									echo "no result to display";
								}else{
									while($row1 = mysqli_fetch_assoc($result)){
										$date = date('Y-m-d',strtotime($row1['appointmentDateTime']));
										$time = date('H:i',strtotime($row1['appointmentDateTime']));
										$approvalGuardian=$row1['approvalGuardian'];
										$appointmentID = $row1['appointmentID'];
										$message = $row1['message'];
										$teacherID = $row1['teacherID'];
										echo "<tr>
												<td>".$appointmentID."</td> 
												<td>".$teacherID."</td> 
												<td>".$date."</td> 
												<td>".$time."</td>
												<td>".$message."</td>";
												if ($approvalGuardian==1){
													echo "<td> approved </td>";
												}elseif ($approvalGuardian==2){
													echo "<td> disapproved </td>";
												}else {
													echo "<td><select name='approve' style='display:inline'>
															<option selected value='-'> - </option>
															<option value='approve'> approve </option>
															<option value='disapprove'> disapprove </option>
														</select>
														<input style='display:none' name='appointmentID' value='".$appointmentID."'/>
														<input style='display:none' name='submit_form' value='guaridanApprove'>
														<button type='submit' name='submit' value='submit_2' class='btn btn-primay'> save</button>
														</td> 
													</tr>";
												}
									}
								}
							?>
							</table>
						</form>
					</div>
					<!--parent sent appointments------------------->
					<br>
					<div >
						<h2 style="display:inline">appointments sent by you</h2>
						<button type='submit' class='btn btn-primary' style=" display:inline ; float:right" onclick="showElement('table2');"> Hide </button>
					</div>
					<br>
					<table id="table2" class="table table-bordered">
						<tr>  
                            <th width="10%" class="text-center">Appointment Id</th>
							<th width="10%" class="text-center">Teacher Id</th>  
							<th width="25%" class="text-center">Date of appointment</th>
							<th width="10%" class="text-center">Time</th>
							<th width="30%" class="text-center">Status</th>
                        </tr> 
					<?php 
						
						$sql = ("SELECT appointmentID,teacherID,appointmentDateTime,approvalTeacher FROM teacher_appointment WHERE guardianID='".$student_guardianID."' AND askedBy='Parent'");
						$result = mysqli_query($connect,$sql);
						if (!$result || mysqli_num_rows($result)==0){
							echo "no result to display";
						}else{
							while($row1 = mysqli_fetch_assoc($result)){
								$appointmentID = $row1['appointmentID'];
								$teacherId = $row1['teacherID'];
								if ($teacherId==0){
									$teacherId = "principal";
								}
								$date = date('Y-m-d',strtotime($row1['appointmentDateTime']));
								$time = date('H:i',strtotime($row1['appointmentDateTime']));
								if ($row1['approvalTeacher']==1){
									$approve = "approved";
								}elseif ($row1['approvalTeacher']==2){
									$approve = "disapproved";
								}else{
									$approve = "Waiting for response";
								}
								echo "<tr>
										<td>".$appointmentID."</td> 
										<td>".$teacherID."</td> 
										<td>".$date."</td> 
										<td>".$time."</td> 
										<td>".$approve."</td> 
									</tr>";
							}
						}
					?>
					</table>
					<!--create new appointment parent------->
					<button type='submit' class='btn btn-primary' style="width:50%; display:block ; margin:auto" onclick="showElement('form1');"> new appointment </button><br>
					<div id="form1" name="form1" style="display:none";>
						<form action = "<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ;?>" method="post" >
							<div class='form-group' id="teacherIdInput">
								<label> Enter the id of the teacher you wish to contact</label>
								<input type='number' class='form-contol' name= 'teacherID' id='teacherID' placeholder='teacher id' min="1" max="20" required >
							</div>
							<div class='form-group'>
								<label> Enter the Message </label><br/>
								<textarea type='text' class='form-group' name='message' id='message' placeholder='type your message here' style="height:100px ; width:900px ;resize:none ; margin:auto" required></textarea>
							</div>
							<div class='form-group'>
								<label> Enter the date for the appointment</label>
								<input type='date' class='form-contol' name= 'date' id='date' placeholder='date' required>
							</div>
							<div class='form-group'>
								<label> Enter the time for the appointment</label>
								<input type='time' class='form-contol' name= 'time' id='time' placeholder='time'>
							</div>
							<input style='display:none' name='submit_form' value='parent'>
							<button type='submit' name="submit" value="submit_1" class='btn btn-primary' style="width:50%; display:block ; margin:auto" onclick="sentAlert('teacherID','message');"> Send </button><br>
						</form>
					</div>
				<?php ;}elseif ($_SESSION['userRole']=="Teacher"){?>
				<!--teacher ------------------------------------------------------------------------------------------------------------------------>
					<!----- appointments recieved by teacher-->
					<div >
						<h2 style="display:inline">Received appointments </h2>
						<button type='submit' class='btn btn-primary' style=" display:inline ; float:right" onclick="showElement('table1');"> Hide </button>
					</div>
					<br>
					<div id="table1">
						<form action = "<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ;?>" method="post">
							<table class="table table-bordered" style="font-size:15px">
								<tr>  
									<th width="10%" class="text-center">Appointment Id</th>
									<th width="10%" class="text-center">Parent Id</th>  
									<th width="25%" class="text-center">Date of appointment</th>
									<th width="10%" class="text-center">Time</th>
									<th width="25%" class="text-center">message</th>
									<th width="30%" class="text-center">Status</th>
								</tr> 
							<?php 
						
								$sql = ("SELECT appointmentID,guardianID,appointmentDateTime,approvalTeacher,message FROM teacher_appointment WHERE (teacherID='".$teacherID."' AND askedBy='Parent')");
								$result = mysqli_query($connect,$sql);
								if (!$result || mysqli_num_rows($result)==0){
									echo "no result to display";
								}else{
									while($row1 = mysqli_fetch_assoc($result)){
										$date = date('Y-m-d',strtotime($row1['appointmentDateTime']));
										$time = date('H:i',strtotime($row1['appointmentDateTime']));
										$approvalTeacher=$row1['approvalTeacher'];
										$appointmentID = $row1['appointmentID'];
										$message = $row1['message'];
										echo "<tr>
												<td>".$appointmentID."</td> 
												<td>".$row1['guardianID']."</td> 
												<td>".$date."</td> 
												<td>".$time."</td>
												<td>".$message."</td>";
												if ($approvalTeacher==1){
													echo "<td> approved </td>";
												}elseif ($approvalTeacher==2){
													echo "<td> disapproved </td>";
												}else {
													echo "<td><select name='approve'>
															<option selected value='-'> - </option>
															<option value='approve'> approve </option>
															<option value='disapprove'> disapprove </option>
														</select>
														<input style='display:none' name='appointmentID' value='".$appointmentID."'/>
														<input style='display:none' name='submit_form' value='teacherApprove'>
														<button type='submit' name='submit' value='submit_2' class='btn btn-primay'> save</button>
														</td> 
													</tr>";
												}
									}
								}
							?>
							</table>
						</form>
					</div>
					<!----- appointments sent by teacher-->
					<br>
					<div style="display:inline">
						<h2 style=" display:inline ;"> appointments sent by you  </h2>
						<button type='submit' class='btn btn-primary' style=" display:inline ; float:right" onclick="showElement('table2');"> Hide </button><br>
					</div>
					<br>
					<div id="table2">
						<table class="table table-bordered">
							<tr>  
								<th width="10%" class="text-center">Appointment Id</th>
								<th width="10%" class="text-center">Parent Id</th>  
								<th width="25%" class="text-center">Date of appointment</th>
								<th width="10%" class="text-center">Time</th>
								<th width="30%" class="text-center">Status</th>
							</tr> 
							<?php 
							$sql = ("SELECT appointmentID,guardianID,appointmentDateTime,approvalGuardian FROM teacher_appointment WHERE (teacherID='".$teacherID."' AND askedBy='Teacher')");
							$result = mysqli_query($connect,$sql);
							if (!$result || mysqli_num_rows($result)==0){
								echo "no result to display";
							}else{
								while($row1 = mysqli_fetch_assoc($result)){
									$date = date('Y-m-d',strtotime($row1['appointmentDateTime']));
									$time = date('H:i',strtotime($row1['appointmentDateTime']));
									$approvalGuardian=$row1['approvalGuardian'];
									$appointmentID = $row1['appointmentID'];
									echo "<tr>
											<td>".$appointmentID."</td> 
											<td>".$row1['guardianID']."</td> 
											<td>".$date."</td> 
											<td>".$time."</td>";
											if ($approvalGuardian==1){
													echo "<td> approved </td>";
												}elseif ($approvalGuardian==2){
													echo "<td> disapproved </td>";
												}else {
													echo "<td> waiting for response </td> ";
												}
									echo "</tr>";			
								}
							}
						?>
						</table>
					</div>
					<!----- create new appointment-->
					<br>
					<button type='submit' class='btn btn-primary' style="width:50%; display:block ; margin:auto" onclick="showElement('form2');"> create new appointment </button><br>
					<div id="form2" name="form2" style="display:none";>
						<form action = "<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ;?>" method="post" >
							<div class='form-group'>
								<label> Enter the id of the parent you wish to contact</label>
								<input type='number' class='form-contol' name= 'guardianID' id='guardianID' placeholder='guardian id' min="1" max="20" required >
							</div>
							<div class='form-group'>
								<label> Enter the Message </label><br/>
								<textarea type='text' class='form-group' name='message' id='message' placeholder='type your message here' style="height:100px ; width:900px ;resize:none ; margin:auto" required></textarea>
							</div>
							<div class='form-group'>
								<label> Enter the date for the appointment</label>
								<input type='date' class='form-contol' name= 'date' id='date' placeholder='date' required>
							</div>
							<div class='form-group'>
								<label> Enter the time for the appointment</label>
								<input type='time' class='form-contol' name= 'time' id='time' placeholder='time'>
							</div>
							<input style='display:none' name='submit_form' value='teacherAppoinment'>
							<button type='submit' name="submit" value="submit_3" class='btn btn-primary' style="width:50%; display:block ; margin:auto" onclick="sentAlert('guardianID','message');"> Send </button><br>
						</form>
					</div>
				<?php ;} ?>
			</div>
			<!-- right panel -->
		</div>
	</div>
</div>
<script>
function sentAlert(id1,id2) {
	var a = document.getElementById(id1).value;
	var b = document.getElementById(id2).value;
	if (a != "" && b != ""){
	alert("message sent succesefully");
	alert(htmlstring);
	} 
}
function showElement(id){
	var a = document.getElementById(id);
	if (a.style.display=='none'){
		a.style.display="block";
	}else{
		a.style.display="none";
	}
		
}
</script>
<!-- footer -->
<?php include('includes/footer.php'); ?>
<!-- //footer -->
<?php include('includes/bottom-footer.php'); ?>