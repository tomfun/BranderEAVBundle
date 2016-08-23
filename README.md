### How it work, what is it
[read in russian](/how_it_work.ru.md)

### Install bundle

```bash
composer require tomfun/brander-eav
```

```php
// app/AppKernel.php
    public function registerBundles()
    // ...
        new \Brander\Bundle\EAVBundle\BranderEAVBundle(),
        new FOS\ElasticaBundle\FOSElasticaBundle(),
        new JMS\AopBundle\JMSAopBundle(),
        new JMS\SerializerBundle\JMSSerializerBundle(), // optional
        new JMS\DiExtraBundle\JMSDiExtraBundle($this),

    // ...
```

```yml
# app/config/parameters.yml
# same add to app/config/parameters.yml.dist
parameters:
# .........
    locale: ru
```

[just **enable** elastica bundle](https://github.com/FriendsOfSymfony/FOSElasticaBundle/blob/master/Resources/doc/setup.md)
[and add **base configuration**](https://github.com/FriendsOfSymfony/FOSElasticaBundle/blob/master/Resources/doc/setup.md#c-basic-bundle-configuration)

```yml
#app/config/config.yml
fos_elastica:
    clients:
        default: { host: localhost, port: 9200 }
    indexes:
        app: ~
```

### Requirements
 - FOSElasticaBundle()
 - JMSAopBundle()
 - JMSDiExtraBundle($this)
 - JS router with generate function
 - Twigjs filters (for listing frontend)
   - trans
   - transchoice
 - Compatible [gulp task](https://www.npmjs.com/package/brander-gulp-tasks) with twigjs compilation

### Configuration

#### First at all
 - you must have entity you want to search
 - query php class
 - search result class

#### Config example

```yml
brander_eav:
  fixturesDirectory: /home/tomfun/fixtures-data
  useJmsSerializer: false #turn off standard elastica serializer for known entity
  list_class_map:
    - Sdelka\Bundle\AdvertBundle\Entity\Advert #entity with eav values, auto find query and result classes in model dir
```
this expanded as: 
```yml
brander_eav:
  useJmsSerializer: false #turn off standard elastica serializer for known entity
  list_class_map:
    - 
      entity: Sdelka\Bundle\AdvertBundle\Entity\Advert #orm entity
      query: Sdelka\Bundle\AdvertBundle\Model\AdvertQuery #query class. must exist.
      result: Sdelka\Bundle\AdvertBundle\Model\AdvertQuery #result class. must exist.
      serviceClass: Brander\Bundle\EAVBundle\Service\Elastica\EavList #service class
```
also **you must implement** some interfaces (e.g. *SearchableEntityInterface*).
name of listing service in this case:
"brander_eav.elastica.list.sdelka_advert.advert"

if you don't need **auto configuration of elastica bundle**, you can use simple serialize directive (searchable) and configure elastic search bundle manually:
```yml
brander_eav:
  useJmsSerializer: false #turn off standard elastica serializer for known entity
  searchable:
    - Sdelka\Bundle\AdvertBundle\Entity\Advert #orm entity
```

#### show auto-generated lists (inner elastica index name / type name): ###
```
app/console de:cont | grep "brander_eav.elastica.list"
```

#### Routing ###

in app/config/routing.yml add this lines:

```yml
# app/config/routing.yml
eav:
  resource: "@BranderEAVBundle/Resources/config/routing.yml"
  options:
    i18n: false
    expose: true
```

default admin url is **/admin/eav/manage/**

### Security ###

if you want *grant access for admin part for non admin* (example: manager)
```yml
brander_eav:
  useJmsSerializer: false #turn off standard elastica serializer for known entity
  manageRole: ROLE_MANAGER
```
or for *anonymous*: ```manageRole: "anon."```
or rewrite voter service: *brander_eav.security.universal_voter*.

also look into
[ElasticaSkeletonBundle](https://github.com/tomfun/BranderElasticaSkeletonBundle/blob/master/README.md)

todo:
* vendor/werkint/stats-bundle/src/Service/Security/Voter/StatsVoter.php supportsAttribute
* backbone.modelbinder -> stickit

wtf:
cache.app
voter
Twig\BranderEAVExtension
\Brander\Bundle\EAVBundle\DependencyInjection\BranderEAVExtension::getConfiguration
