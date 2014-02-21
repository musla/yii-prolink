<?php

/**
 * This is the model class for table "prolink_keys".
 *
 */
class ProlinkKey extends CActiveRecord
{
	

	/**
	 * Returns the static model of the specified AR class.
	 * @return Event the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'prolink_keys';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			/*array('e_obj_id, iaccount, user', 'numerical', 'integerOnly'=>true),*/
			array('id, timestamp, model_id', 'numerical', 'integerOnly'=>true),
			array('key', 'length', 'max'=>128),
			array('model', 'length', 'max'=>32),
			array('url', 'length', 'max'=>256),
			array('id, key, model, model_id, url, timestamp', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => Yii::t('tt','ID'),
			'key' => Yii::t('tt','Key'),
			'model' => Yii::t('tt','Model'),
			'model_id' => Yii::t('tt','Model ID'),
			'url' => Yii::t('tt','URL'),
			'timestamp' => Yii::t('tt','Timestamp'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;
		
		$criteria->compare('id',$this->id);
		$criteria->compare('key',$this->key,false);
		$criteria->compare('model',$this->model, false);
		$criteria->compare('model_id',$this->model_id,true);
		$criteria->compare('url',$this->url,true);
		$criteria->compare('timestamp',$this->timestamp,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
			'sort'=> array(
				'defaultOrder'=>'id DESC',
			),
		));
	}
	
	
	/*return destination model*/
	public function getObjClass() {
		$model_name = $this->model;
		return $model_name::model();
	}

	/*return destination model*/
	public function getObj() {
		if (!$this->model || !$this->model_id) return null;
		$model_name = $this->model;
		return $model_name::model()->findByPk($this->model_id);
	}

	
	public static function add($key, $url, $model, $model_id) {
		$e = new ProlinkKey;
		$e->key = $key;
		$e->url = $url;
		$e->model = $model;
		$e->model_id = $model_id;
		$e->timestamp = time();
		$e->save();
		return $e;
	}
	
}