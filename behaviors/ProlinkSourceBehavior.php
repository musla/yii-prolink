<?php
/**
 * Behavior to be used with models that are source of prolinks. 
 * Specified fields will be stored in the prolink_keys table and 
 * will be converted to links. 
 */
class ProlinkSourceBehavior extends CActiveRecordBehavior{

	/**
	 * Array of expression to be evalated to represent the key of the record
	 */
	public $keys = array(); 
 	/**
	 * Route to be given to URL manager to create link to resource
	 */
	public $urlMap = array(); 
	
	
	public function afterSave($event){
		$this->addKeySources();	
		return parent::afterSave($event);
	}
	
	public function addKeySources(){
		$newRecords = array();
		$oldRecords = $this->keyRecords();
		$oldKeys = array();
		foreach($oldRecords as $or) array_push($oldKeys, $or->id);
		
		foreach($this->keys as $k) {
			$newkey = trim($this->evaluateExpression($k,array('data'=>$this->getOwner())));
			$key = null;
			foreach($oldRecords as $ok) if ($ok->key == $newkey) {
				$key = $ok;
			}
			if (!$key) {
				$key = new ProlinkKey;
			}
			
			if ($newkey != '' && $newkey != $key->key) {
				$key->model		= get_class($this->getOwner()); // Gets a plain text version of the model name
				$key->model_id	= $this->getNormalizedPk();
				$key->key		= $newkey;
				$key->url 		= $this->getURL();
				$key->timestamp = time();
				$key->save();
			}
			array_push($newRecords, $key->id);
		}
		$toDelete = array_diff($oldKeys, $newRecords);
		/*to be faster*/
		if (!empty($toDelete)) Yii::app()->db->createCommand()->delete('prolink_keys',"(id IN(".implode(',',$toDelete).")) ");
		
	}

	public function afterDelete($event){
		/**
		 * Delete from all keys.
		 * Solve issue on prolinked texts!!!
		 * Consider a table prolink_keys => prolink_content
		 */	
		//$sql = "DELETE FROM prolink_keys WHERE model = :model and model_id = :model_id";
		Yii::app()->db->createCommand()->delete('prolink_keys', 'model=:model AND model_id=:model_id', array(':model'=> get_class($this->getOwner()) , ':model_id'=> $this->getNormalizedPk() ) );
		return parent::afterDelete($event);
	}

	/*	
	public function afterFind($event){
		$this->_oldKeyValue = $this->getOwner()->getAttribute($this->keyAttribute);
		return parent::afterFind($event);
	}
	*/



	/**
	 * Construct resulting URL 
	 */
	public function getURL() {
		$r = $this->urlMap;
		$fst = true;
		foreach($r as $k=>$v) {
			/*omit first item, it is a route*/
			if (!$fst) $r[$k] = $this->evaluateExpression($v,array('data'=>$this->getOwner()));
			$fst = false;
		}
		return CHtml::normalizeUrl($r);
	}
	
	/**
	 * Return prolink_keys record for this model
	 */
	public function keyRecords() {
		return ProlinkKey::model()->findAllByAttributes(array('model'=>get_class($this->getOwner()), 'model_id'=> $this->getNormalizedPk()));
	}

	protected function getNormalizedPk(){
		$pk = $this->getOwner()->getPrimaryKey();
		return is_array($pk) ? json_encode($pk) : $pk;
	}
}