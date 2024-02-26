<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Helper;

use Akeeba\AdminTools\Library\Html\Select as SelectHelper;
use Akeeba\AdminTools\Library\Utils\ArrayHelper;

defined('ADMINTOOLSINC') or die;

class Select
{
	protected static function genericlist($list, $name, $attribs, $selected, $idTag)
	{
		if (empty($attribs))
		{
			$attribs = null;
		}
		else
		{
			$temp = '';

			foreach ($attribs as $key => $value)
			{
				$temp .= $key . ' = "' . $value . '"';
			}
			$attribs = $temp;
		}

		return SelectHelper::genericList($list, $name, $attribs, 'value', 'text', $selected, $idTag);
	}

	public static function valuelist($options, $name, $attribs = null, $selected = null, $ignoreKey = false)
	{
		$list = [];

		foreach ($options as $k => $v)
		{
			if ($ignoreKey)
			{
				$k = $v;
			}

			$list[] = SelectHelper::option($k, $v);
		}

		return self::genericlist($list, $name, $attribs, $selected, $name);
	}

	public static function forcehttps($name, $attribs = null, $selected = null)
	{
		$options = [];

		$options[] = SelectHelper::option('-1', Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_FORCEHTTPS_IGNORE'));
		$options[] = SelectHelper::option('0', Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_FORCEHTTPS_NO'));
		$options[] = SelectHelper::option('1', Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_FORCEHTTPS_YES'));

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function booleanlist($name, $attribs = null, $selected = null, $showEmpty = true)
	{
		$options = [];

		if($showEmpty)
		{
			$options[] = SelectHelper::option('-1', '---');
		}

		$options[] = SelectHelper::option('0', Language::_('NO'));
		$options[] = SelectHelper::option('1', Language::_('YES'));

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function csrflist($name, $attribs = null, $selected = null)
	{
		$options = [
			SelectHelper::option('-1', '---'),
			SelectHelper::option('0', Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CSRFSHIELD_NO')),
			SelectHelper::option('1', Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CSRFSHIELD_BASIC')),
			SelectHelper::option('2', Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CSRFSHIELD_ADVANCED'))
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function autoroots($name, $attribs = null, $selected = null)
	{
		$options = [
			SelectHelper::option('-1', '---'),
			SelectHelper::option('0', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_AUTOROOT_OFF')),
			SelectHelper::option('1', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_AUTOROOT_STD')),
			SelectHelper::option('2', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_AUTOROOT_ALT'))
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function published($selected = null, $id = 'enabled', $attribs = [])
	{
		$options = [];

		$options[] = SelectHelper::option('', '- ' . Language::_('COM_ADMINTOOLS_LBL_COMMON_SELECTPUBLISHSTATE') . ' -');
		$options[] = SelectHelper::option(0, Language::_('COM_ADMINTOOLS_LBL_COMMON_UNPUBLISHED'));
		$options[] = SelectHelper::option(1, Language::_('COM_ADMINTOOLS_LBL_COMMON_PUBLISHED'));

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}

	/**
	 * Builds the whole list of reasons, it is useful when dealing with forms
	 *
	 * @return array
	 */
	public static function reasons_options()
	{
		$reasons = [
			'other', 'ipwl', 'ipbl', 'sqlishield', 'antispam',
			'geoblocking', 'rfishield', 'uploadshield',
			'httpbl', 'loginfailure', 'awayschedule', 'admindir',
			'nonewadmins', 'phpshield',
		];

		$options = [];

		foreach ($reasons as $reason)
		{
			$options[] = SelectHelper::option($reason, Language::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_' . strtoupper($reason)));
		}

		return $options;
	}

	public static function reasons($selected = null, $id = 'reason', $attribs = [])
	{
		$options = static::reasons_options();

		// Enable miscellaneous reasons, for use in email templates
		if (isset($attribs['misc']))
		{
			$options[] = SelectHelper::option('ipautoban', Language::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_IPAUTOBAN'));
			unset($attribs['misc']);
		}

		// Let's sort the list alphabetically
		ArrayHelper::sortObjects($options, 'text');

		if (isset($attribs['all']))
		{
			array_unshift($options, SelectHelper::option('all', Language::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_ALL')));
			unset($attribs['all']);
		}

		if (!isset($attribs['hideEmpty']))
		{
			array_unshift($options, SelectHelper::option('', '- ' . Language::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_SELECT') . ' -'));
		}
		else
		{
			unset($attribs['hideEmpty']);
		}

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}

	public static function wwwredirs($name, $attribs = null, $selected = null)
	{
		$options = [
			SelectHelper::option('-1', '---'),
			SelectHelper::option('0', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_WWWREDIR_NO')),
			SelectHelper::option('1', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_WWWREDIR_WWW')),
			SelectHelper::option('2', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_WWWREDIR_NONWWW'))
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function exptime($name, $attribs = null, $selected = null)
	{
		$options = [
			SelectHelper::option('0', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_EXPTIME_NO')),
			SelectHelper::option('1', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_EXPTIME_VARIES')),
			SelectHelper::option('2', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_EXPTIME_YEAR')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function perms($name, $attribs = null, $selected = null)
	{
		$rawperms = [0400, 0440, 0444, 0600, 0640, 0644, 0660, 0664, 0700, 0740, 0744, 0750, 0754, 0755, 0757, 0770, 0775, 0777];

		$options   = [];
		$options[] = SelectHelper::option('', '---');

		foreach ($rawperms as $perm)
		{
			$text      = decoct($perm);
			$options[] = SelectHelper::option('0' . $text, $text);
		}

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function trsfreqlist($name, $attribs = null, $selected = null)
	{
		$freqs = ['second', 'minute', 'hour', 'day'];

		$options   = [];
		$options[] = SelectHelper::option('', '---');

		foreach ($freqs as $freq)
		{
			$text      = Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_FREQ' . strtoupper($freq));
			$options[] = SelectHelper::option($freq, $text);
		}

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function freqlist($name, $attribs = null, $selected = null)
	{
		$freqs = ['hour', 'day', 'month'];

		$options   = [];
		$options[] = SelectHelper::option('', '---');

		foreach ($freqs as $freq)
		{
			$text      = Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_FREQ' . strtoupper($freq));
			$options[] = SelectHelper::option($freq, $text);
		}

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function httpschemes($name, $attribs = null, $selected = null)
	{
		$options = [
			SelectHelper::option('http', Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_IPLOOKUPSCHEME_HTTP')),
			SelectHelper::option('https', Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_IPLOOKUPSCHEME_HTTPS'))
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function scanresultstatus($name = '', $attribs = null, $selected = null)
	{
		$options = [
			SelectHelper::option('new', Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_NEW')),
			SelectHelper::option('suspicious', Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_SUSPICIOUS')),
			SelectHelper::option('modified', Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_MODIFIED')),
		];

		if($name)
		{
			$options = array_unshift($options, SelectHelper::option('', '- ' . Language::_('COM_ADMINTOOLS_LBL_COMMON_SELECTPUBLISHSTATE') . ' -'));

			return self::genericlist($options, $name, $attribs, $selected, $name);
		}
		else
		{
			return $options;
		}
	}

	public static function symlinks($name, $attribs = null, $selected = null)
	{
		$options = [
			SelectHelper::option('0', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_SYMLINKS_OFF')),
			SelectHelper::option('1', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_SYMLINKS_FOLLOW')),
			SelectHelper::option('2', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_SYMLINKS_IFOWNERMATCH')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function keepUrlParamsList($name = '', $attribs = null, $selected = null)
	{
		$options = [
			SelectHelper::option('', '- - -'),
			SelectHelper::option('0', Language::_('COM_ADMINTOOLS_REDIRECTION_KEEPURLPARAMS_LBL_OFF')),
			SelectHelper::option('1', Language::_('COM_ADMINTOOLS_REDIRECTION_KEEPURLPARAMS_LBL_ALL')),
			SelectHelper::option('2', Language::_('COM_ADMINTOOLS_REDIRECTION_KEEPURLPARAMS_LBL_ADD')),
		];

		if($name)
		{
			return self::genericlist($options, $name, $attribs, $selected, $name);
		}
		else
		{
			return $options;
		}
	}

	public static function httpVerbs($name = '', $attribs = null, $selected = null)
	{
		$options = [
			SelectHelper::option('', '- - -'),
			SelectHelper::option('GET', 'GET'),
			SelectHelper::option('POST', 'POST'),
			SelectHelper::option('PUT', 'PUT'),
			SelectHelper::option('DELETE', 'DELETE'),
			SelectHelper::option('HEAD', 'HEAD'),
			SelectHelper::option('TRACE', 'TRACE'),
		];

		if($name)
		{
			return self::genericlist($options, $name, $attribs, $selected, $name);
		}
		else
		{
			return $options;
		}
	}

	public static function queryParamType($name = '', $attribs = null, $selected = null)
	{
		$options = [
			SelectHelper::option('', '- - -'),
			SelectHelper::option('E', Language::_('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_QUERY_CONTENT_EXACT')),
			SelectHelper::option('P', Language::_('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_QUERY_CONTENT_PARTIAL')),
			SelectHelper::option('R', Language::_('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_QUERY_CONTENT_REGEX')),
		];

		if($name)
		{
			return self::genericlist($options, $name, $attribs, $selected, $name);
		}
		else
		{
			return $options;
		}
	}

	public static function referrerpolicy($name, $attribs = null, $selected = null)
	{
		$options = [
			SelectHelper::option('-1', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_DISABLED')),
			SelectHelper::option('', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_EMPTY')),
			SelectHelper::option('no-referrer', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_NOREF')),
			SelectHelper::option('no-referrer-when-downgrade', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_NOREF_DOWNGRADE')),
			SelectHelper::option('same-origin', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_SAMEORIGIN')),
			SelectHelper::option('origin', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_ORIGIN')),
			SelectHelper::option('strict-origin', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_STRICTORIGIN')),
			SelectHelper::option('origin-when-cross-origin', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_ORIGINCROSS')),
			SelectHelper::option('strict-origin-when-cross-origin', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_STRICTORIGINGCROSS')),
			SelectHelper::option('unsafe-url', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_UNSAFE')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function etagtype($name, $attribs = null, $selected = null)
	{
		$options = [
			SelectHelper::option('default', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_DEFAULT')),
			SelectHelper::option('full', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_FULL')),
			SelectHelper::option('sizetime', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_SIZETIME')),
			SelectHelper::option('size', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_SIZE')),
			SelectHelper::option('none', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_NONE')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function etagtypeIIS($name, $attribs = null, $selected = null)
	{
		$options = [
			SelectHelper::option('default', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_DEFAULT')),
			SelectHelper::option('none', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_NONE')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function cors($name, $attribs = null, $selected = null)
	{
		$options = [
			SelectHelper::option('-1', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_CORS_OPT_SAMEORIGIN')),
			SelectHelper::option('0', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_CORS_OPT_UNSET')),
			SelectHelper::option('2', Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_CORS_OPT_ENABLE')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	/**
	 * Drop down list of CSV delimiter preference
	 *
	 * @param   string  $name      The field's name
	 * @param   int     $selected  Pre-selected value
	 * @param   array   $attribs   Field attributes
	 *
	 * @return  string  The HTML of the drop-down
	 */
	public static function csvdelimiters($name = 'csvdelimiters', $selected = 1, $attribs = [])
	{
		$options   = [];
		$options[] = SelectHelper::option('1', 'abc, def');
		$options[] = SelectHelper::option('2', 'abc; def');
		$options[] = SelectHelper::option('3', '"abc"; "def"');
		$options[] = SelectHelper::option('-99', Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_DELIMITERS_CUSTOM'));

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function environment($name, $selected, $attribs = [])
	{
		$options   = [];
		$options[] = SelectHelper::option('apache-mod_php', 'Apache + mod_php');
		$options[] = SelectHelper::option('apache-suphp', 'Apache + suPHP');
		$options[] = SelectHelper::option('cgi', 'Apache + CGI/FastCGI');
		$options[] = SelectHelper::option('litespeed', 'LiteSpeed/lsapi');

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function minimumStability($name, $selected, $attribs = [])
	{
		$levels  = ['alpha', 'beta', 'rc', 'stable'];
		$options = [];

		foreach ($levels as $level)
		{
			$options[] = SelectHelper::option($level, Language::_('COM_ADMINTOOLS_PARAMS_MINSTABILITY_' . strtoupper($level)));
		}

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function timezones($name, $attribs = array(), $selected = '')
	{
		if (empty($attribs))
		{
			$attribs = null;
		}
		else
		{
			$temp = '';

			foreach ($attribs as $key => $value)
			{
				$temp .= $key . ' = "' . $value . '"';
			}

			$attribs = $temp;
		}

		// We're going to use WordPress builtin function for this
		$html = '<select id="'.$name.'" name="'.$name.'" '.$attribs.'>';
		$html .= 	wp_timezone_choice( $selected, get_user_locale() );
		$html .= '</select>';

		return $html;
	}

	public static function roles($name, $selected, array $attribs = [])
	{
		$options = [];
		$roles   = get_editable_roles();

		foreach ($roles as $key => $role)
		{
			$options[] = SelectHelper::option($key, $role['name']);
		}

		if (!isset($attribs['hideEmpty']))
		{
			array_unshift($options, SelectHelper::option('', '- ' . Language::_('COM_ADMINTOOLS_LBL_COMMON_ROLES_SELECT') . ' -'));
		}
		else
		{
			unset($attribs['hideEmpty']);
		}

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function error_reporting($name, $selected, array $attribs = [])
	{
		$options = [];

		$options[] = SelectHelper::option('', Language::_('COM_ADMINTOOLS_ERROR_REPORTING_DEFAULT'));
		$options[] = SelectHelper::option('none', Language::_('COM_ADMINTOOLS_ERROR_REPORTING_NONE'));
		$options[] = SelectHelper::option('errors', Language::_('COM_ADMINTOOLS_ERROR_REPORTING_ERRORS'));
		$options[] = SelectHelper::option('minimal', Language::_('COM_ADMINTOOLS_ERROR_REPORTING_MINIMAL'));
		$options[] = SelectHelper::option('full', Language::_('COM_ADMINTOOLS_ERROR_REPORTING_FULL'));
		$options[] = SelectHelper::option('developer', Language::_('COM_ADMINTOOLS_ERROR_REPORTING_DEVELOPER'));

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function logLevel($name, $selected, array $attribs = [])
	{
		$options = [];

		$options[] = SelectHelper::option(0, Language::_('COM_ADMINTOOLS_LBL_PARAMS_LOGLEVEL_NONE'));
		$options[] = SelectHelper::option(1, Language::_('COM_ADMINTOOLS_LBL_PARAMS_LOGLEVEL_ERROR'));
		$options[] = SelectHelper::option(2, Language::_('COM_ADMINTOOLS_LBL_PARAMS_LOGLEVEL_WARNING'));
		$options[] = SelectHelper::option(3, Language::_('COM_ADMINTOOLS_LBL_PARAMS_LOGLEVEL_INFO'));
		$options[] = SelectHelper::option(4, Language::_('COM_ADMINTOOLS_LBL_PARAMS_LOGLEVEL_DEBUG'));

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function post_revisions($name, $selected, array $attribs = [])
	{
		$options = [];

		$options[] = SelectHelper::option('default', Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_POSTREVISIONS_DEFAULT'));
		$options[] = SelectHelper::option('custom', Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_POSTREVISIONS_DISABLED'));

		for ($i = 1; $i <= 10; $i++)
		{
			$options[] = SelectHelper::option($i, $i);
		}

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	/**
	 * Creates a three state list to set Core Autoupdates value
	 *
	 * @param   string $name     Field name
	 * @param   string $selected Selected value
	 * @param   array  $attribs  Field attributes
	 *
	 * @return  string  The HTML of the field
	 */
	public static function core_autoupdates($name, $selected = null, $attribs = null)
	{
		if (empty($attribs))
		{
			$attribs = ['class' => 'akeeba-toggle'];
		}
		else
		{
			if (isset($attribs['class']))
			{
				$attribs['class'] .= ' akeeba-toggle';
			}
			else
			{
				$attribs['class'] = 'akeeba-toggle';
			}
		}

		$temp = '';

		foreach ($attribs as $key => $value)
		{
			$temp .= $key . ' = "' . $value . '"';
		}

		$attribs = $temp;

		$checked_1 = $selected == '0' ? 'checked ' : '';
		$checked_2 = $selected == '1' ? 'checked ' : '';
		$checked_3 = $selected == '2' ? 'checked ' : '';

		$html  = '<div '.$attribs.'>';
		$html .= 	'<input type="radio" class="" name="'.$name.'" '.$checked_1.'id="'.$name .'-1" value="0">';
		$html .=	'<label for="'.$name.'-1" class="red">'.Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_COREUPDATES_DISABLED').'</label>';
		$html .=	'<input type="radio" class="" name="'.$name.'" '.$checked_2.'id="'.$name .'-2" value="1">';
		$html .= 	'<label for="'.$name.'-2" class="primary">'.Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_COREUPDATES_MINOR').'</label>';
		$html .=	'<input type="radio" class="" name="'.$name.'" '.$checked_3.'id="'.$name .'-3" value="2">';
		$html .= 	'<label for="'.$name.'-3" class="orange">'.Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_COREUPDATES_MAJOR').'</label>';
		$html .= '</div>';

		return $html;
	}

	public static function custom_admin_actions($name, $selected = null, $attribs = null)
	{
		$options = [
			SelectHelper::option('1', Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ADMINLOGINDIR_ACTION_BLOCK')),
			SelectHelper::option('2', Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ADMINLOGINDIR_ACTION_HOME')),
			SelectHelper::option('3', Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ADMINLOGINDIR_ACTION_404')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}
}
