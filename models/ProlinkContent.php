<?php

/**
 * This is the model class for table "prolink_keys".
 *
 */
class ProlinkContent extends CActiveRecord
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
		return 'prolink_content';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			/*array('e_obj_id, iaccount, user', 'numerical', 'integerOnly'=>true),*/
			array('id, timestamp, model_id', 'numerical', 'integerOnly'=>true),
			array('field', 'length', 'max'=>32),
			array('model', 'length', 'max'=>32),
			array('id, field, model, model_id, timestamp, prolinked', 'safe', 'on'=>'search'),
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
			'field' => Yii::t('tt','Field'),
			'model' => Yii::t('tt','Model'),
			'model_id' => Yii::t('tt','Model ID'),
			'prolinked' => Yii::t('tt','Linked Text'),
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
		$criteria->compare('field',$this->field,false);
		$criteria->compare('model',$this->model, false);
		$criteria->compare('model_id',$this->model_id,true);
		$criteria->compare('prolinked',$this->prolinked,true);
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

	

	
}