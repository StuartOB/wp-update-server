<?php

class SecureUpdateServer extends Wpup_UpdateServer {
	
	public function __construct( $serverUrl = NULL ) {
		parent::__construct($serverUrl);
	}
 
	/*protected function initRequest($query = null, $headers = null) {
		$request = parent::initRequest($query, $headers);
 
		// Load the license, if any.
		$license = null;
		if ( $request->param('license_key') ) {
			
			$result = $this->licenseServer->verifyLicenseExists(
				$request->slug,
				$request->param('license_key')
			);
			
			$licence = $this->checkLicence( $request->param('license_key') );
			
			if ( ! $licence ) {
				
				// If the license doesn't exist, we'll output an invalid dummy license.
				$license = new Wslm_ProductLicense( array(
					'status' => $result->get_error_code(),
					'error' => array(
						'code' => $result->get_error_code(),
						'message' => $result->get_error_message(),
					),
				));
				
				$licence = NULL;
			}
			else {
				$license = $result;
			}
		}
 
		$request->license = $license;
		return $request;
	}*/
 	
 	/**
 	 * Overide method - checks licence_key at time of UPDATE CHECK
 	 */
 	
	protected function filterMetadata($meta, $request) {
		$meta = parent::filterMetadata($meta, $request);
		
		// Don't do this check here - do it when they try to
		// update. So they get to see the notification, but
		// can't download it. It's more enticing.
		
		// Only include the download URL if the license is valid.
		if (
				isset( $request->query['license_key'] ) &&
				$this->checkLicence( $request->query['license_key'] )
			) {
			
			// Append the license key or to the download URL.
			$meta['download_url'] = self::addQueryArg(
				array( 'license_key' => $request->param( 'license_key' ) ),
				$meta['download_url']
			);
		}
		else {
			
			// No license = no download link.
			// unset( $meta['download_url'] );
		}
		
		return $meta;
	}
 	
 	/**
 	 * Overide method - checks licence_key at time of DOWNLOAD
 	 */
 	
	protected function checkAuthorization( $request ) {
		parent::checkAuthorization( $request );
		
		// Prevent download without a valid licence.
		if ( 'download' === $request->action ) {
			
			$message = FALSE;
			
			if ( ! isset( $request->query['license_key'] ) ) {
				
				$message = 'You must provide a license key to download this plugin.';
			}
			else if ( ! $this->checkLicence( $request->query['license_key'] ) ) {
				
				$message = 'Sorry, your license is not valid.';
			}
			
			if ( $message ) $this->exitWithError( $message, 403 );
		}
	}
	
	/**
	 * Custom method by Calyx
	 */
	protected function checkLicence( $licence_key ) {
		
		$licence_check = Envato::verifyPurchase( 'cxThemes', 'mq1x88c37pyi8jhqc1xnzqje6y6h3a6f', $licence_key );
		// $licence_check = FALSE; // Dubug: Force fail.
		// $licence_check = TRUE; // Dubug: Force success.
		
		if ( $licence_check ) {
			return $licence_key;
		}
		else {
			return FALSE;
		}
	}
}

?>