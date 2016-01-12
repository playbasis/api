<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Utility extends CI_Model
{
	public function getEventMessage($eventType, $amount = 'some', $pointName = 'points', $badgeName = 'a', $newLevel = '', $objectiveName = '', $goodsName = '')
	{
		switch($eventType)
		{
		case 'badge':
			return "earned $badgeName badge";
		case 'point':
			return "earned $amount $pointName";
		case 'level':
			return ($newLevel) ? "is now level $newLevel" : 'gained a level';
		case 'login':
			return 'logged in';
		case 'logout':
			return 'logged out';
		case 'objective':
			return 'completed an objective "'.$objectiveName.'"';
        case 'goods':
            return "redeem $goodsName";
		default:
			return 'did a thing';
		}
	}

	public function elapsed_time($key = "default") {
		static $last = array();
		$now = microtime(true);
		$ret = null;
		if (!array_key_exists($key, $last)) $last[$key] = null;
		if ($last[$key] != null) $ret = $now - $last[$key];
		$last[$key] = $now;
		return $ret;
	}

	public function url_exists($url, $prefix='') {
		if (substr($url, 0, 4) != 'http') $url = $prefix.$url;
		$file_headers = @get_headers($url);
		log_message('debug', 'url = '.print_r($url, true).', header = '.print_r($file_headers[0], true));
		return strpos($file_headers[0], ' 20') || strpos($file_headers[0], ' 30');
	}

	public function save_dir($dir, $mode=0755) {
		if (!is_dir($dir)) {
			mkdir($dir, $mode, true);
		}
	}

	public function save_file($dir, $file, $content, $mode=0755) {
		$this->save_dir($dir, $mode);
		file_put_contents("$dir/$file", $content);
	}

	/* http://stackoverflow.com/questions/19083175/generate-random-string-in-php-for-file-name */
	public function random_string($length) {
		$key = '';
		$keys = array_merge(range(0, 9), range('a', 'z'));
		for ($i = 0; $i < $length; $i++) {
			$key .= $keys[array_rand($keys)];
		}
		return $key;
	}

	/* require: $this->load->library('amazon_ses'); */
	public function email($from, $to, $subject, $message, $message_alt=null, $attachments=array()) {
        $message_response = array();
        if (is_array($to)) foreach($to as $email){
            $message_response[] = $this->_email(array(
                'from' => $from,
                'to' => trim($email),
                'subject' => $subject,
                'message' => $message,
                'message_alt' => $message_alt,
                'attachment' => $attachments,
            ));
            sleep(1);
        } else {
            $message_response = $this->_email(array(
                'from' => $from,
                'to' => trim($to),
                'subject' => $subject,
                'message' => $message,
                'message_alt' => $message_alt,
                'attachment' => $attachments,
            ));
        }
        return $message_response;
	}

	/* require: $this->load->library('amazon_ses'); */
	public function email_bcc($from, $bcc, $subject, $message, $message_alt=null, $attachments=array()) {
        $message_response = array();
        if (is_array($bcc)) foreach($bcc as $email){
            $message_response[] =  $this->_email(array(
                'from' => $from,
                'bcc' => trim($email),
                'subject' => $subject,
                'message' => $message,
                'message_alt' => $message_alt,
                'attachment' => $attachments,
            ));
            sleep(1);
        } else {
            $message_response =  $this->_email(array(
                'from' => $from,
                'bcc' => trim($bcc),
                'subject' => $subject,
                'message' => $message,
                'message_alt' => $message_alt,
                'attachment' => $attachments,
            ));
        }
        return $message_response;
	}

	/* require: $this->load->library('amazon_ses'); */
	public function _email($data) {
		if (!is_array($data)) return null; // error
		foreach ($data as $key => $value) {
			switch ($key) {
			case 'from':        $this->amazon_ses->from($value); break;
			case 'to':          $this->amazon_ses->to($value); break;
			case 'cc':          $this->amazon_ses->cc($value); break;
			case 'bcc':         $this->amazon_ses->bcc($value); break;
			case 'subject':     $this->amazon_ses->subject($value); break;
			case 'message':     $this->amazon_ses->message($value); break;
			case 'message_alt': $this->amazon_ses->message_alt($value); break;
			case 'attachment':  $this->amazon_ses->attachment($value); break;
			default: break;
			}
		}
		$this->amazon_ses->debug(EMAIL_DEBUG_MODE);
		$response = $this->amazon_ses->send();
		log_message('info', 'response = '.$response);
		return $response;
	}

	/* http://mpdf1.com/manual/index.php?tid=125 */
	public function html2mpdf($html, $output=false) {
		require_once(APPPATH.'/libraries/mpdf/mpdf.php');
		$mpdf = new mPDF('s','A4','','',25,15,21,22,10,10);
		$mpdf->WriteHTML($html);
		return $output ? $mpdf->Output('', 'S') : null;
	}

	public function find_diff_in_days($from, $to) {
		return intval($this->find_diff_in_fmt($from, $to, '%r%a'));
	}

	public function find_diff_in_fmt($from, $to, $fmt) {
		$_from = new DateTime(date("Y-m-d", $from));
		$_to = new DateTime(date("Y-m-d", $to));
		$interval = $_from->diff($_to);
		return $interval->format($fmt);
	}

	public function replace_template_vars($template, $data) {
		foreach (array('first_name', 'last_name', 'cl_player_id', 'email', 'phone_number', 'code',
				'first_name-2', 'last_name-2', 'cl_player_id-2', 'email-2', 'phone_number-2', 'code-2',
				'coupon') as $var) {
			if (isset($data[$var])) $template = str_replace('{{'.$var.'}}', $data[$var], $template);
		}
		return $template;
	}

	public function var2file($f, $var) {
		$meta = stream_get_meta_data($f);
		$uri = $meta['uri'];
		fwrite($f, $var);
		return $uri;
	}
	public function pagination($page, $limit_per_page, $input = array()){
		if (!$limit_per_page || $limit_per_page <= 0) return $input;
		$return_array = array();
		$page = $page < 1 ? 1 : $page; // page is based 1
		$start_page_element = ($page-1)*$limit_per_page;
		$end_page_element = $start_page_element+ $limit_per_page;
		if (is_array($input))foreach ($input as $key => $value){
			if ($key <  $end_page_element && $key >= $start_page_element ){
				array_push($return_array,$value);
			}
		}
		return $return_array;
	}
}
?>