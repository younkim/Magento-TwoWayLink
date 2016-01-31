<?php
/**
 * Bi-directional prouct association
 *
 * @category  Younkim
 * @package   Younkim_TwoWayLink
 * @author    Youn Kim <younwkim@gmail.com>
 * @author    Brad Traynham <btray77@gmail.com>
 * @copyright Copyright (c) 2016 Youn Kim (https://github.com/younkim)
 * @license   http://www.gnu.org/licenses/gpl-3.0.html
 */

class Younkim_TwoWayLink_Helper_Data
    extends Mage_Core_Helper_Abstract
{
    const XML_PATH_TWOWAYLINK_ENABLED = 'twowaylink/general/enabled';
    const XML_PATH_TWOWAYLINK_LOGGING = 'twowaylink/general/logging';

    /**
     * Log file name.
     *
     * @var str
     */
    protected $_logFile = 'younkim_twowaylink.log';

    /**
     * Writes extension messages to the log file.
     *
     * @param str $message
     */
    public function log($message)
    {
        if (Mage::getStoreConfig(self::XML_PATH_TWOWAYLINK_LOGGING)) {
            Mage::log($message, null, $this->_logFile);
        }
    }

    /**
     * Checks if extension is enabled in the system configuration.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_TWOWAYLINK_ENABLED);
    }
}
