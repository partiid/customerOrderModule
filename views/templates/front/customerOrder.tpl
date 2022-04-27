{extends file='customer/page.tpl'}

{block name='page_content'}
<div class="row">
    <h1>{l s='Wybierz klienta' d='Modules.customerOrder'}</h1>
    <form class="customerOrderForm" method="POST">
        <div class="row">
            <select class="form-select w-100 display-3" aria-label="Select customer" name="selected-customer">
                <option selected>{l s='Wybierz klienta' d='Modules.customerOrder'}</option>

                {foreach $customers as $customer}
                    <option value="{$customer.id_customer}">{$customer.firstname} {$customer.lastname}</option>
                {/foreach}

                
            </select>
        </div>
       
        
        <div class="row d-flex ">
            <button class="btn btn-primary display-2 mt-2" type="submitCustomerOrderController">{l s='Wybierz' d='Modules.customerOrder'}</button>
    
        </div>
    </form>
</div>






{/block}