<?php

class SecureUpdateServer extends Wpup_UpdateServer {
	
	protected $licenseServer;
 
	public function __construct($serverUrl = NULL, $licenseServer = NULL) {
		parent::__construct($serverUrl);
		$this->licenseServer = $licenseServer;
	}
 
	protected function initRequest($query = null, $headers = null) {
		$request = parent::initRequest($query, $headers);
 
		//Load the license, if any.
		$license = null;
		if ( $request->param('license_key') ) {
			$result = $this->licenseServer->verifyLicenseExists(
				$request->slug,
				$request->param('license_key')
			);
			if ( is_wp_error($result) ) {
				//If the license doesn't exist, we'll output an invalid dummy license.
				$license = new Wslm_ProductLicense(array(
					'status' => $result->get_error_code(),
					'error' => array(
						'code' => $result->get_error_code(),
						'message' => $result->get_error_message(),
					),
				));
			} else {
				$license = $result;
			}
		}
 
		$request->license = $license;
		return $request;
	}
 
	protected function filterMetadata($meta, $request) {
		$meta = parent::filterMetadata($meta, $request);
 
		//Include license information in the update metadata. This saves an HTTP request
		//or two since the plugin doesn't need to explicitly fetch license details.
		$license = $request->license;
		if ( $license !== null ) {
			$meta['license'] = $this->licenseServer->prepareLicenseForOutput($license);
		}
 
		//Only include the download URL if the license is valid.
		if ( $license && $license->isValid() ) {
			//Append the license key or to the download URL.
			$args = array( 'license_key' => $request->param('license_key') );
			$meta['download_url'] = self::addQueryArg($args, $meta['download_url']);
		} else {
			//No license = no download link.
			unset($meta['download_url']);
		}
 
		return $meta;
	}
 
	protected function checkAuthorization($request) {
		parent::checkAuthorization($request);
 
		//Prevent download if the user doesn't have a valid license.
		$license = $request->license;
		if ( $request->action === 'download' && ! ($license && $license->isValid()) ) {
			if ( !isset($license) ) {
				$message = 'You must provide a license key to download this plugin.';
			} else {
				$error = $license->get('error');
				$message = isset($error) ? $error : 'Sorry, your license is not valid.';
			}
			$this->exitWithError($message, 403);
		}
	}
}

?>