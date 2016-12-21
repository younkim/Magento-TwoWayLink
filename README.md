# Two-Way Related Product Link

## Features

This exntesion performs bi-directional linking of related products when such associations are made. Natively, Magento's related products associations are one-way.

For example, if Product B is associated as a related product to Product A, then this extension automatically associates Product A to Product B as well. This is called a _bi-directional link_. This is works only in the admin.

Additionally, mass link actions, _union-link_ and _join-link_, are available. A _union-link_ associates all of the products selected to each other, and any other products that are associated indirectly.

A _join-link_ associates only the explicitly selected products, and removes any other associations.


## Usage

Simply associate related products from the admin, and the bi-directional link will be made.

Additionally, union- and join-links can be made through the mass-action on the Manage Product page. 

Logging can be enabled for debugging.


## Installation

Copy the extension files to the appropriate directories and enable the extension from _System > Configuration > Catalog > Two-Way Relation_. It is disabled by default.

Alternatively, use the extension manager, `modman`.

```
cd /path/to/magento/
modman init
modman clone https://github.com/younkim/Magento-TwoWayLink.git
```

## Release Notes

### v1.2.1: 2016-09-12
- Removed test exception throw in link removal.

### v1.2.0: 2016-01-28
- Refactored code.

### v1.1.0: 2015-05-17
- Implemented mass-update options available from the admin product grid.
- Implemented mass-union and mass-join operations.

### v1.0.0: 2015-05-14
- Implemented two-way related product linking and removal.
- Works only from the Related Products tab in the admin.

## Notes

This feature works only in the admin.

