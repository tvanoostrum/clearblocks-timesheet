<?php

function connect() {
	$conn = mysql_connect('localhost', 'admin', 'admin_555');
	if ($conn === false) {
		print_r('no connect');
		return false;
	}
	else {
		return mysql_select_db('administration');
	}
}

function getPreviousMondayISO() {
	$dayFormat = "D";
	$mondayTime = time();
	$prevMonday = date($dayFormat, $mondayTime);
	if ($prevMonday !== 'Mon') {
		$mondayTime = strtotime("last Monday");
	}

	$isoFormat = "Y-m-d H:i:s";
	return date($isoFormat, $mondayTime);
}

function getWorkTimeString($startDate, $endDate) {
	$start = strtotime($startDate);
	if ($endDate === '' || $endDate === null) {
		$end = time();
	}
	else {
		$end = strtotime($endDate);
	}
	$difference_ms = $end - $start;
	return msToTimeString($difference_ms);
}

function msToTimeString($ms) {
	$seconds = floor($ms % 60);
	if ($seconds < 10) {
		$seconds = "0" . $seconds;
	}
	$ms = $ms/60;
	$minutes = floor($ms % 60);
	if ($minutes < 10) {
		$minutes = "0" . $minutes;
	}
	$ms = $ms/60;
	$hours = floor($ms % 24);
	if ($hours < 10) {
		$hours = "0" . $hours;
	}
	return $hours . ':' . $minutes . ':' . $seconds;
}

function getWorkTimeMs($startDate, $endDate) {
	$start = strtotime($startDate);
	if ($endDate === '' || $endDate === null) {
		$end = time();
	}
	else {
		$end = strtotime($endDate);
	}

	return $end - $start;
}

function toDutchDay($dayInWeek) {
	$dutchDay = '';
	switch ($dayInWeek) {
		case 'Sat':
			$dutchDay = 'Za';
			break;
		case 'Sun':
			$dutchDay = 'Zo';
			break;
		case 'Mon':
			$dutchDay = 'Ma';
			break;
		case 'Tue':
			$dutchDay = 'Di';
			break;
		case 'Wed':
			$dutchDay = 'Wo';
			break;
		case 'Thu':
			$dutchDay = 'Do';
			break;
		case 'Fri':
			$dutchDay = 'Vr';
			break;
	}
	return $dutchDay;
}

function toDateTimeStr($timestamp) {
	$format = "d-m-Y H:i:s";
	return date($format, $timestamp);
}