Ez Social Login Bundle
======================

[![Build Status](https://secure.travis-ci.org/crevillo/ezsocialloginbundle.svg?branch=master)](http://travis-ci.org/crevillo/ezsocialloginbundle)

The eZ Social Login Bundle adds support for authenticating users via OAuth1.0a or OAuth2 in eZ Platform.

It just uses [HwiOauthBundle](https://github.com/hwi/HWIOAuthBundle), so you can refer to this documentation 
bundle to know how it internally works. 

Installation
------------
You can install this project via composer:
```
composer require crevillo/ezsocialloginbundle
```

Documentation
-------------

You can use any of the social networks listed in [HwiOauthBundle documentation](https://github.com/hwi/HWIOAuthBundle#installation). 
Please refer to that bundle in order to know how you can configure any of them. 

Setting up this bundle
----------------------

### A) Add EzSocialLoginBundle to your project

```bash
composer require crevillo/ezsocialloginbundle:dev-master
```

### B) Enable the bundle

Enable the bundle in the kernel, you will also need to enable
HwiOauthBundle

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),
        new Crevillo\EzSocialLoginBundle\CrevilloEzSocialLoginBundle()
    );
}
```

### C) Import the routing

Import the `redirect.xml` and `login.xml` routing files in your own routing file.

```yaml
# app/config/routing.yml
hwi_oauth_redirect:
    resource: "@HWIOAuthBundle/Resources/config/routing/redirect.xml"
    prefix:   /connect

hwi_oauth_login:
    resource: "@HWIOAuthBundle/Resources/config/routing/login.xml"
    prefix:   /login
```

**Note:**

> The bundle will override default user login template provided by EzPublishCoreBundle. It might happen that you
won't see any changes if any of your others bundles does that too. So, if you already have a customized login template,
just add [these lines](https://github.com/crevillo/ezsocialloginbundle/blob/master/Resources/views/Security/login.html.twig#L36-L38) anywhere in your content block of your template.

### D) Configure Google resource owner

You will need to modify your config.yml file adding 

```yaml
hwi_oauth:
    # list of names of the firewalls in which this bundle is active, this setting MUST be set
    firewall_names: [ezpublish_front]
```    

Next, you can add your google app information and options under hwi_oauth > resource_owners settings
```yaml
hwi_oauth:
    # list of names of the firewalls in which this bundle is active, this setting MUST be set
    firewall_names: [ezpublish_front]
    resource_owners:
        your_google_app:
            type: google
            client_id: <your client id>
            client_secret: <your client secret>
            scope: "email profile"
```    

### E) Configuring the Security Layer

The bundle needs a service that is able to load users based on the user response of the oauth endpoint.
It should implement the interface: ```HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface```.
This bundle provides [this service](https://github.com/crevillo/ezsocialloginbundle/blob/master/Security/EzSocialUserProvider.php) as an starting
point, but you are free to create your own. 

Our UserProvider will try to get the email from the social network. If there as already any user in the eZ Platform
repository with this mail, it will just return it. 

If there is no user having that email, it will try to create a new one under your "Guest Accounts" user group and
will also log it.

### F) Configure the oauth firewall

In the firewall configuration you will need to configure a login path for the resource owners you have configured previously.
Additionally you will need to point the oauth firewall to the service this bundle provides:

```yaml
# app/config/security.yml
security:
    firewalls:
        # your other firewalls
        #
        ezpublish_front:
            anonymous: ~
            oauth:
                resource_owners:
                    google:             "/login/check-google"
                login_path:        /login
                use_forward:       false
                failure_path:      /login

                oauth_user_provider:
                    service:  crevillo.ezsocialloginbundle.oauth_aware.user_provider.service
```

Finally the paths you have defined in the previous step for your resource_owners must be adding to your routing.

```yaml
# app/config/routing.yml
google_login:
    path: /login/check-google
```

* Actually this is manually tested and seems to work for Facebook, Google and Linkedin. 
* Twitter doesn't provide the email of the logged user. I haven't tested this bundle with it yet. 

That's it!. Feel free to try any other social networks!. 
