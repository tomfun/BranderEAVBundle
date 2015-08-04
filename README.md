show auto-generated lists (inner elastica index name / type name):
```
app/console de:cont | grep "brander_eav.elastica.list"
```

config example:
```yml
brander_eav:
  fixturesDirectory: /home/tomfun/fixtures-data
  useJmsSerializer: false #turn off standard elastica serializer for known entity
  list_class_map:
    - Sdelka\Bundle\AdvertBundle\Entity\Advert #entity with eav values, auto find query and result classes in model dir
```
 - this expanded as: 
```yml
brander_eav:
  useJmsSerializer: false #turn off standard elastica serializer for known entity
  list_class_map:
    - 
      entity: Sdelka\Bundle\AdvertBundle\Entity\Advert #orm entity
      query: Sdelka\Bundle\AdvertBundle\Model\AdvertQuery #query class. must exist.
      result: Sdelka\Bundle\AdvertBundle\Model\AdvertQuery #result class. must exist.
      serviceClass: Brander\Bundle\EAVBundle\Model\Elastica\EavList #service class
```
also you must implement some interfaces (e.g. SearchableEntityInterface).
name of listing service in this case:
"brander_eav.elastica.list.sdelka_advert.advert"

if you don't need auto configuration of elastica bundle, you can use simple serialize directive:
```yml
brander_eav:
  useJmsSerializer: false #turn off standard elastica serializer for known entity
  searchable:
    - Sdelka\Bundle\AdvertBundle\Entity\Advert #orm entity
```

if you want grant access for admin part for non admin (example: manager)
```yml
brander_eav:
  useJmsSerializer: false #turn off standard elastica serializer for known entity
  manageRole ROLE_MANAGER
```
or rewrite voter.

also look into
src/Brander/Bundle/ElasticaSkeletonBundle/queries.md