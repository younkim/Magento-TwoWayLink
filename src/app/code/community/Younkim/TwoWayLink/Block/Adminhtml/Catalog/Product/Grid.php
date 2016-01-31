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

class Younkim_TwoWayLink_Block_Adminhtml_Catalog_Product_Grid
    extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
    /**
     * Mass actions for the product-linking.
     */
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

        if (!Mage::helper('twowaylink')->isEnabled()) {
            return;
        }

        $this->getMassactionBlock()->addItem(
            'union-link',
            array(
                'label'=> Mage::helper('catalog')->__('Related Union-Link'),
                'url'  => $this->getUrl('*/*/massUnionRelatedLink'),
                // 'confirm' => Mage::helper('catalog')->__('Are you sure?')
            )
        );

        $this->getMassactionBlock()->addItem(
            'join-link',
            array(
                'label'=> Mage::helper('catalog')->__('Related Join-Link'),
                'url'  => $this->getUrl('*/*/massJoinRelatedLink'),
                // 'confirm' => Mage::helper('catalog')->__('Are you sure?')
            )
        );

        return $this;
    }
}

