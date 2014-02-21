Prolink v0.1
==========

Prolink is a linking behavior for [Yii web framework](http://www.yiiframework.com) that collects key from 
ActiveRecords and automatically creates link in other ActiveRecords attributes. 

## Installation

Import migrations/prolink.sql into your database. 


```
'import'=>array(
    'application.extensions.prolink.models.*',
)
```

To the ActiveRecord that is supposed to be linked add a ProlinkSource Behavior.
For example the User ActiveRecord.

```
'ProlinkSourceBehavior' => array(
    'class' => 'application.extensions.prolink.behaviors.ProlinkSourceBehavior',
   	'keys'=> array('$data->username'), /*expressions to be evaluated as key*/
   	'urlMap'=> array('/user/view', 'id'=>'$data->id'), /*array expression to be evaluated and used for URL construction*/
),

``` 
You can specify more expressions to be linked in keys array. 

Add ProlinkContent behavior to the ActiveRecord models where you want attributes to be pro-linked and use prolinked values.
For example the Blog ActiveRecord 
```
'ProlinkContentBehavior' => array(
    'class' => 'application.extensions.prolink.behaviors.ProlinkContentBehavior',
   	'attributes'=>array('text'),
),

```
In the views replace $model->text with Behavior method $model->linked('text').



## Todo
This is initial version that uses database for both, keys and converted text. In comparison with other tools caching prolinked 
text is stored and updated in DB and not calculated on the fly. 

However there is a change that other caching mechanism would be much faster. 
I plan to create a 'ContentStorage' abstract class working on key-value principe to store the prolinked content. 
Then memcached or any faster noSQL storage could be used to speed up the application. 
You are welcome to help here.
 


## Bug tracker
If you find any bugs, please create an issue at [issue tracker for project Github repository](https://github.com/musla/yii-prolink/issues).

## License
This work is licensed under a MIT license. Full text is included in the `LICENSE` file in the root of codebase.

[![tomashnilica.com](http://tomashnilica.com/themes/unify/assets/img/logo1-orange.png)](http://www.tomashnilica.com)

Perfect web applications.

[www.tomashnilica.com](http://www.tomashnilica.com)
