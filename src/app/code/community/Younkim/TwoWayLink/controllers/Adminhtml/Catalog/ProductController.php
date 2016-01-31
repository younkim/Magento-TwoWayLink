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
class Younkim_TwoWayLink_Adminhtml_Catalog_ProductController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Establish associations between all of the possible products, including
     * ones that are not directly related.
     */
    public function massUnionRelatedLinkAction()
    {
        $productIds = $this->getRequest()->getParam('product');
        if (!is_array($productIds)) {
            $this->_getSession()->addError($this->__('Please select product(s).'));
        } else {
            $allIds = array();

            // Get all possible links
            foreach ($productIds as $id) {
                $allIds = array_merge($allIds, Mage::getSingleton('twowaylink/link_api')
                    ->findLinkedIds($id, Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED));
            }
            $allIds = array_merge($allIds, $productIds);
            $allIds = array_unique($allIds);
            $allIds2 = $allIds;

            // Link each product to another, if applicable
            foreach ($allIds2 as $id) {
                foreach ($allIds as $id2) {
                    if ($id === $id2) {
                        continue;
                    }

                    // createLink() checks for existing link and skips if it exists
                    Mage::getSingleton('twowaylink/link_api')->createLink(
                        $id,
                        $id2,
                        Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED
                    );
                }
            }
        }

        return $this->_redirect('*/*/index');
    }

    /**
     * Establish associations only between the products selected and remove any
     * other previous links.
     */
    public function massJoinRelatedLinkAction()
    {
        $productIds = $this->getRequest()->getParam('product');
        if (!is_array($productIds)) {
            $this->_getSession()->addError($this->__('Please select product(s).'));

        } else {

            // Remove all links to (linked_product_id) $productIds
            Mage::getSingleton('twowaylink/link_api')->removeLinksTo(
                $productIds,
                Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED
            );

            // Now $productIds are disassociated with other groups of links
            // Safe to remove all of $productIds' links
            Mage::getSingleton('twowaylink/link_api')->removeAllLinks(
                $productIds,
                Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED
            );

            // Re-establish all links for the selected products
            $productIds2 = $productIds;
            foreach ($productIds2 as $id) {
                foreach ($productIds as $id2) {
                    if ($id === $id2) {
                        continue;
                    }

                    // createLink() checks for existing link and skips if it exists
                    Mage::getSingleton('twowaylink/link_api')->createLink(
                        $id,
                        $id2,
                        Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED
                    );
                }
            }
        }

        return $this->_redirect('*/*/index');
    }
}
