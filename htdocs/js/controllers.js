var timesheetApp = angular.module('timesheetApp', []);

timesheetApp.controller('TimesheetCtrl', function($scope, $http) {
		$scope.loadProjects = function() {
			$http({
				url:'timesheet.php',
				method: 'GET',
				params: {'action': 'getProjects'}
			}).success(function(data) {
				$scope.projects = data['projects'];
			});
		};
		$scope.loadWorkTimes = function(project_id) {
			$http({
				url:'timesheet.php',
				method: 'GET',
				params: {'action': 'getWorkTimes', 'project_id': project_id}
			}).success(function(data) {
				console.log(data);
				$scope.worktimes = data['worktimes'];
				$scope.total_worktime = data['total_worktime'];
			});
		}
		
		$scope.checkRunningTask = function() {
			$http({
				url:'timesheet.php',
				method: 'GET',
				params: {'action': 'checkRunningTask'}
			}).success(function(data) {
				console.log(data);
				if (data.task_running) {
					$('#description').val(data.task.description);
					$('#timesheet_id').val(data.task.id);
					angular.element('#end').show();
					angular.element('#start').hide();
					$('select#project_id').val(data.task.project_id);
					$('#msg').html("start: " + data.task.start);
				}
			});
		}
		
		$scope.setStartTime = function() {
			var desc = angular.element('#description').val();
			var project_id = angular.element('#project_id').val();
			var msgElem = angular.element('#msg');
			if (desc == '') {
				msgElem.html("description is empty");
				return;
			}
			else {
				msgElem.html("");
			}

			var params = {
				'action': 'setStartTime',
				'description' : desc,
				'project_id' : project_id
			};
			
			$http({
				url:'timesheet.php',
				method: 'GET',
				params: params
			}).success(function(data) {
				if (data.ok == true) {
					angular.element('#timesheet_id').val(data.id);
					angular.element('#start').attr('style', 'display:none');
					angular.element('#end').attr('style', 'display:inline');
					msgElem.html("start: " + data.start);
				}
				else {
					msgElem.html(data.msg);
				}
			});
		};
		
		$scope.setEndTime = function() {
			var msgElem = angular.element('#msg');
			var params = {
				'action': 'setEndTime',
				'id' : angular.element('#timesheet_id').val()
			};
			$http({
				url:'timesheet.php',
				method: 'GET',
				params: params
			}).success(function(data) {
				if (data.ok == true) {
					angular.element('#description').val("");
					angular.element('#timesheet_id').val("");
					angular.element('#end').hide();
					angular.element('#start').show();
					msgElem.html("eind: " + data.end);
					$scope.loadWorkTimes();
				}
				else {
					msgElem.html(data.msg);
				}
			});
		};
		
		$scope.loadProjects('');
		$scope.loadWorkTimes();
		$scope.checkRunningTask();
});