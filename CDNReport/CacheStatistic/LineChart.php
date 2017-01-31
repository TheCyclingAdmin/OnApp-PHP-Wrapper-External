<?php
/**
 *
 * @category    API wrapper
 * @package     OnApp
 * @author      Ivan Gavryliuk
 * @copyright   © 2017 OnApp
 * @link        http://www.onapp.com/
 * @see         OnApp
 */

/**
 * Managing LineChart
 *
 * The OnApp_CDNReport_CacheStatistic_LineChart class uses the following basic methods:
 *
 * For full fields reference and curl request details visit: ( http://help.onapp.com/manual.php?m=2 )
 */
class OnApp_CDNReport_CacheStatistic_LineChart extends OnApp {
    /**
     * API Fields description
     *
     * @param string|float $version OnApp API version
     * @param string $className current class' name
     *
     * @return array
     */
    public function initFields( $version = null, $className = '' ) {
        switch ( $version ) {
            case 5.3:
                $this->fields = array(
                    'time'     => array(
                        ONAPP_FIELD_MAP  => '_time',
                        ONAPP_FIELD_TYPE => 'datetime',
                    ),
                    'cached'   => array(
                        ONAPP_FIELD_MAP  => '_cached',
                        ONAPP_FIELD_TYPE => 'float',
                    ),
                    'uncached' => array(
                        ONAPP_FIELD_MAP  => '_uncached',
                        ONAPP_FIELD_TYPE => 'float',
                    ),
                    'hit'      => array(
                        ONAPP_FIELD_MAP  => '_hit',
                        ONAPP_FIELD_TYPE => 'float',
                    ),
                    'miss'     => array(
                        ONAPP_FIELD_MAP  => '_miss',
                        ONAPP_FIELD_TYPE => 'float',
                    ),
                    'gb'       => array(
                        ONAPP_FIELD_MAP  => '_gb',
                        ONAPP_FIELD_TYPE => 'float',
                    ),
                );
                break;
        }
        parent::initFields( $version, __CLASS__ );

        return $this->fields;
    }
}