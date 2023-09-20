#Advancelearn Otp-auth

<img src="https://banners.beyondco.de/advancelearn%2Fmanage-payment-and-orders.png?theme=dark&packageManager=composer+require&packageName=advancelearn%2Fmanage-payment-and-orders&pattern=stripes&style=style_1&description=Orders+and+payments+management+system+in+Laravel+and+the+feature+of+adding+sales+functionality+for+each+model&md=1&showWatermark=0&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg&widths=350" alt="advancelearn-otp-auth">


<a name="introduction"></a>

## Introduction

This package was developed in collaboration with a Sadratek team member and AdvanceLearn. This package aims to create and manage various aspects related to user orders and payments. It includes tracking the number of products and tagging all steps of the user's purchase order." This package is currently under active development and there are plans to add a shopping voucher module in the future, pending support and feasibility.

<a name="installation"></a>

## Installation

You can install the package with Composer.

```bash
composer require advancelearn/manage-payment-and-orders
```

<a name="Config"></a>

## Config
After installation, please add its provider to your config folder in the app file to complete and configure the package:

```php
 \Advancelearn\ManagePaymentAndOrders\ManagePayOrderServiceProvider::class,
```

Then run this command to import and make the package tables public

```php
php artisan vendor:publish
```

Select the row number of this title from among the tags and enter it
```php
  Tag: AdvanceLearnManagePayAndOrder-migrations ...............
```

#### By entering the tag number of the image above, these tables will be added to the tables folder of your program

![img_1.png](img_1.png)

Then enter the following command to add tables in your database

```php 
php artisan migrate
```
ادامه مستندات رو باید تکمیل کنم

<a name="conclusion"></a>

[//]: # (## Conclusion)

[//]: # ()
[//]: # (With this advanced learning package called advancelearn/otp-auth, you can easily send the user's username and receive)

[//]: # (the token, and you will not have the trouble of creating or saving the token in the database, because the token for the)

[//]: # (user's username is easily cached according to the time you give. and in the second step, the token validation is applied)

[//]: # (to the requested username, and according to that, you can successfully register the user in the system or direct the)

[//]: # (user to the resend code page.)
