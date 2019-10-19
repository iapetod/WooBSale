# WooBsale

Plugin for the integration of the BSale API with Woocommerce

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

It is necessary to have Wordpress installed

```
$ wp core install --url=example.com --title=Example --admin_user=supervisor --admin_password=strongpassword --admin_email=info@example.com
```

or

In addition to wordpress, it is necessary to have woocommerce installed for full integration


```
wp plugin install woocommerce --activate
```

or download and install zip in https://woocommerce.com/


### Installing


```
cd path_wordpress/wp-content/plugins
mkdir bsale
cd bsale
git clone https://github.com/iapetod/WooBSale.git
```

In Wordpress Path

```
wp plugin activate bsale
```

or zip the bsale folder and install the plugin from the wordpress dashboard

## Features

* Import and Synchronization of products, categories and price list
* Verification and update of inventories
* Issuance of invoices
* Discounts on invoices through woocommerce coupons
* Creation of products with their variants and in pack


## Authors

* **Jesus Marcano** - *Initial work* - [Iapetod](https://github.com/iapetod)

See also the list of [contributors](https://github.com/iapetod/WooBSale/graphs/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

