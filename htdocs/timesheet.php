<?php

require_once('timesheet_utils.php');

if (!connect()) {
	$response['ok'] = false;
	$response['msg'] = 'could not connect';
}
else {
	$action = '';
	if (isset($_POST['action'])) {
		$action = $_POST['action'];
	}
	else if (isset($_GET['action'])) {
		$action = $_GET['action'];
	}
	if ($action !== '') {
		if ($action === 'getProjects') {
			$response = getProjects();
		}
		if ($action === 'getWorkTimes') {
			$project_id = '';
			if (isset($_GET['project_id'])) {
				$project_id = $_GET['project_id'];
			}
			$response = getWorkTimes($project_id);
		}
		else if ($action === 'setStartTime') {
			$response = setStartTime($_GET['description'], $_GET['project_id']);
		}
		else if ($action === 'setEndTime') {
			$response = setEndTime($_GET['id']);
		}
		else if ($action === 'checkRunningTask') {
			$response = checkRunningTask();
		}
	}
	else {
		$response['ok'] = false;
		$response['msg'] = 'no action parameter';
	}
}

print_r(json_encode($response));

function setStartTime($description, $project_id) {
	$response = array();
	$query = "INSERT INTO timesheet (description, project_id) VALUES ('". $description ."', " . $project_id .");";
	mysql_query($query);
	$response['ok'] = true;
	$response['id'] = mysql_insert_id();
	$response['start'] = toDateTimeStr(time());

	return $response;
}

function setEndTime($id) {
	$query = "UPDATE timesheet SET end = NOW() WHERE id=".$id.";";
	mysql_query($query);
	$response['ok'] = true;
	$response['end'] = toDateTimeStr(time());

	return $response;
}

function getProjects() {
	$response = array();
	$query = "SELECT * FROM projects";
	$result = mysql_query($query);
	
	$projects = array();
	while ($row = mysql_fetch_assoc($result)) {
		
		$totalTimeStr = getTotalProjectTime($row['id']);
		$project = array(
			'name' => $row['name'],
			'id' => $row['id'],
			'description' => $row['description'],
			'contact_id' => $row['client_id'],
			'total_time' => $totalTimeStr
		);
		
		$projects[] = $project;
	}
	
	$response['ok'] = true;
	$response['projects'] = $projects;

	return $response;
}

function getTotalProjectTime($project_id) {
	$response = array();
	$query = "SELECT t.start as start, t.end as end FROM timesheet t where t.project_id = " . $project_id;
	$result = mysql_query($query);
	$totalMs = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$startDate = $row['start'];
		$endDate = $row['end'];
		$totalMs += getWorkTimeMs($startDate, $endDate);
	}
	
	return msToTimeString($totalMs);
}

function getWorkTimes($project_id) {
	
	$prevMonday = getPreviousMondayISO();
	
	$response = array();
	$query = "SELECT t.id as id, t.description as task, t.start as start, t.end as end, p.name as project"
			. " FROM timesheet t JOIN projects p on t.project_id = p.id"
			. " WHERE t.start >= '" . $prevMonday . "'";
	if ($project_id !== '') {
		$query .= " AND p.id = " . $project_id;
	}
	$query .= ' ORDER BY t.start';
	$result = mysql_query($query);
	
	$dateFormat = "d-m-Y";
	$dayFormat = "D";
	
	$worktimes = array();
	$totalMs = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$startDate = $row['start'];
		$endDate = $row['end'];
		$startStr = date($dateFormat, (strtotime($startDate)));
		$startDay = date($dayFormat, (strtotime($startDate)));		
		$startStr = toDutchDay($startDay) . " " . substr($startStr, 0, 10);
		$worktime = array(
				'id' => $row['id'],
				'project' => $row['project'],
				'task' => $row['task'],
				'date' => $startStr
		);
		
		$worktime['worktime'] = getWorkTimeString($startDate, $endDate);
		$totalMs += getWorkTimeMs($startDate, $endDate);
		
		$worktimes[] = $worktime;
	}

	$response['ok'] = true;
	$response['worktimes'] = $worktimes;
	
	$response['total_worktime'] = msToTimeString($totalMs);

	return $response;
}

function checkRunningTask() {
	$response = array();
	
	$query = "SELECT t.id as id, t.description as description, t.start as start, t.project_id as project_id"
			. " FROM timesheet t"
			. " WHERE t.end IS NULL"
			. " ORDER BY t.id DESC"
			. " LIMIT 1";
	$result = mysql_query($query);
	$taskRunning = false;
	while ($row = mysql_fetch_assoc($result)) {
		$taskRunning = true;
		$task = array(
				'id' => $row['id'],
				'project_id' => $row['project_id'],
				'description' => $row['description'],
				'start' => toDateTimeStr(strtotime($row['start']))
		);
	}
	$response['task_running'] = $taskRunning;
	if ($taskRunning) {
		$response['task'] = $task;
	}
	
	return $response;
}