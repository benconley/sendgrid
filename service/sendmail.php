<?php
require '../vendor/autoload.php';

class sendemail{
	//validate incoming data
	public $respObj = ['status'=>true, 'message'=>'Your email has been sent','detail'=>''];
	protected $defFailMsg = 'An error has occurred';
	protected $toemail, $fromemail, $subject, $message, $apiKey;
	protected $delaySecs = 300;
	public function __construct() {
		$this->apiKey = trim(file_get_contents('/home/bcphpuser/backups/sendgrid.env'));
		$this->validate();
		if ($this->respObj['status']) {
			$this->submitMail();
		}
		$this->yieldResponse();
	}

	protected function validate() {
		if (!$_POST['toemail'] || !$_POST['fromemail'] || !$_POST['subject']) {
			$this->respObj['status'] = false;
			$this->respObj['message'] = $this->defFailMsg;
			$this->respObj['detail'] = 'Invalid parameters';
			return;
		}
		if (!filter_var($_POST['toemail'], FILTER_VALIDATE_EMAIL)){
			$this->respObj['status'] = false;
			$this->respObj['message'] = $this->defFailMsg;
			$this->respObj['detail'] = 'Please enter a valid destination email address';
			return;
		}
		if (!filter_var($_POST['fromemail'], FILTER_VALIDATE_EMAIL)){
			$this->respObj['status'] = false;
			$this->respObj['message'] = $this->defFailMsg;
			$this->respObj['detail'] = 'Please enter a valid source email address';
			return;
		}
		if (!strlen(trim($_POST['subject']))){
			$this->respObj['status'] = false;
			$this->respObj['message'] = $this->defFailMsg;
			$this->respObj['detail'] = 'Subject is required';
			return;
		}
		if (!strlen(trim($_POST['message']))){
			$this->respObj['status'] = false;
			$this->respObj['message'] = $this->defFailMsg;
			$this->respObj['detail'] = 'Message is required';
			return;
		}
		$this->toemail = $_POST['toemail'];
		$this->fromemail = $_POST['fromemail'];
		$this->subject = $_POST['subject'];
		$this->message = $_POST['message'];
	}

	protected function submitMail() {
		// build submission object
		$mailObj = [
			"personalizations" => [[
				"to" => [["email" => $this->toemail, "name" => $this->toemail]],
				"subject" => $this->subject,
				"headers" => ["X-Accept-Language" => "en", "X-Mailer" => "MyApp"],
				"custom_args" => [
					"Developer" => "Ben",
					"Company" => "FilterEasy",
					"Email_Name" => "test"
				],
				"send_at" => time() + intval($this->delaySecs)
			]],
			"from" => ["email" => $this->fromemail, "name" => $this->fromemail],
			"content" => [["type" => "text/html", "value" => $this->message]]
		];

		if(is_uploaded_file($_FILES['attachment']['tmp_name'])) {
			$mailObj["attachments"] = array(
				array(
					"content" => base64_encode(file_get_contents($_FILES['attachment']['tmp_name'])),
					"type" => $_FILES['attachment']['type'],
					"name" => $_FILES['attachment']['name'],
					"filename" => $_FILES['attachment']['name'],
					"content_id" => uniqid()
				)
			);
		}
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.sendgrid.com/v3/mail/send",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($mailObj),
			CURLOPT_HTTPHEADER => array(
			"authorization: Bearer $this->apiKey",
			"content-type: application/json"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);
		if ($err) {
			$this->respObj['status'] = false;
			$this->respObj['message'] = 'cURL Error #:' . $err;
		} else {
			if(strlen($response)) {
				$errArr = json_decode($response);
				if ($errArr) {
					$errPtr = $errArr->errors[0];
					$errPtr->message;
					$this->respObj['status'] = false;
					$this->respObj['message'] = $errPtr->message; 
				}
			}
		}

	}

	protected function yieldResponse() {
		header('Content-Type: application/json');
		echo json_encode($this->respObj);
	}
}

$mailObj = new sendemail;


?>
