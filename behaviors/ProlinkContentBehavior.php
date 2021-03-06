<?php
/**
 * Behavior to be used with models that have atrributes to be linked with ProlinkKey.
 * This Behavior is working with prolink_content table.
 */
class ProlinkContentBehavior extends CActiveRecordBehavior{

	public $attributes = array();
		
	
	public function afterDelete($event){		
		Yii::app()->db->createCommand()->delete('prolink_content', 'model=:model AND model_id=:model_id', array(':model'=> get_class($this->getOwner()) , ':model_id'=> $this->getNormalizedPk() ) );
		return parent::afterDelete($event);
	}

	/**
	 * Delete cached content when a record was changes. 
	 * todo: store old values of prolinked attributes and delete only in
	 * case those have been changed.  
	 */
	public function afterSave($event){
		Yii::app()->db->createCommand()->delete('prolink_content', 'model=:model AND model_id=:model_id', array(':model'=> get_class($this->getOwner()) , ':model_id'=> $this->getNormalizedPk() ) );
		return parent::afterSave($event);
	}
	
	/**
	 * Return pro-linked version of the text.
	 * Todo: its necessary to implement shadow relation to avoid another query for linked text.
	 * When performance is slow try to use memcached server. 
	 * Optionally use some FAST no-sql database or key-value service. This approach seems to be much more faster 
	 * than traditional DB table storage. 
	 */
	public function linked($field) {
		/* Get timestamo of last key table change
		 * This code is mysql dependant.
		 */
		$curdba  = explode('=', Yii::app()->db->connectionString);
		$curdb =  $curdba[2];
		
		$lastSourceModify = 0;
		$lsm = Yii::app()->db->createCommand("
					SELECT UPDATE_TIME FROM  information_schema.tables WHERE  TABLE_SCHEMA = '${curdb}' AND TABLE_NAME = 'prolink_keys'")->
					queryAll();
		foreach($lsm as $lr) $lastSourceModify = strtotime($lr['UPDATE_TIME']);
				
		$linked =  ProlinkContent::model()->findByAttributes(array('model'=>get_class($this->getOwner()), 'model_id'=> $this->getNormalizedPk(), 'field'=>$field ));
		if (!$linked) {
			/*create new one and return it*/
			$l = $this->createProlinkContent($field);
			return $l->prolinked;
		} else {
			if ($lastSourceModify > $linked->timestamp) {
				/*regenerate this record, its outdated*/
				$l = $this->updateProlinkContent($linked, $field);
				return $l->prolinked;
			} else return $linked->prolinked;
		}
	}
	
	/**
	 * Create new ProlinkContent and return
	 */
	 public function createProlinkContent($field) {
	 	$l = new ProlinkContent;
		$l->model = get_class($this->getOwner());
		$l->model_id = $this->getNormalizedPk();
		$l->field = $field;
		$l->timestamp = time();
		$l->prolinked = $this->createLinked($this->getOwner()->{$field});
		$l->save();
		return $l;
	 }

	/**
	 * Update new ProlinkContent and return
	 */
	 public function updateProlinkContent($l, $field) {
	 	$l->prolinked = $this->createLinked($this->getOwner()->{$field});
		$l->save();
		return $l;
	 }

	
	/**
	 * Create pro-linked text from source
	 */
	public function createLinked($text) {
		$text = "  " . $text . "  ";
		/*using ActiveRecord for large datasets is highly inefficient*/	
		$cmd = Yii::app()->db->createCommand('SELECT p.key, p.url FROM prolink_keys p ORDER BY LENGTH(p.key) DESC');
		$keys=$cmd->query();
		
		while(($row=$keys->read())!==false) {
			$phrase = " ".$row["key"]." ";
			//$phrase = $row["key"];
			$link = CHtml::link($phrase, $row['url'], array('class'=>'prolink'));
			$str_lc = strtolower($text);
			$offset = 0;
			while($position = strpos($text, $phrase, $offset))
			{
			    if (substr_count($str_lc, "<a", 0, $position) <= substr_count($str_lc, "</a>", 0, $position)) {
			        $text = substr_replace($text, $link, $position, strlen($phrase));
			        $str_lc = strtolower($text);
			        $offset = $position + strlen($link) - strlen($phrase);
			    } else {
			        $offset = $position + 1;
			    }
			}
		}
		if (substr($text,0,2) == "  ") $text = substr($text,2);
		else $text = substr($text,1);
		$text = substr($text,0,strlen($text)-1);
		if (substr($text,strlen($text)-2) == "  ") $text = substr($text,0,strlen($text)-2);
		else $text = substr($text,0,strlen($text)-1);
		return $text;
	}


	protected function getNormalizedPk(){
		$pk = $this->getOwner()->getPrimaryKey();
		return is_array($pk) ? json_encode($pk) : $pk;
	}
}