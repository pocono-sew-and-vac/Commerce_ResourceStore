<?php
namespace PoconoSewVac\Resourcestore\Modules;
use modmore\Commerce\Modules\BaseModule;
use Symfony\Component\EventDispatcher\EventDispatcher;
use modmore\Commerce\Admin\Widgets\Form\TextField;
use modmore\Commerce\Admin\Widgets\Form\SelectMultipleField;
use modmore\Commerce\Admin\Widgets\Form\Validation\Required;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

class Resourcestore extends BaseModule {
    private $user;
    private $items;

    public function getName()
    {
        $this->adapter->loadLexicon('commerce_resourcestore:default');
        return $this->adapter->lexicon('commerce_resourcestore');
    }

    public function getAuthor()
    {
        return 'Tony Klapatch';
    }

    public function getDescription()
    {
        return $this->adapter->lexicon('commerce_resourcestore.description');
    }

    /**
    * Gets all commerce delivery types and puts them in value - label array
    * @return array
    */
    public function getDeliveryTypes() {
        $query = $this->adapter->newQuery('comDeliveryType');
        $comDeliveryTypes = $this->adapter->getCollection('comDeliveryType', $query);
        
        $deliveryTypes = [];
        foreach ($comDeliveryTypes as $deliveryType) {
            $deliveryTypes[] = ['value' => $deliveryType->get('id'), 'label' => $deliveryType->get('name')];
        }

        return $deliveryTypes;
    }

    /**
    * Returns MODX user
    * @return user
    */
    public function getUser() {
        if (!isset($this->user)) {
            $this->user = $this->adapter->getUser();
        }

        return $this->user;
    }

    /**
    * Returns true if user is logged in.
    * @return bool
    */
    public function isLoggedIn() {
        return $this->getUser() ? true : false;
    }

    /**
    * Checks the contents of the orders
    * @param OrderItem event
    * @return void
    */
    public function checkOrderItems(\modmore\Commerce\Events\Payment $event)
    {
        if ($this->isLoggedIn()) {
        	$items = $event->getOrder()->getItems();
            $deliveryTypes = $this->getConfig('delivery_types');

        	foreach ($items as $item) {
                if (in_array($item->get('delivery_type'), $deliveryTypes)) {
                    // Get the target of the item from comProduct
                    $query = $this->adapter->newQuery('comProduct');
                    $query->where([
                        'id' => $item->get('product')
                    ]);
                    $product = $this->adapter->getObject('comProduct', $query);
                    
                    if ($product->get('target') !== 0)
                        $this->addToUser($product->get('target'));
                }
        	}
        }
    }

    /**
    * Checks the contents of the orders
    * @param resource id
    * @return void
    */
    public function addToUser($resource) {
        // Get extended field off given key
        $key = $this->getConfig('extended_key');
        $profile = $this->getUser()->getOne('Profile');
        $extended = $profile->get('extended');

        // Prevent duplicates
        if (!in_array($resource, $extended[$key])) {
            $extended[$key][] = $resource;
        }

        // Save to profile
        $profile->set('extended', $extended);
        $profile->save();
    }

    public function initialize(EventDispatcher $dispatcher)
    {
        // Load our lexicon
        $this->adapter->loadLexicon('commerce_resourcestore:default');

        // Run module after payment for order is received.
        $dispatcher->addListener(\Commerce::EVENT_ORDER_PAYMENT_RECEIVED, array($this, 'checkOrderItems'));
		
        // For quick testing
        //$dispatcher->addListener(\Commerce::EVENT_ORDERITEM_UPDATED, array($this, 'checkOrderItems'));
    }

    public function getModuleConfiguration(\comModule $module)
    {
        $fields = [];

        $fields[] = new SelectMultipleField($this->commerce, [
            'name' => 'properties[delivery_types]',
            'label' => $this->adapter->lexicon('commerce_resourcestore.delivery_types_label'),
            'options' => $this->getDeliveryTypes(),
            'description' => $this->adapter->lexicon('commerce_resourcestore.delivery_types_description'),
            'value' => $module->getProperty('delivery_types')
        ]);

        $fields[] = new TextField($this->commerce, [
            'name' => 'properties[extended_key]',
            'label' => $this->adapter->lexicon('commerce_resourcestore.extended_field_label'),
            'description' => $this->adapter->lexicon('commerce_resourcestore.extended_field_description'),
            'default' => 'resource_store',
            'validation' => [
                new Required()
            ],
            'value' => $module->getProperty('extended_key')
        ]);

        return $fields;
    }
}
