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

class Younkim_TwoWayLink_Model_Observer
{
    /**
     * Product IDs that need to be removed in reverseLink().
     *
     * @var array
     */
    protected $_idsToRemove = array();

    /**
     * Product IDs that needs to have $_idsToRemove removed.
     *
     * @var array
     */
    protected $_removeFromIds = array();

    /**
     * Cache removed product ID from the related products.
     *
     * When saving links, getRelatedProductIds() does not return anything.
     * However, the getter method getRelatedLinkData() will return data because
     * the array data is set before the product entire save event.
     *
     * When removing links, getRelatedProductIds() returns all IDs, including
     * ones being removed because it will be processed after the product save.
     * getRelatedLinkData() returns only the IDs that will save because the
     * array data is set before the entire product save.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function cacheRemovedIds($observer)
    {
        if (!Mage::helper('twowaylink')->isEnabled()) {
            return;
        }
        $product = $observer->getProduct();
        if (!$product) {
            return;
        }

        $removedIds = array();
        $idsBefore = $product->getRelatedProductIds();
        $idsAfter = $product->getRelatedLinkData();

        $this->_idsToRemove = $this->_getIdsBeingRemoved($idsBefore, $idsAfter);
    }

    /**
     * Creates two-way linking of related products after a product save, if
     * link data is available.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function reverseLink($observer)
    {
        if (!Mage::helper('twowaylink')->isEnabled()) {
            return;
        }

        $product = $observer->getProduct();
        if (!$product) {
            return;
        }

        // Grab related product link data
        $relatedLinkData = $product->getRelatedLinkData();
        if (is_null($relatedLinkData)) {
            return;
        }
        $relatedLinkData[$product->getId()] = array('position' => null);


        // Add back removed IDs so they can be removed in the loop
        if (!is_null($this->_idsToRemove) && !empty($this->_idsToRemove)) {
            $relatedLinkData += $this->_idsToRemove;
        }
        $relatedLinkData2 = $relatedLinkData;

        if (!empty($relatedLinkData)) {
            // For each of the products being assigned to $product, associate
            // each of the products to another (every possible combination).
            // Remove if it's appropriate.
            foreach ($relatedLinkData as $id => $position) {
                foreach ($relatedLinkData2 as $id2 => $position2) {
                    if ($id === $id2) {
                        continue;
                    }
                    if (isset($this->_idsToRemove[$id])
                        || isset($this->_idsToRemove[$id2])
                    ) {
                        Mage::getSingleton('twowaylink/link_api')->removeLink(
                            $id,
                            $id2,
                            Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED
                            );
                    } else {
                        Mage::getSingleton('twowaylink/link_api')->createLink(
                            $id,
                            $id2,
                            Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED
                            );
                    }
                }
            }
        }
    }

    /**
     * Returns the product IDs that are removed.
     *
     * @param array $idsBefore
     * @param array $idsAfter Associative array
     *
     * @return array
     */
    protected function _getIdsBeingRemoved($idsBefore, $idsAfter)
    {
        $removed = array();

        foreach ($idsBefore as $id) {
            if (!isset($idsAfter[$id])) {
                $removed[$id] = array('position' => null);
            }
        }

        return $removed;
    }
}
