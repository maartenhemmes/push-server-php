<?php 
/*
 |--------------------------------------------------------------------------
 | Send pushnotifications to Android and iOS
 |--------------------------------------------------------------------------
 |
 | Below you will find a PHP script to send pushnotifications to both Android and iOS devices.
 | The script requires you to add you own API keys and private keys
 | For support email info@hemmes.it
 |
 */


//enable device
//$deviceType = 'ios';
$deviceType = 'android';

//define device
if($deviceType == 'ios')
	{
		send_ios();
	}

if($deviceType == 'android')
	{
		send_android();
	}

function send_android()
	{
		// API access key from Google API's Console.
		// Note that you have to use Key for server applications and you have to define the IP of the server
		// A public access key won't work
		define('API_ACCESS_KEY', 'xxxx');
						
		//DeviceID (this is the ID that the device pushes to the API)
		$registrationIds = array('xxxx'); //<<-- Android Registration ID
			
		// prep the bundle
		$msg = array
		(
				'message'       => 'There is a new job available',
				'title'         => 'New Job',
				'subtitle'      => 'This is a subtitle. subtitle',
				'tickerText'    => 'Ticker text here...Ticker text here...Ticker text here',
				'vibrate'       => 1,
				'sound'         => 1,
				'largeIcon'     => 'large_icon',
				'smallIcon'     => 'small_icon'
		);
			
		$fields = array
		(
				'registration_ids'      => $registrationIds,
				'data'                  => $msg
		);
			
		$headers = array
		(
				'Authorization: key=' . API_ACCESS_KEY,
				'Content-Type: application/json'
		);
			
		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
		$result = curl_exec($ch );
		curl_close( $ch );
			
		return $result;
	}

function send_ios()
	{	
			//Apple makes a strict difference between testing and production (other than Android). 
			//This means you need two keys.
		
			//Testing
			//$deviceToken = 'e71c6586f1cf36771d60849853e42d847b8073124ef763a481e3df6ed6526066';
	
			//Production
			$deviceToken = 'xxxxx';
			// Put your device token here (without spaces):
	
			// Put your private key's passphrase here:
			$passphrase = 'xxxx';
	
			// Put your alert message here:
			$message = 'This message is awesome!!!';
	
			////////////////////////////////////////////////////////////////////////////////
	
			$ctx = stream_context_create();
			stream_context_set_option($ctx, 'ssl', 'local_cert', 'production_keys/Live.pem');
			//stream_context_set_option($ctx, 'ssl', 'local_cert', 'testing_keys/Testing.pem');
	
			stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
	
			// Open a connection to the APNS server
			$fp = stream_socket_client(
					'ssl://gateway.push.apple.com:2195', $err,
					$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
	
			//TESTING
			/*$fp = stream_socket_client(
					'ssl://gateway.sandbox.push.apple.com:2195', $err,
					$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
			*/ 
			
			
			if (!$fp)
				exit("Failed to connect: $err $errstr" . PHP_EOL);
	
			echo 'Connected to APNS' . PHP_EOL;
	
			// Create the payload body
			$body['aps'] = array(
					'alert' => $message,
					'sound' => 'default',
					'badge' => 1
			);
	
			// Encode the payload as JSON
			$payload = json_encode($body);
	
			// Build the binary notification
			$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
	
			// Send it to the server
			$result = fwrite($fp, $msg, strlen($msg));
	
			if (!$result)
				echo 'Message not delivered' . PHP_EOL;
			else
				echo 'Message successfully delivered' . PHP_EOL;
	
			// Close the connection to the server
			fclose($fp);
	}
?>