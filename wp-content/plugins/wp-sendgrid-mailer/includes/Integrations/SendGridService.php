<?php
/**
 * WP SendGrid Mailer Plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace WPMailPlus\Integrations;

use WPMailPlus\BaseController;

use WPMailPlus\EmailService;

use SendGrid;

class SendGridService implements EmailService
{
	/**
	 * Retrieve token/key needed for Service
	 * @return mixed
	 */
	public function get_token()
	{
		$service_info = get_option('_wp_mailplus_service_info');
		return $service_info['sendgrid_api_key'];
	}

	/**
	 * Send Mail
	 * @param string $to
	 * @param string $subject
	 * @param string $message
	 * @param string $headers
	 * @param array $attachments
	 */
	public function send_mail($to, $subject, $message, $headers, $attachments = '')
	{
		$api_key = $this->get_token();
		$from_info = get_option('_wp_mailplus_from_info');
		$from = new SendGrid\Email($from_info['from_name'], $from_info['from_email']);
		$mail_to = new SendGrid\Email(null, $to);
		$content = new SendGrid\Content("text/html", $message);
		$mail = new SendGrid\Mail($from, $subject, $mail_to, $content);
		if(!is_array($attachments) && !empty($attachments)) {
			$attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
		}

		if(!empty($attachments))    {
			foreach($attachments as $single_attachment) {
				$attachment = $this->prepareAttachment($single_attachment);
				if($attachment)
					$mail->addAttachment($attachment);

			}
		}

		$sendGrid = new \SendGrid($api_key);
		$email_from = BaseController::prepare_from_email($from_info['from_name'], $from_info['from_email']);
		$log_data = array('email_from' => $email_from,
			'email_to' => $to,
			'email_service' => 'Sendgrid',
			'email_subject' => $subject,
			'status' => 'Success',
			'message' => 'Mail sent successfully'
		);
		$response = $sendGrid->client->mail()->send()->post($mail);
		$status_code = $response->statusCode();
		if($status_code != 202) {
			$response_body = json_decode($response->body());
			if($response_body->errors)  {
				$error_count = count($response_body->errors);
				if($error_count > 0)    {
					foreach($response_body->errors as $error_key => $error_info)    {
						$log_data['message'] = $log_data['message'] . ' ' . $error_info->message;
					}
					$log_data['status'] = 'Failed';
					$log_data['message'] = trim($log_data['message']);
				}
			}
		}
		BaseController::addLog($log_data);
	}

	/**
	 * Prepare attachment object
	 * @param $attachment_path
	 * @return SendGrid\Attachment
	 */
	public function prepareAttachment($attachment_path)
	{
		$file_info = pathinfo($attachment_path);
		$attachment = new SendGrid\Attachment();
		$attachment->setContent(base64_encode(file_get_contents($attachment_path, FILE_USE_INCLUDE_PATH)));
		$attachment->setFilename($file_info['basename']);
		$attachment->setType(mime_content_type($attachment_path));
		$attachment->setDisposition('attachment');
		return $attachment;
	}
}
