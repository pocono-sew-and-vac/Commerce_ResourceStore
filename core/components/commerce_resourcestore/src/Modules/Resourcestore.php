<?php
namespace ThirdParty\Resourcestore\Modules;
use modmore\Commerce\Modules\BaseModule;
use Symfony\Component\EventDispatcher\EventDispatcher;
use modmore\Commerce\Admin\Widgets\Form\SelectMultipleField as SelectMultipleField;
use modmore\Commerce\Admin\Widgets\Form\Validation\Required as Required;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

class Resourcestore extends BaseModule {

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

    // Fetch all delivery types and outputs array with value (id) and label (name)
    public function getDeliveryTypes() {
        $query = $this->adapter->newQuery('comDeliveryType');
        $comDeliveryTypes = $this->adapter->getCollection('comDeliveryType', $query);
        
        $deliveryTypes = [];
        foreach ($comDeliveryTypes as $deliveryType) {
            $deliveryTypes[] = ['value' => $deliveryType->get('id'), 'label' => $deliveryType->get('name')];
        }

        return $deliveryTypes;
    }

    public function checkOrder(\modmore\Commerce\Events\OrderItem $event)
    {
    	$items = $event->getOrder()->getItems();

    	foreach ($items as $item) {
            // Get the target of the item from comProduct
            $query = $this->adapter->newQuery('comProduct');
            $query->where([
                'id' => $item->get('product')
            ]);
            $product = $this->adapter->getObject('comProduct', $query);

            echo $product->get('target');
    	}
    }

    public function initialize(EventDispatcher $dispatcher)
    {
        // Load our lexicon
        $this->adapter->loadLexicon('commerce_resourcestore:default');

        // Run module after payment for order is received.
        $dispatcher->addListener(\Commerce::EVENT_ORDER_PAYMENT_RECEIVED, array($this, 'checkOrder'));
		
        // Used for quick testing
        //$dispatcher->addListener(\Commerce::EVENT_ORDERITEM_UPDATED, array($this, 'checkOrder'));
    }

    public function getModuleConfiguration(\comModule $module)
    {
        $fields = [];

        $fields[] = new SelectMultipleField($this->commerce, [
            'name' => 'properties[delivery_types]',
            'label' => 'Delivery Types',
            'options' => $this->getDeliveryTypes(),
            'description' => 'The delivery types you want to enable this module for.',
            'validation' => [
                new Required()
            ],
            'value' => $module->getProperty('delivery_types')
        ]);

        return $fields;
    }
}
