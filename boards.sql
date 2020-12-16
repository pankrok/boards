SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bdev`
--

-- --------------------------------------------------------

--
-- Table structure for table `brd_boards`
--

CREATE TABLE `brd_boards` (
  `id` int(11) NOT NULL,
  `board_name` varchar(255) NOT NULL,
  `board_description` varchar(1023) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `board_order` int(11) DEFAULT NULL,
  `plots_number` int(11) DEFAULT '0',
  `posts_number` int(11) DEFAULT '0',
  `last_post_date` int(11) DEFAULT NULL,
  `last_post_author` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '1999-12-31 23:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `brd_boxes`
--

CREATE TABLE `brd_boxes` (
  `id` int(11) NOT NULL,
  `costum_id` int(11) NOT NULL,
  `costum` tinyint(1) NOT NULL DEFAULT '1',
  `engine` varchar(32) NOT NULL DEFAULT 'custom'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `brd_boxes`
--

INSERT INTO `brd_boxes` (`id`, `costum_id`, `costum`, `engine`) VALUES
(1, 1, 0, 'userdata'),
(2, 2, 0, 'statistics'),
(3, 3, 1, 'custom');

-- --------------------------------------------------------

--
-- Table structure for table `brd_categories`
--

CREATE TABLE `brd_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(527) NOT NULL,
  `category_order` int(4) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '1999-12-31 23:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `brd_chatbox`
--

CREATE TABLE `brd_chatbox` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` varchar(2048) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '2019-12-31 23:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `brd_costum_boxes`
--

CREATE TABLE `brd_costum_boxes` (
  `id` int(11) NOT NULL,
  `translate` tinyint(1) NOT NULL DEFAULT '0',
  `name_prefix` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `html` blob NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `brd_costum_boxes`
--

INSERT INTO `brd_costum_boxes` (`id`, `translate`, `name_prefix`, `name`, `html`, `updated_at`, `created_at`) VALUES
(3, 1, '<i class=\"fa fa-bullhorn\"></i>', 'Announcements', 0x3c64697620636c6173733d226974656d5f6d61696e223e090909090d0a093c703e536f6d6520717569636b206578616d706c65207465787420746f206275696c64206f6e207468652063617264207469746c6520616e64206d616b65207570207468652062756c6b206f66207468652063617264277320636f6e74656e742e3c2f703e0d0a3c2f6469763e, '2020-11-23 10:33:28', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `brd_groups`
--

CREATE TABLE `brd_groups` (
  `id` int(11) NOT NULL,
  `username_html` varchar(255) NOT NULL,
  `grupe_name` varchar(255) NOT NULL,
  `grupe_level` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `brd_groups`
--

INSERT INTO `brd_groups` (`id`, `username_html`, `grupe_name`, `grupe_level`, `updated_at`, `created_at`) VALUES
(1, '<b class=\"fa fa-plus-circle\"> {{username}}</b>', 'user', 1, '2020-11-06 14:06:45', '0000-00-00 00:00:00'),
(2, '<strong style=\"color:red\"><i class=\"fas fa-circle-notch fa-spin\"></i> {{username}}</strong>', '<strong style=\"color:red\"><i class=\"fas fa-circle-notch fa-spin\"></i> Admin</strong>', 10, '2020-11-06 14:06:32', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `brd_images`
--

CREATE TABLE `brd_images` (
  `id` int(11) NOT NULL,
  `original` varchar(255) NOT NULL,
  `_38` varchar(255) NOT NULL,
  `_85` varchar(255) NOT NULL,
  `_150` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `brd_likeit`
--

CREATE TABLE `brd_likeit` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `brd_plotread`
--

CREATE TABLE `brd_plotread` (
  `id` int(11) NOT NULL,
  `plot_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `timeline` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `brd_plots`
--

