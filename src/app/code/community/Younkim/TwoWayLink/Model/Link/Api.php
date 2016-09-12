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

class Younkim_TwoWayLink_Model_Link_Api
{
    /**
     * Remove the specified product link.
     *
     * @param int $parentId
     * @param int $childId
     *
     * @return void
     */
    public function removeLink($parentId, $childId, $type)
    {
        $link = Mage::getModel('catalog/product_link')->getCollection()
            ->addFieldToFilter('product_id', $parentId)
            ->addFieldToFilter('linked_product_id', $childId)
            ->addFieldToFilter(
                'link_type_id',
                $type
            )
            ->setCurPage(1)
            ->setPageSize(1)
            ->getFirstItem();

        try {
            $temp = $link->delete();
            return;

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')
                ->addError('Failed to remove links. Error: ' . $e->getMessage());
            Mage::helper('twowaylink')->log(
                "Failed to remove link between $id and "
                 . $link->getLinkedProductId()
            );
        }
    }

    /**
     * Add $idToLink to the related products list of product ID $id.
     *
     * @param int $id Reference product ID
     * @param int $idToLink ID to be associted to Product $id
     *
     * @see Mage_Catalog_Model_Resource_Product_Link::saveProductLinks()
     *
     * @return void
     */
    public function createLink($id, $idToLink, $type)
    {
        if ($id == $idToLink) { // Invalid arguments
            return;
        }

        // Check if $idToLink is associated to $id
        if (!$this->isLinked($id, $idToLink, $type)) {
            try {
                $mainTable = $this->getTableName('catalog/product_link');
                $positionTable = $this->getTableName('catalog/product_link_attribute_int');

                // Insert to main table
                $bind1 = array(
                    'product_id' => $id,
                    'linked_product_id' => $idToLink,
                    'link_type_id' => $type
                );
                $this->getWriteAdapter()->insert(
                    $mainTable,
                    $bind1
                );

                // Insert to position (_int) table
                $linkId = $this->getWriteAdapter()->lastInsertId($mainTable);
                $bind2 = array(
                    'product_link_attribute_id' => '1', // @see table catalog_product_link_type
                    'link_id' => $linkId,
                    'value' => '0'  // Default position
                );
                $this->getWriteAdapter()->insertOnDuplicate(
                    $positionTable,
                    $bind2,
                    array('value')
                );

                return;

            } catch (Exception $e) {
                Mage::helper('twowaylink')->log("Failed to link $idToLink to $id. Error: " . $e->getMessage());
            }
        }
    }

    /**
     * Returns all of the related product IDs or just designated one.
     *
     * @param int $id
     * @param int $linkedId Desginated target to find
     *
     * @return array
     */
    public function findLinkedIds($id, $type, $linkedId = null)
    {
        $linkedIds = array();
        $select = $this->getReadAdapter()->select()
            ->from(
                array('main_table' => $this->getTableName('catalog/product_link')),
                array('link_id', 'linked_product_id')
            )
            ->where('main_table.product_id = ?', $id)
            ->where('main_table.link_type_id = ?', $type);

        if (!is_null($linkedId)) {
            $select->where('main_table.linked_product_id = ?', $linkedId);
        }

        $results = $this->getReadAdapter()->fetchAll($select);

        foreach ($results as $link) {
            $linkedIds[] = $link['linked_product_id'];
        }

        return $linkedIds;
    }

    /**
     * Checks if $linkedId is associated to $id.
     *
     * @param int $id
     * @param int $linkedId
     *
     * @return bool
     */
    public function isLinked($id, $linkedId, $type)
    {
        $productId = $this->findLinkedIds($id, $type, $linkedId);

        if ($productId) {
            return true;
        }
        return false;
    }

    /**
     * Removes all links pointing to the given list of IDs.
     *
     * @param array $productIds
     *
     * @return void
     */
    public function removeLinksTo($productIds, $type)
    {
        $collection = Mage::getModel('catalog/product_link')->getCollection()
            ->addFieldToFilter('linked_product_id', array('in' => $productIds))
            ->addFieldToFilter('link_type_id', $type);

        foreach ($collection as $link) {
            try {
                $id = $link->getProductId();
                $linkedId = $link->getLinkedProductId();
                $link->delete();
            } catch (Exception $e) {
                Mage::helper('twowaylink')->log(
                    "Failed to remove link between $id and "
                     . $linkedId
                );
            }
        }
    }

    /**
     * Remove all of the related products of the given product ID and returns
     * all of the product IDs to which the given product ID was linked.
     *
     * @param int $ids
     *
     * @return array
     */
    public function removeAllLinks($productIds, $type)
    {
        $allIds = array();

        foreach ($productIds as $id) {
            $linkedIds = $this->findLinkedIds($id, $type);
            foreach ($linkedIds as $linkedId) {
                $allIds[] = $linkedId;
                $this->removeLink($id, $linkedId, $type);
            }
        }

        return array_unique($allIds);
    }

    /**
     * Returns the resource connection.
     *
     * @param str $type
     *
     * @return Varien_Db_Adapter_Interface
     */
    public function getReadAdapter()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    /**
     * Returns the resource connection.
     *
     * @param str $type
     *
     * @return Varien_Db_Adapter_Interface
     */
    public function getWriteAdapter()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    /**
     * Returns the table name.
     *
     * @param string $name
     *
     * @return string
     */
    public function getTableName($name)
    {
        return Mage::getSingleton('core/resource')->getTableName($name);
    }
}
