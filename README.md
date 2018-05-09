# Commerce Resource Store

![Module configuration](https://raw.githubusercontent.com/poconosewandvac/Commerce_ResourceStore/master/core/components/commerce_resourcestore/docs/images/config-screenshot.png)

Stores resources with selected delivery types in a user's extended field (configurable). Requires Modmore's Commerce to use https://www.modmore.com/commerce/.

## Use Case

With Commerce 0.11 and the UserGroupShipment module, you can sell access to resource groups. Using this module, you can log the target field (resource id) of products in a user's extended field to access it later. This can be used to create a dashboard of products the customer has purchased.

For example, you can display all saved resource ids using a simple snippet such as this:

```php
$userResources = $modx->getUser()
    ->getOne('Profile')
    ->get('extended')["resource_store"];
    
return implode(",", $userResources);
```

Then calling it with pdoResources (where UserResources is the snippet):

```
[[!pdoResources?
    &parents=`0`
    &resources=`[[!UserResources]]`
]]
```

## Installation

Download the transport package from releases or download from [modx.com in package manager](https://modx.com/extras/package/commerce_resourcestore).

## Compatibility 

Requires Commerce 0.11 or above due to the usage of `SelectMultipleField` in the module's configuration fields. If compatibility with versions before 0.11 is required, you could change it to SelectField instead, however you will only be able to choose 1 delivery type to use this module with.
