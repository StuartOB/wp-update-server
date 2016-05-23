<?php

class SecureUpdateServer extends Wpup_UpdateServer {
 
	public function __construct( $serverUrl = NULL ) {
		
		parent::__construct($serverUrl);
	}
 
	protected function filterMetadata( $meta, $request ) {
		$meta = parent::filterMetadata( $meta, $request );
		
		// Only include the download URL if the license is valid.
		if (
				! isset( $request->query['license_key'] ) ||
				! $this->checkLicence( $request->query['license_key'] )
			) {
			
			// No license = no download link.
			unset( $meta['download_url'] );
		}
		else {
			
			// Append the license key or to the download URL.
			$args = array( 'license_key' => $request->param( 'license_key' ) );
			$meta['download_url'] = self::addQueryArg( $args, $meta['download_url'] );
		}
 
		return $meta;
	}
 
	protected function checkAuthorization($request) {
		parent::checkAuthorization($request);
		
		// Prevent download if the user doesn't have a valid license.
		if (
				$request->action === 'download' &&
				(
					! isset( $request->query['license_key'] ) ||
					! $this->checkLicence( $request->query['license_key'] )
				)
			) {
			
			if ( ! isset( $request->query['license_key'] ) ) {
				
				$message = 'You must provide a license key to download this plugin.';
			}
			else {
				
				$message = 'Sorry, your license is not valid.';
			}
			
			$this->exitWithError( $message, 403 );
		}
	}
	
	protected function checkLicence( $licence_key ) {
		
		// $licence_check = Envato::verifyPurchase( $userName, $apiKey , $purchaseCode, $itemId = false );
		// $licence_check = Envato::verifyPurchase( 'cxThemes', 'mq1x88c37pyi8jhqc1xnzqje6y6h3a6f', $licence_key );
		$licence_check = ( 'f128a5a1-5a1c-4e4e-82ec-a4856614c0b2' == $licence_key );
		
		if ( $licence_check ) {
			return $licence_key;
		}
		else {
			return FALSE;
		}
	}
}

?>