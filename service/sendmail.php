<?php
require '../vendor/autoload.php';

class sendemail{
	//validate incoming data
	public $respObj = ['status'=>true, 'message'=>'Your email has been sent','detail'=>''];
	protected $defFailMsg = 'An error has occurred';
	protected $toemail, $fromemail, $subject, $message, $apiKey;
	protected $delaySecs = 300;
	public function __construct() {
		$this->apiKey = file_get_contents('/home/bcphpuser/backups/sendgrid.env');
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
			$this->respObj['detail'] = 'Subject is required';
			return;
		}
		$this->toemail = $_POST['toemail'];
		$this->fromemail = $_POST['fromemail'];
		$this->subject = $_POST['subject'];
		$this->message = $_POST['message'];
	}

	protected function submitMail() {
		$mail = new SendGrid\Mail();
		$email = new SendGrid\Email($this->fromemail, $this->fromemail);

		// from
		$mail->setFrom($email);

		//personalization object
		$personalization = new SendGrid\Personalization();

		// destination
		$email = new SendGrid\Email($this->toemail, $this->toemail);
		$personalization->addTo($email);

		//subject
		$personalization->setSubject($this->subject);

		// set delay
		$personalization->setSendAt(time() + intval($this->delaySecs));

		// custom args
		$personalization->addCustomArg("Developer", "Ben");
		$personalization->addCustomArg("Company", "FilterEasy");
		$personalization->addCustomArg("Email_Name", "test");
		$mail->addPersonalization($personalization);

		// content
		$content = new SendGrid\Content("text/plain", $this->message);
		$mail->addContent($content);

		// attachment
		if(is_uploaded_file($_FILES['attachment']['tmp_name'])){
			$attachment = new SendGrid\Attachment();
			$attachment->setContent(base64_encode(file_get_contents($_FILES['attachment']['tmp_name'])));
			$attachment->setType($_FILES['attachment']['type']);
			$attachment->setFilename($_FILES['attachment']['name']);
			$attachment->setDisposition("attachment");
			$attachment->setContentId("Attachment");
			$mail->addAttachment($attachment);
		}

		$sg = new \SendGrid($this->apiKey);

		// send mail
		$response = $sg->client->mail()->send()->post($mail);

		// handle any error response
		if (intval($response->statusCode()) >= 400 ) {
			$this->respObj['status'] = false;
			$this->respObj['message'] = 'An error was returned from the mailer service';
			$this->respObj['detail'] = $response->body();
		}
	}

	protected function yieldResponse() {
		header('Content-Type: application/json');
		echo json_encode($this->respObj);
	}
}

$mailObj = new sendemail;


?>
