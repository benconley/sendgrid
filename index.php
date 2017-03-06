<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<title>SendGrid Email Submission</title>
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

	<style>
		.medPad { padding-left:40px; }
		ul { padding-left:40px; list-style: none; }
		li { padding-left:40px; }
	</style>
</head>
<body>
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">&nbsp;</div>
		</div>
		<div class="row">
			<div class="col-md-1"></div>
			<div class="col-md-10">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<h4>
							SendGrid Email Submission	
						</h4>
					</div>
					<div class="panel-body">

					<form class="form-horizontal" id="msgForm" method="post" enctype="multipart/form-data">
						<div class="form-group">
							<label for="toemail" class="col-md-2 control-label">To:</label>
							<div class="col-md-10">
								<input type="email" class="form-control" id="toemail" name="toemail" placeholder="Email" required>
							</div>
						</div>
						<div class="form-group">
							<label for="fromemail" class="col-md-2 control-label">From:</label>
							<div class="col-md-10">
								<input type="email" class="form-control" id="fromemail" name="fromemail" placeholder="Email" required>
							</div>
						</div>
						<div class="form-group">
							<label for="subject" class="col-md-2 control-label">Subject:</label>
							<div class="col-md-10">
								<input type="text" class="form-control" id="subject" name="subject" placeholder="Subject" required>
							</div>
						</div>
						<div class="form-group">
							<label for="message" class="col-md-2 control-label">Message:</label>
							<div class="col-md-10">
								<textarea class="form-control" rows="3" id="message" placeholder="Message" name="message" required></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="attachment" class="col-md-2 control-label">Attachment:</label>
							<div class="col-md-10">
								<input type="file" class="form-control" id="attachment" name="attachment" placeholder="Attachment">
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-2"></div>
							<div class="col-md-2">
								<button type="reset" class="btn btn-warning">Reset</button>
								<button type="submit" class="btn btn-success">Submit</button>
							</div>
							<div class="col-md-8" id="results" class="pull-left"><strong><small>*Emails are sent with a 5 minute delay</small></strong></div>
						</div>

					</form>
						
					</div>
				</div>
			</div>
			<div class="col-md-1"></div>
		</div>
	</div>
	<script>
		$("form#msgForm").submit(function(){

			var formData = new FormData($(this)[0]);

			$.ajax({
				url: 'service/sendmail.php',
				type: 'POST',
				data: formData,
				datatype: 'json',
				contentType: false,
				processData: false,
				beforeSend: function() {
					var tempText = 'Submitting your email. This may take a moment...';
					$('#results').removeClass('text-danger text-success');
					$('#results').css("font-weight", "bold");
					$('#results').html(tempText);
				},
				success: function (data) {
					$('#results').html('<div></div>');
					if ( typeof(data.status) !== 'undefined' ) {
						$('#results').addClass( data.status == true ? 'text-success' : 'text-danger' );
						var msg = (data.status == true ? 'Success - ' : '') + data.message ;
						if (data.detail.length > 0) {
							msg += ' - ' + data.detail;
						}
						$('#results').html('<span id="disptext">' + msg + '</span>');
					}	
				},
				cache: false
			});

			return false;
		});

		$("form#msgForm").bind('reset', function() {
			$('#results').html('<div></div>');
			$('#results').removeClass('text-danger text-success');
		});

	</script>
</body>
</html>