CREATE TABLE `brd_plots` (
  `id` int(11) NOT NULL,
  `plot_name` varchar(255) DEFAULT NULL,
  `plot_tags` varchar(1023) DEFAULT NULL,
  `board_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `plot_active` tinyint(1) DEFAULT '1',
  `pinned` tinyint(1) DEFAULT NULL,
  `pinned_order` int(11) DEFAULT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `posts_nuber` int(11) DEFAULT '0',
  `views` int(11) NOT NULL DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `brd_plugins`
--

CREATE TABLE `brd_plugins` (
  `id` int(11) NOT NULL,
  `plugin_name` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `install` tinyint(1) NOT NULL,
  `version` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `brd_plugins`
--

INSERT INTO `brd_plugins` (`id`, `plugin_name`, `active`, `install`, `version`, `updated_at`, `created_at`) VALUES
(1, 'ExamplePlugin', 1, 1, '1.0', '2020-12-15 11:24:02', '2020-12-09 14:18:53');

-- --------------------------------------------------------

--
-- Table structure for table `brd_posts`
--

CREATE TABLE `brd_posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plot_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `post_reputation` int(11) NOT NULL DEFAULT '0',
  `hidden` tinyint(1) NOT NULL DEFAULT '1',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '1999-12-31 23:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `brd_skins`
--

CREATE TABLE `brd_skins` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `dirname` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `brd_skins`
--

INSERT INTO `brd_skins` (`id`, `name`, `dirname`, `author`, `version`, `active`, `updated_at`, `created_at`) VALUES
(2, 'simple', 'simple', 'PanKrok', '1.0', 1, '2020-12-16 13:53:38', '2020-11-24 13:51:27');

-- --------------------------------------------------------

--
-- Table structure for table `brd_skins_boxes`
--

CREATE TABLE `brd_skins_boxes` (
  `id` int(11) NOT NULL,
  `skin_id` int(11) NOT NULL,
  `box_id` int(11) NOT NULL,
  `side` varchar(8) DEFAULT 'right',
  `box_order` int(11) NOT NULL DEFAULT '0',
  `active` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `brd_skins_boxes`
--

INSERT INTO `brd_skins_boxes` (`id`, `skin_id`, `box_id`, `side`, `box_order`, `active`) VALUES
(1, 2, 1, 'right', 0, '{\"home\":1}'),
(2, 2, 2, 'right', 1, '{\"home\":1}'),
(3, 2, 3, 'top', 6, '{\"home\":1,\"category.getCategory\":1,\"board.getBoard\":0,\"board.getPlot\":0,\"board.newPlot\":1,\"auth.signin\":0,\"auth.signup\":0,\"user.profile\":0,\"userlist\":1}');

-- --------------------------------------------------------

--
-- Table structure for table `brd_userdata`
--

CREATE TABLE `brd_userdata` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `rank` varchar(255) NOT NULL,
  `sex` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `website` varchar(255) NOT NULL,
  `bday` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `brd_users`
--

CREATE TABLE `brd_users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_group` varchar(255) DEFAULT NULL,
  `additional_groups` varchar(255) DEFAULT NULL,
  `main_group` int(11) DEFAULT NULL,
  `admin_lvl` int(2) NOT NULL,
  `posts` varchar(255) NOT NULL DEFAULT '0',
  `plots` varchar(255) NOT NULL DEFAULT '0',
  `avatar` int(11) DEFAULT NULL,
  `reputation` int(11) NOT NULL DEFAULT '0',
  `last_active` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_post` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `online_time` int(11) NOT NULL,
  `recommended_by` varchar(255) NOT NULL,
  `pm` varchar(255) NOT NULL,
  `recive_pm` varchar(255) NOT NULL,
  `brithday` varchar(255) NOT NULL,
  `brithday_visibility` varchar(255) NOT NULL,
  `mail_visibility` varchar(255) NOT NULL,
  `active_visibility` varchar(255) NOT NULL,
  `timezone` int(255) NOT NULL,
  `friends` varchar(255) NOT NULL,
  `ignore_users` varchar(255) NOT NULL,
  `style` varchar(255) NOT NULL,
  `away` tinyint(1) NOT NULL DEFAULT '0',
  `away_start` varchar(255) NOT NULL,
  `away_end` varchar(255) NOT NULL,
  `lang` varchar(5) NOT NULL DEFAULT 'pl_PL',
  `warn_level` int(100) NOT NULL DEFAULT '0',
  `priv_notes` varchar(2048) NOT NULL,
  `lostpw` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '1999-12-31 23:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brd_boards`
--
ALTER TABLE `brd_boards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brd_fgk` (`category_id`);

--
-- Indexes for table `brd_boxes`
--
ALTER TABLE `brd_boxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brd_categories`
--
ALTER TABLE `brd_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brd_chatbox`
--
ALTER TABLE `brd_chatbox`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `brd_costum_boxes`
--
ALTER TABLE `brd_costum_boxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brd_groups`
--
ALTER TABLE `brd_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brd_images`
--
ALTER TABLE `brd_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brd_likeit`
--
ALTER TABLE `brd_likeit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `brd_plotread`
--
ALTER TABLE `brd_plotread`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brd_plots`
--
ALTER TABLE `brd_plots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brd_fgk_2` (`author_id`),
  ADD KEY `brd_fgk_3` (`board_id`);

--
-- Indexes for table `brd_plugins`
--
ALTER TABLE `brd_plugins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brd_posts`
--
ALTER TABLE `brd_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plot_id` (`plot_id`),
  ADD KEY `brd_posts_ibfk_2` (`user_id`);

--
-- Indexes for table `brd_skins`
--
ALTER TABLE `brd_skins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brd_skins_boxes`
--
ALTER TABLE `brd_skins_boxes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `box_id` (`box_id`),
  ADD KEY `brd_skins_boxes_ibfk_1` (`skin_id`);

--
-- Indexes for table `brd_userdata`
--
ALTER TABLE `brd_userdata`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `brd_users`
--
ALTER TABLE `brd_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `main_group` (`main_group`),
  ADD KEY `avatar` (`avatar`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brd_boards`
--
ALTER TABLE `brd_boards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brd_boxes`
--
ALTER TABLE `brd_boxes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `brd_categories`
--
ALTER TABLE `brd_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brd_chatbox`
--
ALTER TABLE `brd_chatbox`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brd_costum_boxes`
--
ALTER TABLE `brd_costum_boxes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `brd_groups`
--
ALTER TABLE `brd_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `brd_images`
--
ALTER TABLE `brd_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brd_likeit`
--
ALTER TABLE `brd_likeit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brd_plotread`
--
ALTER TABLE `brd_plotread`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brd_plots`
--
ALTER TABLE `brd_plots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brd_plugins`
--
ALTER TABLE `brd_plugins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `brd_posts`
--
ALTER TABLE `brd_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brd_skins`
--
ALTER TABLE `brd_skins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `brd_skins_boxes`
--
ALTER TABLE `brd_skins_boxes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `brd_userdata`
--
ALTER TABLE `brd_userdata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brd_users`
--
ALTER TABLE `brd_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `brd_boards`
--
ALTER TABLE `brd_boards`
  ADD CONSTRAINT `brd_fgk` FOREIGN KEY (`category_id`) REFERENCES `brd_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `brd_chatbox`
--
ALTER TABLE `brd_chatbox`
  ADD CONSTRAINT `brd_chatbox_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `brd_users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `brd_likeit`
--
ALTER TABLE `brd_likeit`
  ADD CONSTRAINT `brd_likeit_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `brd_posts` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `brd_likeit_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `brd_users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `brd_plots`
--
ALTER TABLE `brd_plots`
  ADD CONSTRAINT `brd_fgk_2` FOREIGN KEY (`author_id`) REFERENCES `brd_users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `brd_fgk_3` FOREIGN KEY (`board_id`) REFERENCES `brd_boards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `brd_posts`
--
ALTER TABLE `brd_posts`
  ADD CONSTRAINT `brd_posts_ibfk_1` FOREIGN KEY (`plot_id`) REFERENCES `brd_plots` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `brd_posts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `brd_users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `brd_skins_boxes`
--
ALTER TABLE `brd_skins_boxes`
  ADD CONSTRAINT `brd_skins_boxes_ibfk_1` FOREIGN KEY (`skin_id`) REFERENCES `brd_skins` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `brd_skins_boxes_ibfk_2` FOREIGN KEY (`box_id`) REFERENCES `brd_boxes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `brd_userdata`
--
ALTER TABLE `brd_userdata`
  ADD CONSTRAINT `brd_userdata_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `brd_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `brd_users`
--
ALTER TABLE `brd_users`
  ADD CONSTRAINT `brd_users_ibfk_1` FOREIGN KEY (`main_group`) REFERENCES `brd_groups` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `brd_users_ibfk_2` FOREIGN KEY (`avatar`) REFERENCES `brd_images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
