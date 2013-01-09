<?php 

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

	if (isset($_SERVER['HTTP_USER_AGENT'])) {
		header('Location: http://github.com/fokkezb/Ganttoday');
		exit;
	
	} else {
		header('Content-type: application/json');
		readfile('manifest.json');
		exit;
	}
}

$projectXML = simplexml_load_string($_POST["projectXML"]);

$sxe = new SimpleXMLElement($projectXML->asXML());
$sxe->registerXPathNamespace('n', 'http://schemas.microsoft.com/project');

$project = current($sxe->xpath('/n:Project/n:Tasks/n:Task[n:UID[text()="0"]]'));

$tasks = $sxe->xpath('/n:Project/n:Tasks/n:Task[n:PercentComplete < 100 and n:Summary = 0]');

$waitingForPredecessor = array();
$predecessors = array();

function waitingForPredecessor($task) {
	global $sxe, $waitingForPredecessor, $predecessors;
	
	$UID = (int) $task->UID;
	
	if (isset($waitingForPredecessor[$UID])) {	
		return $waitingForPredecessor[$UID];
	}
	
	$waitingForPredecessor[$UID] = false;

	if (isset($task->PredecessorLink)) {
				
		foreach ($task->PredecessorLink as $predecessorLink) {
			$PredecessorUID = (int) $predecessorLink->PredecessorUID;
			
			if (isset($predecessors[$PredecessorUID]) == false) {				
				$predecessors[$PredecessorUID] = current($sxe->xpath('/n:Project/n:Tasks/n:Task[n:UID = ' . $predecessorLink->PredecessorUID . ']'));
			}
				
			if ($predecessorLink->Type == 0 && $predecessors[$PredecessorUID]->PercentComplete < 100) {
				// Voltooi $predecessor om deze taak te Voltooien (Voltooi-om-te-Voltooien, VV)
				// TODO: Show color/icon/info
				
			} elseif ($predecessorLink->Type == 1 && $predecessors[$PredecessorUID]->PercentComplete < 100) {
				// Voltooi $predecessor om deze taak te Beginnen (Voltooi-om-te-Beginnen, VB)
				$waitingForPredecessor[$UID] = true;
				
			} elseif ($predecessorLink->Type == 2 && $predecessors[$PredecessorUID]->PercentComplete == 0) {
				// Begin een taak om deze taak te Voltooien (Begin-om-te-Voltooien, BV)
				// TODO: Show color/icon/info
				
			} elseif ($predecessorLink->Type == 3 && $predecessors[$PredecessorUID]->PercentComplete == 0) {
				// Begin een taak om deze taak te Beginnen (Begin-om-te-Beginnen, BB)
				$waitingForPredecessor[$UID] = true;
			}
		}
	}
		
	return $waitingForPredecessor[$UID];
}

$waitingForPredecessor = array();

function waitingForConstraint($task) {
	global $watingForConstraint;
	
	$UID = (int) $task->UID;
	
	if (isset($watingForConstraint[$UID])) {	
		return $watingForConstraint[$UID];
	}
	
	$watingForConstraint[$UID] = false;

	// Zo snel als mogelijk
	if ($task->ConstraintType == 0) {
		// TODO: Nothing?
		
	} elseif ($task->ConstraintType == 1) {
		// TODO: Show color/icon/info

	// Vang aan precies op
	} elseif ($task->ConstraintType == 2) {
			
		// Moet vandaag of eerder beginnen
		if ($task->ConstraintDate <= date('Y-m-d') . 'T23:59:59') {
			// TODO: Show color/icon/info
			
		// Mag nog niet beginnen
		} else {
			$watingForConstraint[$UID] = true;
		}
	
	// Voltooi precies op
	} elseif ($task->ConstraintType == 3) {
			
		// Moet vandaag of eerder voltooien
		if ($task->ConstraintDate <= date('Y-m-d') . 'T23:59:59') {
			// TODO: Show color/icon/info
		}
		
	// Begin niet eerder dan
	} elseif ($task->ConstraintType == 4) {
	
		// Mag nog niet beginnen
		if ($task->ConstraintDate > date('Y-m-d') . 'T23:59:59') {
			$watingForConstraint[$UID] = true;
		}
	
	// Begin niet later dan
	} elseif ($task->ConstraintType == 5) {
		
		// Moet vandaag of eerder beginnen
		if ($task->ConstraintDate <= date('Y-m-d') . 'T23:59:59') {
			// TODO: Show color/icon/info
		}
	
	// Voltooi niet eerder dan
	} elseif ($task->ConstraintType == 6) {
		
		// Moet vandaag of eerder voltooien
		if ($task->ConstraintDate <= date('Y-m-d') . 'T23:59:59') {
			// TODO: Show color/icon/info
		}
		
	// Voltooi niet later dan
	} elseif ($task->ConstraintType == 7) {
	
		// Moet vandaag of eerder voltooien
		if ($task->ConstraintDate <= date('Y-m-d') . 'T23:59:59') {
			// TODO: Show color/icon/info
		}
	}
	
	return $watingForConstraint[$UID];
}

