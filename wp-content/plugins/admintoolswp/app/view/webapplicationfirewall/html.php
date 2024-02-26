<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\WebApplicationFirewall;

use Akeeba\AdminTools\Admin\Helper\ServerTechnology;
use Akeeba\AdminTools\Library\Input\Input;

defined('ADMINTOOLSINC') or die;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
    public $hasHtaccess = false;

    public function __construct(Input $input)
    {
        parent::__construct($input);

        $this->hasHtaccess = ServerTechnology::isHtaccessSupported();
    }
}
