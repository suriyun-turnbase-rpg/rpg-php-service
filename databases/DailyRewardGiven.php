<?php
class DailyRewardGiven extends \DB\SQL\Mapper {
	public function __construct() {
		parent::__construct( \Base::instance()->get('DB'), \Base::instance()->get('db_prefix') . 'daily_reward_given' );
	}
}
?>