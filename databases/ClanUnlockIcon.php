<?php
class ClanUnlockIcon extends \DB\SQL\Mapper {
	public function __construct() {
		parent::__construct( \Base::instance()->get('DB'), \Base::instance()->get('db_prefix') . 'clan_unlock_icon' );
	}
}
?>