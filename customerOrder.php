<?php
/**
* 2007-2022 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomerOrder extends Module
{
    protected $config_form = false;

    

    public function __construct()
    {
        $this->name = 'customerOrder';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Supportal.pl';
        $this->need_instance = 1;

        

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Order as customer');
        $this->description = $this->l('This module allows you to place an order as any customer');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall? ');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('CUSTOMERORDER_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayCustomerAccount');
    }

    public function uninstall()
    {
        Configuration::deleteByName('');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */

         $output = '';


        if (((bool)Tools::isSubmit('submitCustomerOrderModule')) == true) {
            $this->postProcess();

            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        //$output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();

    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCustomerOrderModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {

        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                  $this->getCustomerSelectField(), 

                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'CUSTOMERORDER_ADMINACCOUNT' => Configuration::get('CUSTOMERORDER_ADMINACCOUNT'),
            
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }

        
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }


    //display additional block in customer account to forward to front controller

    public function hookDisplayCustomerAccount()
    {
        $customer_id = $this->context->customer->id;
        $admin_account = Configuration::get('CUSTOMERORDER_ADMINACCOUNT');

        if($customer_id == $admin_account)
        {
            $this->context->smarty->assign(array(
                'controller_url' => $this->context->link->getModuleLink($this->name, 'customer', array()),
            ));
    
            return $this->display(__FILE__, 'views/templates/front/orderAsCustomerBlock.tpl');
        }
        
    }

    public function getModuleLink()
    {
        return $this->context->link->getModuleLink('customerOrder', 'display');
    }
    
    private function getCustomers(): array
    {
        $db = Db::getInstance();

        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'customer';
        $result = $db->executeS($sql);

        return $result;
    }

    private function getCustomerSelectField(): array
    {
        $customers = $this->getCustomers(); 

        $field = array(
            'type' => 'select',
            'label' => $this->l('Admin account'),
            'desc' => $this->l('Select the admin account to be able to use the module in front office'),
            'name' => 'CUSTOMERORDER_ADMINACCOUNT',
            'required' => true,
            'options' => array(
                'query' => $customers,
                'id' => 'id_customer',
                'name' => 'firstname',
            ),
        );
        
        return $field; 
    }
}
