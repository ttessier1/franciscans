<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

use Akeeba\AdminTools\Admin\Helper\ConfigManager;
use Akeeba\AdminTools\Library\Mvc\Model\Model;

defined('ADMINTOOLSINC') or die;

class Salts extends Model
{
	/**
	 * Change all the salts inside WordPress. This causes all users to login again.
	 */
	public function changeSalts()
	{
		$configManager = ConfigManager::getInstance();

		$salts     = $this->generateSalts();
		$salt_keys = array('AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY',
						   'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT');

		foreach ($salt_keys as $key)
		{
			if (!$salts)
			{
				$salts = $this->generateSalts();
			}

			$value = "'".array_pop($salts)."'";
			$configManager->setOption($key, $value, false);
		}

		$configManager->updateFile();
	}

	/**
	 * Create random salts. Implements the same logic of WordPress
	 *
	 * @return array|\WP_Error
	 */
	private function generateSalts()
	{
		// Generate keys and salts using secure CSPRNG; fallback to API if enabled; further fallback to original wp_generate_password().
		try
		{
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
			$max   = strlen($chars) - 1;

			for ( $i = 0; $i < 8; $i++ )
			{
				$key = '';

				for ( $j = 0; $j < 64; $j++ )
				{
					$key .= substr( $chars, random_int( 0, $max ), 1 );
				}

				$secret_keys[] = $key;
			}
		}
		catch (\Exception $ex )
		{
			$secret_keys = wp_remote_get( 'https://api.wordpress.org/secret-key/1.1/salt/' );

			if (is_wp_error($secret_keys))
			{
				$secret_keys = array();

				for ($i = 0; $i < 8; $i++)
				{
					$secret_keys[] = wp_generate_password( 64, true, true );
				}
			}
			else
			{
				$secret_keys = explode( "\n", wp_remote_retrieve_body( $secret_keys ) );

				foreach ( $secret_keys as $k => $v )
				{
					$secret_keys[$k] = substr( $v, 28, 64 );
				}
			}
		}

		return $secret_keys;
	}
}
