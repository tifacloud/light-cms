-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 2019-05-05 03:15:22
-- 服务器版本： 5.7.24
-- PHP Version: 7.2.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- 表的结构 `lc_advertisements`
--

CREATE TABLE `lc_advertisements` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `lc_advertisement_positions`
--

CREATE TABLE `lc_advertisement_positions` (
  `id` int(10) UNSIGNED NOT NULL,
  `position` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `lc_advertisement_positions`
--

INSERT INTO `lc_advertisement_positions` (`id`, `position`) VALUES
(1, 'front page nav bar'),
(2, 'front page top contents'),
(3, 'front page content ranks'),
(4, 'detail page nav bar'),
(5, 'detail page article'),
(6, 'detail page content ranks'),
(7, 'search page nav bar'),
(8, 'search page content ranks'),
(9, 'category page nav bar'),
(10, 'category page content ranks');

-- --------------------------------------------------------

--
-- 表的结构 `lc_ad_ad_positions`
--

CREATE TABLE `lc_ad_ad_positions` (
  `position_id` int(10) UNSIGNED NOT NULL,
  `advertisement_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `lc_categories`
--

CREATE TABLE `lc_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(128) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `lc_category_contents`
--

CREATE TABLE `lc_category_contents` (
  `category_id` int(10) UNSIGNED NOT NULL,
  `content_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `lc_comments`
--

CREATE TABLE `lc_comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `content` text NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `parent` int(10) UNSIGNED DEFAULT NULL,
  `content_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `lc_contents`
--

CREATE TABLE `lc_contents` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(256) NOT NULL,
  `introduction` text NOT NULL,
  `content` mediumtext NOT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '0',
  `views` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `lc_groups`
--

CREATE TABLE `lc_groups` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `name` varchar(20) NOT NULL,
  `description` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `lc_groups`
--

INSERT INTO `lc_groups` (`id`, `name`, `description`) VALUES
(1, 'admin', 'Administrator'),
(2, 'members', 'General User');

-- --------------------------------------------------------

--
-- 表的结构 `lc_links`
--

CREATE TABLE `lc_links` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(64) NOT NULL,
  `address` varchar(128) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `group_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `lc_link_groups`
--

CREATE TABLE `lc_link_groups` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(128) NOT NULL,
  `link` varchar(256) NOT NULL,
  `position` varchar(8) NOT NULL DEFAULT 'top',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `lc_login_attempts`
--

CREATE TABLE `lc_login_attempts` (
  `id` int(11) UNSIGNED NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `login` varchar(100) NOT NULL,
  `time` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `lc_pages`
--

CREATE TABLE `lc_pages` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(32) NOT NULL,
  `link` varchar(256) NOT NULL,
  `parent` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `lc_page_access`
--

CREATE TABLE `lc_page_access` (
  `id` int(10) UNSIGNED NOT NULL,
  `ip` varchar(45) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `lc_page_access`
--

INSERT INTO `lc_page_access` (`id`, `ip`, `created_at`) VALUES
(584, '::1', '2019-04-23 04:24:35'),
(585, '::1', '2019-04-23 06:14:39'),
(586, '::1', '2019-04-23 06:14:52'),
(587, '::1', '2019-05-05 01:36:40'),
(588, '::1', '2019-05-05 02:59:52');

-- --------------------------------------------------------

--
-- 表的结构 `lc_slides`
--

CREATE TABLE `lc_slides` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `lc_top_contents`
--

CREATE TABLE `lc_top_contents` (
  `id` int(10) UNSIGNED NOT NULL,
  `content_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `lc_users`
--

CREATE TABLE `lc_users` (
  `id` int(11) UNSIGNED NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(254) NOT NULL,
  `activation_selector` varchar(255) DEFAULT NULL,
  `activation_code` varchar(255) DEFAULT NULL,
  `forgotten_password_selector` varchar(255) DEFAULT NULL,
  `forgotten_password_code` varchar(255) DEFAULT NULL,
  `forgotten_password_time` int(11) UNSIGNED DEFAULT NULL,
  `remember_selector` varchar(255) DEFAULT NULL,
  `remember_code` varchar(255) DEFAULT NULL,
  `created_on` int(11) UNSIGNED NOT NULL,
  `last_login` int(11) UNSIGNED DEFAULT NULL,
  `active` tinyint(1) UNSIGNED DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `lc_users`
--

INSERT INTO `lc_users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES
(1, '127.0.0.1', 'administrator', '$2y$12$TG8.HO5YdHpQuCdqO6bPh.YENjEzqoQRsxnov1.3sPA2otI/sYduq', 'admin@admin.com', NULL, '', NULL, NULL, NULL, NULL, NULL, 1268889823, 1557020948, 1, 'Admin', 'istrator', 'ADMIN', '0');

-- --------------------------------------------------------

--
-- 表的结构 `lc_users_groups`
--

CREATE TABLE `lc_users_groups` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `group_id` mediumint(8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `lc_users_groups`
--

INSERT INTO `lc_users_groups` (`id`, `user_id`, `group_id`) VALUES
(1, 1, 1),
(2, 1, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lc_advertisements`
--
ALTER TABLE `lc_advertisements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lc_advertisement_positions`
--
ALTER TABLE `lc_advertisement_positions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lc_ad_ad_positions`
--
ALTER TABLE `lc_ad_ad_positions`
  ADD PRIMARY KEY (`position_id`,`advertisement_id`),
  ADD KEY `advertisement_id` (`advertisement_id`);

--
-- Indexes for table `lc_categories`
--
ALTER TABLE `lc_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `lc_category_contents`
--
ALTER TABLE `lc_category_contents`
  ADD PRIMARY KEY (`category_id`,`content_id`),
  ADD KEY `content_id` (`content_id`);

--
-- Indexes for table `lc_comments`
--
ALTER TABLE `lc_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent` (`parent`),
  ADD KEY `content_id` (`content_id`);

--
-- Indexes for table `lc_contents`
--
ALTER TABLE `lc_contents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lc_groups`
--
ALTER TABLE `lc_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lc_links`
--
ALTER TABLE `lc_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `lc_link_groups`
--
ALTER TABLE `lc_link_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lc_login_attempts`
--
ALTER TABLE `lc_login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lc_pages`
--
ALTER TABLE `lc_pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent` (`parent`);

--
-- Indexes for table `lc_page_access`
--
ALTER TABLE `lc_page_access`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lc_slides`
--
ALTER TABLE `lc_slides`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lc_top_contents`
--
ALTER TABLE `lc_top_contents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `content_id` (`content_id`);

--
-- Indexes for table `lc_users`
--
ALTER TABLE `lc_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uc_email` (`email`),
  ADD UNIQUE KEY `uc_activation_selector` (`activation_selector`),
  ADD UNIQUE KEY `uc_forgotten_password_selector` (`forgotten_password_selector`),
  ADD UNIQUE KEY `uc_remember_selector` (`remember_selector`);

--
-- Indexes for table `lc_users_groups`
--
ALTER TABLE `lc_users_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uc_users_groups` (`user_id`,`group_id`),
  ADD KEY `fk_users_groups_users1_idx` (`user_id`),
  ADD KEY `fk_users_groups_groups1_idx` (`group_id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `lc_advertisements`
--
ALTER TABLE `lc_advertisements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lc_advertisement_positions`
--
ALTER TABLE `lc_advertisement_positions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用表AUTO_INCREMENT `lc_categories`
--
ALTER TABLE `lc_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lc_comments`
--
ALTER TABLE `lc_comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lc_contents`
--
ALTER TABLE `lc_contents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lc_groups`
--
ALTER TABLE `lc_groups`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `lc_links`
--
ALTER TABLE `lc_links`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lc_link_groups`
--
ALTER TABLE `lc_link_groups`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lc_login_attempts`
--
ALTER TABLE `lc_login_attempts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lc_pages`
--
ALTER TABLE `lc_pages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lc_page_access`
--
ALTER TABLE `lc_page_access`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=589;

--
-- 使用表AUTO_INCREMENT `lc_slides`
--
ALTER TABLE `lc_slides`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lc_users`
--
ALTER TABLE `lc_users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `lc_users_groups`
--
ALTER TABLE `lc_users_groups`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 限制导出的表
--

--
-- 限制表 `lc_ad_ad_positions`
--
ALTER TABLE `lc_ad_ad_positions`
  ADD CONSTRAINT `lc_ad_ad_positions_ibfk_1` FOREIGN KEY (`advertisement_id`) REFERENCES `lc_advertisements` (`id`),
  ADD CONSTRAINT `lc_ad_ad_positions_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `lc_advertisement_positions` (`id`);

--
-- 限制表 `lc_category_contents`
--
ALTER TABLE `lc_category_contents`
  ADD CONSTRAINT `lc_category_contents_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `lc_categories` (`id`),
  ADD CONSTRAINT `lc_category_contents_ibfk_2` FOREIGN KEY (`content_id`) REFERENCES `lc_contents` (`id`);

--
-- 限制表 `lc_comments`
--
ALTER TABLE `lc_comments`
  ADD CONSTRAINT `lc_comments_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `lc_comments` (`id`),
  ADD CONSTRAINT `lc_comments_ibfk_2` FOREIGN KEY (`content_id`) REFERENCES `lc_contents` (`id`);

--
-- 限制表 `lc_links`
--
ALTER TABLE `lc_links`
  ADD CONSTRAINT `lc_links_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `lc_link_groups` (`id`);

--
-- 限制表 `lc_pages`
--
ALTER TABLE `lc_pages`
  ADD CONSTRAINT `lc_pages_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `lc_pages` (`id`);

--
-- 限制表 `lc_top_contents`
--
ALTER TABLE `lc_top_contents`
  ADD CONSTRAINT `lc_top_contents_ibfk_1` FOREIGN KEY (`content_id`) REFERENCES `lc_contents` (`id`);

--
-- 限制表 `lc_users_groups`
--
ALTER TABLE `lc_users_groups`
  ADD CONSTRAINT `fk_users_groups_groups1` FOREIGN KEY (`group_id`) REFERENCES `lc_groups` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_users_groups_users1` FOREIGN KEY (`user_id`) REFERENCES `lc_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
