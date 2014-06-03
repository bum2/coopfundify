coopfundify
===========

Add some customizations to fundify by Astoundify theme.


Customizations
----------------------
- **Shop_worker** EDD->get_payments hook
- 
 Provide 'shop_worker" role the ability to manage pending payments once they are paid outside website.

 NOTICE: 
 
 1) That we will only check for payments done by "[Manual Gateway](https://github.com/aleph1888/manual_edd_wp_plugin)" wich will only process ONE item on Cart at once.
 1.1) Only "pending" payments will be listed.
 
 2) Campaign_contributor role will be changed to 'shop_worker' when a user creates a campaign.
 
 3) Payment management will be done on backend wp-admin. Still user can see own payments on frontend. While campaign edition can be done in both frontend and backend. 

 [File](https://github.com/aleph1888/coopfundify/blob/master/shop_worker.php)



- **campaign_payments_column_adder**
- 
 To add a 'campaign' column to the admin's Payments History page.

 NOTICE:
 
 1) Assuming all user payments Carts have only ONE item (campaign) to pay (contribute).
 
