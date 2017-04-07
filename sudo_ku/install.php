<?php
/**
 * 安装
 *
 */

/**
 * 活动配置表
 *
 */
$sql = "
CREATE TABLE IF NOT EXISTS " . tablename('sudo_ku') . " (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) NOT NULL,
  `weid` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `starttime` int(10) DEFAULT NULL,
  `endtime` int(10) DEFAULT NULL,
  `perssion_city` varchar(50) NOT NULL,
  `start_picurl` varchar(100) NOT NULL,
  `isSubscribe` tinyint(4) NOT NULL DEFAULT '0',
  `set_win_one_cash` tinyint(4) NOT NULL DEFAULT '1' COMMENT '让用户只能领取到一个红包',
  `setPrizeName` varchar(25) NOT NULL,
  `male_qrcode` varchar(100) NOT NULL,
  `female_qrcode` varchar(100) NOT NULL,
  `tip_img` varchar(100) NOT NULL,
  `rule` varchar(1000) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `copyright` varchar(100) NOT NULL,
  `peruserday_num_times` int(10) NOT NULL,
  `peruserday_num_times_tips` varchar(255) NOT NULL,
  `per_nums_value` int(10) NOT NULL DEFAULT '0' COMMENT '买一次玩法花费金币',
  `sweet_tips` text NOT NULL,
  `share_open_close` tinyint(4) NOT NULL DEFAULT '0',
  `share_title` varchar(200) DEFAULT NULL,
  `share_type` tinyint(4) NOT NULL DEFAULT '0',
  `share_icon` varchar(200) DEFAULT NULL,
  `share_content` varchar(200) DEFAULT NULL,
  `share_confirm_url` varchar(50) NOT NULL,
  `share_give_num` int(10) NOT NULL DEFAULT '0',
  `share_per_day_time_line_num` int(10) NOT NULL DEFAULT '0' COMMENT '分享盆友圈次数',
  `share_per_day_app_num` int(10) NOT NULL DEFAULT '0' COMMENT '分享朋友次数',
  `share_scs_tips` varchar(100) NOT NULL,
  `createtime` int(10) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
pdo_query($sql);


/**
 * 用户信息表
 *
 */
$sql = "
CREATE TABLE IF NOT EXISTS " . tablename('sudo_ku_user') . " (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jid` int(10) NOT NULL,
  `openid` varchar(200) NOT NULL,
  `other_openid` varchar(100) NOT NULL,
  `nickname` varchar(100) NOT NULL,
  `headimgurl` varchar(200) NOT NULL,
  `coin` int(10) NOT NULL DEFAULT '0',
  `is_win_cash` tinyint(4) NOT NULL DEFAULT '0',
  `leftPlayTimes` int(10) NOT NULL DEFAULT '0',
  `last_time` int(10) NOT NULL,
  `createtime` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `index_jid` (`jid`),
  KEY `index_openid` (`openid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1" ;
pdo_query($sql);

/**
 *奖品表
 *
 */
$sql = "
CREATE TABLE IF NOT EXISTS " . tablename('sudo_ku_prize') . " (
  `prize_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jid` int(10) NOT NULL DEFAULT '0',
  `location` tinyint(4) NOT NULL DEFAULT '0',
  `prize_type` tinyint(4) NOT NULL DEFAULT '0',
  `jid_loca` varchar(25) NOT NULL,
  `prize_value` int(10) NOT NULL,
  `prize_name` varchar(25) NOT NULL,
  `prize_level` varchar(25) NOT NULL DEFAULT '',
  `prize_img` varchar(100) NOT NULL,
  `prize_total` int(10) NOT NULL DEFAULT '0',
  `prize_left` int(10) NOT NULL DEFAULT '0',
  `win_max_sum` int(10) NOT NULL DEFAULT '0',
  `give_max_sum` int(10) NOT NULL DEFAULT '0',
  `prize_min_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `prize_max_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `possibility` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`prize_id`),
  UNIQUE KEY `jid_loca` (`jid_loca`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
pdo_query($sql);

/**
 * 抽奖记录表
 *
 */
$sql = "
CREATE TABLE IF NOT EXISTS " . tablename('sudo_ku_user_record') . " (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jid` int(10) NOT NULL,
  `prize_id` int(10) NOT NULL DEFAULT '0',
  `openid` varchar(200) NOT NULL,
  `award_name` varchar(200) NOT NULL,
  `createtime` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `index_openid` (`openid`),
  KEY `index_jid` (`jid`),
  KEY `index_create` (`createtime`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
pdo_query($sql);

/**
 * 用户中奖记录表
 *
 */
$sql = "
CREATE TABLE IF NOT EXISTS " . tablename('sudo_ku_user_award') . " (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jid` int(10) NOT NULL,
  `openid` varchar(200) NOT NULL,
  `prize_id` int(10) NOT NULL DEFAULT '0',
  `award_name` varchar(200) NOT NULL,
  `prize_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(4) DEFAULT '0',
  `createtime` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `index_openid` (`openid`),
  KEY `index_jid` (`jid`),
  KEY `index_prize_id` (`prize_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
pdo_query($sql);

/**
 * 用户分享
 *
 */
$sql = "
CREATE TABLE IF NOT EXISTS " . tablename('sudo_ku_share') . " (
  `share_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) NOT NULL,
  `jid` int(10) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0表示分享朋友圈，1表示分享朋友',
  `createtime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`share_id`),
  KEY `index_openid` (`openid`),
  KEY `index_jid` (`jid`),
  KEY `index_status` (`status`),
  KEY `index_create` (`createtime`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1";
pdo_query($sql);



?>


