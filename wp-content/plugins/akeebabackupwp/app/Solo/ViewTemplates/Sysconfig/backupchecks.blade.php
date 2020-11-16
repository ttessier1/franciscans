<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Awf\Text\Text;
use Solo\Helper\Escape;
use Solo\Helper\FEFSelect;

defined('_AKEEBA') or die();

/** @var \Solo\View\Sysconfig\Html $this */

$config = $this->getContainer()->appConfig;

/**
 * Remember to update wpcli/Command/Sysconfig.php in the WordPress application whenever this file changes.
 */
?>
<div class="akeeba-form-group">
    <label for="failure_timeout">
		@lang('COM_AKEEBA_CONFIG_FAILURE_TIMEOUT_LABEL')
    </label>
    <input type="text" name="options[failure_timeout]" id="failure_timeout"
           placeholder="@lang('COM_AKEEBA_CONFIG_FAILURE_TIMEOUT_LABEL')"
           value="{{ $config->get('options.failure_timeout', 180) }}">
    <p class="akeeba-help-text">
		@lang('COM_AKEEBA_CONFIG_FAILURE_TIMEOUT_DESC')
    </p>
</div>

<div class="akeeba-form-group">
    <label for="failure_email_address">
		@lang('COM_AKEEBA_CONFIG_FAILURE_EMAILADDRESS_LABEL')
    </label>
    <input type="text" name="options[failure_email_address]" id="failure_email_address"
           placeholder="@lang('COM_AKEEBA_CONFIG_FAILURE_EMAILADDRESS_LABEL')"
           value="{{ $config->get('options.failure_email_address') }}">
    <p class="akeeba-help-text">
		@lang('COM_AKEEBA_CONFIG_FAILURE_EMAILADDRESS_DESC')
    </p>
</div>

<div class="akeeba-form-group">
    <label for="failure_email_subject">
		@lang('COM_AKEEBA_CONFIG_FAILURE_EMAILSUBJECT_LABEL')
    </label>
    <input type="text" name="options[failure_email_subject]" id="failure_email_subject"
           placeholder="@lang('COM_AKEEBA_CONFIG_FAILURE_EMAILSUBJECT_LABEL')"
           value="{{ $config->get('options.failure_email_subject') }}">
    <p class="akeeba-help-text">
		@lang('COM_AKEEBA_CONFIG_FAILURE_EMAILSUBJECT_DESC')
    </p>
</div>

<div class="akeeba-form-group">
    <label for="failure_email_body">
		@lang('COM_AKEEBA_CONFIG_FAILURE_EMAILBODY_LABEL')
    </label>
    <textarea type="text" name="options[failure_email_body]" id="failure_email_body"
              placeholder="@lang('COM_AKEEBA_CONFIG_FAILURE_EMAILBODY_LABEL')"
              rows="15">{{ $config->get('options.failure_email_body') }}</textarea>
    <p class="akeeba-help-text">
		@lang('COM_AKEEBA_CONFIG_FAILURE_EMAILBODY_DESC')
    </p>
</div>
