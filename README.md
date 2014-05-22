coopfundify
===========

Add some customizations to fundify by Astoundify theme.


Customizations
----------------------
- **Shop_worker** EDD->get_payments hook
- 
 Provide 'shop_worker" role the ability to manage pending payments once they are paid outside website.

 NOTICE: that we will only check for payments done by "Manual Gateway" wich will only process ONE item on Cart at once.
 
 NOTICE: Campaign_contributor role will be changed to 'shop_worker' when a user creates a campaign
 
 [File](https://github.com/aleph1888/coopfundify/blob/master/shop_worker.php)
