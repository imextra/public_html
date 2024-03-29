<?php

class CUserEntity extends CBaseEntity
{
	protected function __construct() {}

	public function initialize()
	{
		$this->className = __CLASS__;
		$this->filePath = __FILE__;

		$this->uf_id = 'USER';

		global $DB;

		$this->fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer'
			),
			'LOGIN' => array(
				'data_type' => 'string'
			),
			'ACTIVE' => array(
				'data_type' => 'boolean'
			),
			'NAME' => array(
				'data_type' => 'string'
			),
			'SECOND_NAME' => array(
				'data_type' => 'string'
			),
			'LAST_NAME' => array(
				'data_type' => 'string'
			),
			'WORK_POSITION' => array(
				'data_type' => 'string'
			),
			'SHORT_NAME' => array(
				'data_type' => 'string',
				'expr' => array(
					$DB->Concat("%s","' '", "UPPER(".$DB->Substr("%s", 1, 1).")", "'.'"),
					'LAST_NAME', 'NAME'
				)
			),
			'UTS_OBJECT' => array(
				'data_type' => 'UtsUser',
				'reference' => array('ID', 'VALUE_ID')
			)
		);
	}

}