<?php 
/**
 * <ModuleClassName> => CustomerOrder
 * <FileName> => customerOrderController.php
 * Format expected: <ModuleClassName><FileName>ModuleFrontController
 */
class CustomerOrderCustomerModuleFrontController extends ModuleFrontController 
{

    public $auth = true; 
    public $guestAllowed = false; 
    

    public function initContent()
    {

        $customer_id = $this->context->customer->id;
        $admin_account = Configuration::get('CUSTOMERORDER_ADMINACCOUNT');
        if($customer_id == $admin_account)
        {
            parent::initContent();

            $customers = $this->getCustomers(); 
    
            $this->context->smarty->assign(array(
                'customers' => $customers,
            ));
    
            $this->setTemplate('module:customerOrder/views/templates/front/customerOrder.tpl');
            
        } else 
        {
            Tools::redirect('index.php');
        }
       
    }


    public function postProcess()
    {
        //login as selected customer 
            if((bool)Tools::getIsset('selected-customer') == true)
            {
                $customer_id = Tools::getValue('selected-customer');
                $this->loginCustomer($customer_id);
            }  
        
    }

    private function loginCustomer($customerId)
    {
        
        $id_customer = (int) Tools::getValue('id_customer'); 

        $customer = new Customer($customerId);
        if(Validate::isLoadedObject($customer))
        {
            
            Context::getContext()->updateCustomer($customer); 
            //Tools::redirect($this->context->link->getPageLink('index.php', true));
            $this->context->controller->success[] = $this->l('Zalogowano jako klient ' . $customer->firstname . ' ' . $customer->lastname); 
        }
         else 
        {
            $this->context->controller->errors[] = $this->l('Nie udało się'); 
        }
        

        
    }
    private function createToken($customerId)
    {
        return md5(_COOKIE_KEY_.$customerId.date("Ymd"));
    }

    private function getCustomers(): array
    {
        $db = Db::getInstance();

        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'customer';
        $result = $db->executeS($sql); 

        return $result; 

    }

    private function getCustomer($id_customer): array
    {
        $db = Db::getInstance();

        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'customer where id_customer = ' . $id_customer;
        $result = $db->executeS($sql); 

        return $result; 
    }
}


?>