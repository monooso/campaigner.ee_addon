<?php

/**
 * String sanitization functions.
 *
 * @author		Stephen Lewis <stephen@experienceinternet.co.uk>
 * @copyright	Experience Internet
 * @package		EI
 */

if ( ! function_exists('sanitize_string'))
{
	
	/**
	 * Sanitises a string so that it doesn't trigger EE's "Disallowed characters" error.
	 *
	 * @param	string		$source		The string to sanitise.
	 * @return	string
	 */
	function sanitize_string($source = '')
	{
		return strtr(base64_encode(addslashes(gzcompress(serialize($source), 9))), '+=', '-_');
	}
	
}


if ( ! function_exists('desanitize_string'))
{
	
	/**
	 * "De-sanitises" a string that was previously sanitised using the sanitize_string function.
	 *
	 * @param	string		$encoded	The string to de-sanitise.
	 * @return	string
	 */
	function desanitize_string($encoded = '')
	{
		return unserialize(gzuncompress(stripslashes(base64_decode(strtr($encoded, '-_', '+=')))));
	}
	
}

/* End of file		: EI_sanitize_helper.php */
/* File location	: system/modules/campaigner/helpers/EI_sanitize_helper.php */