header('Content-type: text/html');

?> 
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
		<title>Gantter.com - Ganttoday</title>
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7">
		<meta name="viewport" content="width=device-width,height=device-height,maximum-scale=1.0">
		<link rel="stylesheet" href="https://app.gantter.com/css/styles.css?v=15242168" type="text/css" media="screen,projection,print">
		<link rel="stylesheet" href="https://app.gantter.com/css/default-theme.css?v=15242168" type="text/css" media="screen,projection,print">
		<link rel="icon" href="https://app.gantter.com/images/favicon.ico" type="image/x-icon">
		<link rel="shortcut icon" href="https://app.gantter.com/images/favicon.ico" type="image/x-icon">
		<style type="text/css">
		
			html, body {
				overflow: auto;
			}
		
			#a0 div:hover {
				background-color: transparent;
				cursor: default;
			}
			
			#today-title {
				color: #999;
			}
			
			#today-wrap {
				margin: 70px 20px 20px 20px;
			}
			
			#today-table {
				width: 100%;
			}
			
			#today-table th,
			#today-table td {
				padding: 5px;
			}
			
			#today-table thead th {
				border-top: 1px solid silver;
			}
			
			#today-table thead tr th:first-child,
			#today-table tbody tr td:first-child {
				border-left: 1px solid silver;
			}
			
			#today-table thead th:first-child {
				width: 50px;
			}
			
			.today-path {
				display: block;
				color: silver;
			}
		
		</style>
	</head>
	<body>
	
		<div class="main">
		
			<div class="top">
			
				<div id="a2">
					<div id="links">
						<span id="a3" style=""><span id="a4"><?= $_POST['email'] ?></span>&nbsp;</span>&nbsp;&nbsp;<a id="b1" href="#" onclick="window.close();return false;">Close</a></span>
					</div>
				</div>
	
				<div id="logo"><a href="http://gantter.com"><img src="http://app.gantter.com/images/gantter-logo.png" width="162" height="34" alt="Gantter"></a></div>
	
				<div id="a0">
					<div id="a1"><?= $project->Name; ?> / <span id="today-title">Today</span></div>
				</div>
	
				<div id="today-wrap">
				
					<table id="today-table">
						<thead>
							<tr>
								<th>&nbsp;</th>
								<th>Task</th>
								<th>Completed</th>
								<th>Deadline</th>
							</tr>
						</thead>
						<tbody>
							<? foreach ($tasks as $task): ?>
							
								<?
								
								if (waitingForPredecessor($task)) {
									continue;
								}
								
								if (waitingForConstraint($task)) {
									continue;
								}
								
								$name = $task->Name;
								$path = '';
								
								if ($task->OutlineLevel > 1) {
									$parentOutlineNumber = substr($task->OutlineNumber, 0, strrpos($task->OutlineNumber, '.'));

									while (true) {
										$parent = current($sxe->xpath('/n:Project/n:Tasks/n:Task[n:OutlineNumber = "' . $parentOutlineNumber . '"]'));
										
										if (waitingForConstraint($parent)) {
											continue 2;
										}
										
										if (waitingForPredecessor($parent)) {
											continue 2;
										}
										
										$path = $parent->Name . (empty($path) ? '' : ' / ' . $path);
										
										if ($parent->OutlineLevel > 1) {
											$parentOutlineNumber = substr($parent->OutlineNumber, 0, strrpos($parent->OutlineNumber, '.'));
										} else {
											break;
										}
									}		
								}
								
								?>
							
								<tr>
									<td title="<?= $task->ID ?>" class="r3"><?= $task->ID ?></td>
									<td><?= $task->Name ?><? if (empty($path) == false): ?><span class="today-path"><?= $path ?></span><? endif ?></td>
									<td><?= $task->PercentComplete ?>%</td>
									<td><?= isset($task->Deadline) ? $task->Deadline : (in_array($task->ConstraintType, array(3,7)) ? $task->ConstraintDate : '&nbsp;') ?></td>
								</tr>
							<? endforeach ?>
						</tbody>
					</table>
				
				</div>
								
			</div>
			
		</div>

	</body>
</html>
