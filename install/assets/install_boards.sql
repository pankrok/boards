SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


-- --------------------------------------------------------

--
-- Table structure for table `t1_additional_fields`
--

CREATE TABLE `t1_additional_fields` (
  `id` int(11) NOT NULL,
  `add_name` varchar(255) NOT NULL,
  `add_type` varchar(32) NOT NULL,
  `add_values` text,
  `add_require` tinyint(1) NOT NULL DEFAULT '0',
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `t1_admin_logs`
--

CREATE TABLE `t1_admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `log` blob NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '1999-12-31 23:00:01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `t1_boards`
--

CREATE TABLE `t1_boards` (
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
-- Table structure for table `t1_boxes`
--

CREATE TABLE `t1_boxes` (
  `id` int(11) NOT NULL,
  `costum_id` int(11) NOT NULL,
  `costum` tinyint(1) NOT NULL DEFAULT '1',
  `engine` varchar(32) NOT NULL DEFAULT 'custom'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `t1_boxes`
--

INSERT INTO `t1_boxes` (`id`, `costum_id`, `costum`, `engine`) VALUES
(1, 1, 0, 'userdata'),
(2, 2, 0, 'statistics'),
(3, 3, 0, 'chatbox'),
(4, 4, 1, 'custom');

-- --------------------------------------------------------

--
-- Table structure for table `t1_categories`
--

CREATE TABLE `t1_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(527) NOT NULL,
  `category_order` int(4) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '1999-12-31 23:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `t1_chatbox`
--

CREATE TABLE `t1_chatbox` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` varchar(2048) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '2019-12-31 23:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `t1_conversations`
--

CREATE TABLE `t1_conversations` (
  `id` int(11) NOT NULL,
  `admin` tinyblob,
  `users` mediumblob,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '1999-12-31 23:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `t1_costum_boxes`
--

CREATE TABLE `t1_costum_boxes` (
  `id` int(11) NOT NULL,
  `translate` tinyint(1) NOT NULL DEFAULT '0',
  `name_prefix` varchar(255) NOT NULL,
  `name` varchar(128) NOT NULL,
  `html` blob,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `t1_costum_boxes`
--

INSERT INTO `t1_costum_boxes` (`id`, `translate`, `name_prefix`, `name`, `html`, `updated_at`, `created_at`) VALUES
(1, 0, 'system', 'userdata', NULL, '2021-01-14 13:23:52', '2000-01-01 00:00:00'),
(2, 0, 'system', 'statistics', NULL, '2021-01-14 13:24:16', '2000-01-01 00:00:00'),
(3, 0, 'system', 'chatbox', NULL, '2021-03-18 10:02:22', '2000-01-01 00:00:00'),
(4, 1, '<i class=\"fa fa-bullhorn\"></i>', 'Announcements', 0x3c64697620636c6173733d226974656d5f6d61696e223e090909090d0a093c703e536f6d6520717569636b206578616d706c65207465787420746f206275696c64206f6e207468652063617264207469746c6520616e64206d616b65207570207468652062756c6b206f66207468652063617264277320636f6e74656e742e3c2f703e0d0a3c2f6469763e, '2020-11-23 10:33:28', '2000-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t1_groups`
--

CREATE TABLE `t1_groups` (
  `id` int(11) NOT NULL,
  `username_html` varchar(255) NOT NULL,
  `grupe_name` varchar(255) NOT NULL,
  `grupe_level` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `t1_groups`
--

INSERT INTO `t1_groups` (`id`, `username_html`, `grupe_name`, `grupe_level`, `updated_at`, `created_at`) VALUES
(1, '<strong style=\"text-shadow: 1px 1px 10px red; color: red; font-weight: bold;\"><i class=\"fas fa-circle-notch fa-spin\"></i> {{username}}</strong>', '<strong style=\"text-shadow: 1px 1px 10px red; color: red; font-weight: bold;\"><i class=\"fas fa-circle-notch fa-spin\"></i> Admin</strong>', 10, '2021-04-12 12:17:23', '2000-01-01 00:00:00'),
(2, '<strong style=\"text-shadow: 1px 1px 10px green; color: green; font-weight: bold;\"><i class=\"fas fa-hammer\"></i> {{username}}</strong>', '<strong style=\"text-shadow: 1px 1px 10px green; color: green; font-weight: bold;\"><i class=\"fas fa-hammer\"></i> Moderator</strong>', 5, '2021-04-12 12:18:13', '2020-12-18 09:06:55'),
(3, '<b class=\"fa fa-plus-circle\"> {{username}}</b>', '<i class=\"fa fa-plus-circle\"></i> user', 1, '2021-04-13 11:27:29', '2000-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t1_images`
--

CREATE TABLE `t1_images` (
  `id` int(11) NOT NULL,
  `original` varchar(255) NOT NULL,
  `_38` varchar(255) NOT NULL,
  `_85` varchar(255) NOT NULL,
  `_150` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `t1_likeit`
--

CREATE TABLE `t1_likeit` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `t1_mailbox`
--

CREATE TABLE `t1_mailbox` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `conversation_id` int(11) DEFAULT NULL,
  `unread` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `t1_mail_logs`
--

CREATE TABLE `t1_mail_logs` (
  `id` int(11) NOT NULL,
  `recipient` varchar(128) DEFAULT NULL,
  `is_send` tinyint(1) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `content_txt` text NOT NULL,
  `content_html` text NOT NULL,
  `log` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '1999-12-31 22:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `t1_menu`
--

CREATE TABLE `t1_menu` (
  `id` int(11) NOT NULL,
  `url` text NOT NULL,
  `translate` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `url_order` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `t1_message`
--

CREATE TABLE `t1_message` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `conversation_id` int(11) DEFAULT NULL,
  `body` blob NOT NULL,
  `read_by` tinyblob,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '1999-12-31 23:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `t1_pages`
--

CREATE TABLE `t1_pages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` longblob,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `system` varchar(31) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `t1_pages`
--

INSERT INTO `t1_pages` (`id`, `name`, `content`, `active`, `system`, `updated_at`, `created_at`) VALUES
(1, '<i class=\"fas fa-users\"></i> Members', NULL, 1, 'userlist', '2020-12-21 10:15:01', '2000-01-01 00:00:00'),
(2, '<i class=\"fas fa-gavel\"></i> Regulations', 0x3c646c3e3c64643e3c7370616e207374796c653d22636f6c6f723a20726762283230342c203230342c20323034293b223e3c656d3e5b33325d20536564207574207065727370696369617469732c20756e6465206f6d6e69732069737465206e61747573206572726f7220736974200d0a766f6c7570746174656d206163637573616e7469756d20646f6c6f72656d717565206c617564616e7469756d2c20746f74616d2072656d206170657269616d206561717565200d0a697073612c207175616520616220696c6c6f20696e76656e746f726520766572697461746973206574207175617369206172636869746563746f20626561746165207669746165200d0a64696374612073756e742c206578706c696361626f2e204e656d6f20656e696d20697073616d20766f6c7570746174656d2c207175696120766f6c7570746173207369742c200d0a61737065726e6174757220617574206f646974206175742066756769742c20736564207175696120636f6e73657175756e747572206d61676e6920646f6c6f72657320656f732c200d0a71756920726174696f6e6520766f6c7570746174656d207365717569206e65736369756e742c206e6571756520706f72726f20717569737175616d206573742c2071756920646f3c623e6c6f72656d20697073756d3c2f623e2c2071756961203c623e646f6c6f72207369742c20616d65742c20636f6e73656374657475722c2061646970697363693c2f623e20763c623e656c69742c207365643c2f623e2071756961206e6f6e206e756d7175616d203c623e65697573206d6f643c2f623e69203c623e74656d706f723c2f623e61203c623e696e636964756e742c207574206c61626f726520657420646f6c6f7265206d61676e613c2f623e6d203c623e616c697175613c2f623e6d207175616572617420766f6c7570746174656d2e203c623e557420656e696d206164206d696e696d3c2f623e61203c623e76656e69616d2c2071756973206e6f737472753c2f623e6d203c623e657865726369746174696f6e3c2f623e656d203c623e756c6c616d20636f3c2f623e72706f7269732073757363697069743c623e206c61626f72696f733c2f623e616d2c203c623e6e69736920757420616c697175696420657820656120636f6d6d6f646920636f6e7365717561743c2f623e75723f203c623e5175697320617574653c2f623e6d2076656c2065756d203c623e6975726520726570726568656e64657269742c3c2f623e20717569203c623e696e3c2f623e206561203c623e766f6c7570746174652076656c697420657373653c2f623e2c207175616d206e6968696c206d6f6c657374696165203c623e633c2f623e6f6e73657175617475722c2076656c203c623e696c6c756d3c2f623e2c20717569203c623e646f6c6f72653c2f623e6d203c623e65753c2f623e6d203c623e6675676961743c2f623e2c2071756f20766f6c7570746173203c623e6e756c6c612070617269617475723c2f623e3f3c2f656d3e3c2f7370616e3e3c2f64643e0d0a3c64643e3c7370616e207374796c653d22636f6c6f723a20726762283230342c203230342c20323034293b223e3c693e5b33335d204174207665726f20656f73206574206163637573616d757320657420697573746f206f64696f206469676e697373696d6f7320647563696d75732c200d0a71756920626c616e646974696973207072616573656e7469756d20766f6c7570746174756d2064656c656e69746920617471756520636f7272757074692c2071756f73200d0a646f6c6f7265732065742071756173206d6f6c657374696173203c623e65786365707475723c2f623e69203c623e73696e742c206f626361656361743c2f623e69203c623e6375706964697461743c2f623e65203c623e6e6f6e2070726f3c2f623e763c623e6964656e743c2f623e2c2073696d696c69717565203c623e73756e7420696e2063756c70613c2f623e2c203c623e717569206f666669636961206465736572756e74206d6f6c6c69743c2f623e6961203c623e616e696d3c2f623e692c203c623e696420657374206c61626f72756d3c2f623e0d0a20657420646f6c6f72756d20667567612e20457420686172756d2071756964656d20726572756d20666163696c697320657374206574206578706564697461200d0a64697374696e6374696f2e204e616d206c696265726f2074656d706f72652c2063756d20736f6c757461206e6f6269732065737420656c6967656e6469206f7074696f2c200d0a63756d717565206e6968696c20696d70656469742c2071756f206d696e75732069642c2071756f64206d6178696d6520706c61636561742c20666163657265200d0a706f7373696d75732c206f6d6e697320766f6c757074617320617373756d656e6461206573742c206f6d6e697320646f6c6f7220726570656c6c656e6475732e200d0a54656d706f726962757320617574656d2071756962757364616d20657420617574206f6666696369697320646562697469732061757420726572756d200d0a6e65636573736974617469627573207361657065206576656e6965742c20757420657420766f6c7570746174657320726570756469616e6461652073696e74206574200d0a6d6f6c657374696165206e6f6e207265637573616e6461652e2049746171756520656172756d20726572756d206869632074656e6574757220612073617069656e7465200d0a64656c65637475732c20757420617574207265696369656e64697320766f6c757074617469627573206d61696f72657320616c69617320636f6e736571756174757220617574200d0a706572666572656e64697320646f6c6f7269627573206173706572696f72657320726570656c6c61742e3c2f693e3c2f7370616e3e3c2f64643e3c2f646c3e3c703e3c7370616e207374796c653d22636f6c6f723a20726762283230342c203230342c20323034293b223e3c62723e3c2f7370616e3e3c2f703e, 1, NULL, '2020-12-21 13:04:37', '2000-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t1_plotread`
--

CREATE TABLE `t1_plotread` (
  `id` int(11) NOT NULL,
  `plot_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `timeline` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `t1_plots`
--

CREATE TABLE `t1_plots` (
  `id` int(11) NOT NULL,
  `plot_name` varchar(255) DEFAULT NULL,
  `plot_tags` varchar(1023) DEFAULT NULL,
  `board_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `plot_active` tinyint(1) DEFAULT '1',
  `pinned` tinyint(1) DEFAULT NULL,
  `pinned_order` int(11) DEFAULT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `posts_nuber` int(11) DEFAULT '0',
  `views` int(11) NOT NULL DEFAULT '0',
  `stars` float DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `t1_plugins`
--

CREATE TABLE `t1_plugins` (
  `id` int(11) NOT NULL,
  `plugin_name` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `install` tinyint(1) NOT NULL DEFAULT '0',
  `version` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `t1_posts`
--

CREATE TABLE `t1_posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `plot_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `post_reputation` int(11) NOT NULL DEFAULT '0',
  `hidden` tinyint(1) DEFAULT '0',
  `edit_by` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '1999-12-31 23:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `t1_rates`
--

CREATE TABLE `t1_rates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `plot_id` int(11) NOT NULL,
  `rate` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `t1_secret`
--

CREATE TABLE `t1_secret` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `secret` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `t1_skins`
--

CREATE TABLE `t1_skins` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `dirname` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `t1_skins`
--

INSERT INTO `t1_skins` (`id`, `name`, `dirname`, `author`, `version`, `active`, `updated_at`, `created_at`) VALUES
(1, 'modern', 'modern', 'PanKrok', '1.0', 1, '2021-06-22 00:00:00', '2021-06-22 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t1_skins_boxes`
--

CREATE TABLE `t1_skins_boxes` (
  `id` int(11) NOT NULL,
  `skin_id` int(11) NOT NULL,
  `box_id` int(11) NOT NULL,
  `side` varchar(8) DEFAULT 'right',
  `box_order` int(11) NOT NULL DEFAULT '0',
  `hide_on_mobile` tinyint(1) NOT NULL DEFAULT '0',
  `active` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `t1_skins_boxes`
--

INSERT INTO `t1_skins_boxes` (`id`, `skin_id`, `box_id`, `side`, `box_order`, `hide_on_mobile`, `active`) VALUES
(1, 1, 1, 'right', 5, 0, '{\"home\":1}'),
(2, 1, 2, 'right', 2, 0, '{\"home\":1}'),
(3, 1, 3, 'iTop', 0, 0, '{\"home\":1}'),
(4, 1, 4, 'top', 0, 0, '{\"home\":1,\"category.getCategory\":1,\"board.getBoard\":1,\"board.getPlot\":1,\"board.newPlot\":1,\"auth.signin\":1,\"auth.signup\":1,\"user.profile\":1,\"userlist\":1}');

-- --------------------------------------------------------

--
-- Table structure for table `t1_userdata`
--

CREATE TABLE `t1_userdata` (
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
-- Table structure for table `t1_users`
--

CREATE TABLE `t1_users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `user_group` varchar(255) DEFAULT NULL,
  `additional_groups` varchar(255) DEFAULT NULL,
  `main_group` int(11) DEFAULT NULL,
  `admin_lvl` int(2) NOT NULL,
  `posts` varchar(255) NOT NULL DEFAULT '0',
  `plots` varchar(255) NOT NULL DEFAULT '0',
  `avatar` int(11) DEFAULT NULL,
  `reputation` int(11) NOT NULL DEFAULT '0',
  `last_active` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_post` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00',
  `online_time` int(11) NOT NULL,
  `recommended_by` varchar(255) DEFAULT NULL,
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
  `banned` tinyint(1) NOT NULL DEFAULT '0',
  `priv_notes` varchar(2048) NOT NULL,
  `lostpw` varchar(255) DEFAULT NULL,
  `tfa` tinyint(1) NOT NULL DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '1999-12-31 23:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `t1_user_additional_fields`
--

CREATE TABLE `t1_user_additional_fields` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `add_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `t1_additional_fields`
--
ALTER TABLE `t1_additional_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t1_admin_logs`
--
ALTER TABLE `t1_admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `t1_boards`
--
ALTER TABLE `t1_boards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `t1_fgk` (`category_id`);

--
-- Indexes for table `t1_boxes`
--
ALTER TABLE `t1_boxes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `costum_id` (`costum_id`);

--
-- Indexes for table `t1_categories`
--
ALTER TABLE `t1_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t1_chatbox`
--
ALTER TABLE `t1_chatbox`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `t1_conversations`
--
ALTER TABLE `t1_conversations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t1_costum_boxes`
--
ALTER TABLE `t1_costum_boxes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `t1_groups`
--
ALTER TABLE `t1_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t1_images`
--
ALTER TABLE `t1_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t1_likeit`
--
ALTER TABLE `t1_likeit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `t1_mailbox`
--
ALTER TABLE `t1_mailbox`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `message_id` (`message_id`),
  ADD KEY `conversation_id` (`conversation_id`);

--
-- Indexes for table `t1_mail_logs`
--
ALTER TABLE `t1_mail_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t1_menu`
--
ALTER TABLE `t1_menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t1_message`
--
ALTER TABLE `t1_message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users` (`conversation_id`,`sender_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `t1_pages`
--
ALTER TABLE `t1_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `SECONDARY` (`name`(191));

--
-- Indexes for table `t1_plotread`
--
ALTER TABLE `t1_plotread`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`) USING BTREE,
  ADD KEY `plot_id` (`plot_id`) USING BTREE;

--
-- Indexes for table `t1_plots`
--
ALTER TABLE `t1_plots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `t1_fgk_2` (`author_id`),
  ADD KEY `t1_fgk_3` (`board_id`);

--
-- Indexes for table `t1_plugins`
--
ALTER TABLE `t1_plugins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t1_posts`
--
ALTER TABLE `t1_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plot_id` (`plot_id`),
  ADD KEY `t1_posts_ibfk_2` (`user_id`),
  ADD KEY `edit_by` (`edit_by`);

--
-- Indexes for table `t1_rates`
--
ALTER TABLE `t1_rates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plot_id` (`plot_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `t1_secret`
--
ALTER TABLE `t1_secret`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`) USING BTREE;

--
-- Indexes for table `t1_skins`
--
ALTER TABLE `t1_skins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t1_skins_boxes`
--
ALTER TABLE `t1_skins_boxes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `box_id` (`box_id`),
  ADD KEY `t1_skins_boxes_ibfk_1` (`skin_id`);

--
-- Indexes for table `t1_userdata`
--
ALTER TABLE `t1_userdata`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `t1_users`
--
ALTER TABLE `t1_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `main_group` (`main_group`),
  ADD KEY `avatar` (`avatar`);

--
-- Indexes for table `t1_user_additional_fields`
--
ALTER TABLE `t1_user_additional_fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user_id`),
  ADD KEY `additional_field_id` (`field_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `t1_additional_fields`
--
ALTER TABLE `t1_additional_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_admin_logs`
--
ALTER TABLE `t1_admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_boards`
--
ALTER TABLE `t1_boards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_boxes`
--
ALTER TABLE `t1_boxes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `t1_categories`
--
ALTER TABLE `t1_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_chatbox`
--
ALTER TABLE `t1_chatbox`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_conversations`
--
ALTER TABLE `t1_conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_costum_boxes`
--
ALTER TABLE `t1_costum_boxes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `t1_groups`
--
ALTER TABLE `t1_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `t1_images`
--
ALTER TABLE `t1_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_likeit`
--
ALTER TABLE `t1_likeit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_mailbox`
--
ALTER TABLE `t1_mailbox`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_mail_logs`
--
ALTER TABLE `t1_mail_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_menu`
--
ALTER TABLE `t1_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_message`
--
ALTER TABLE `t1_message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_pages`
--
ALTER TABLE `t1_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `t1_plotread`
--
ALTER TABLE `t1_plotread`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_plots`
--
ALTER TABLE `t1_plots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_plugins`
--
ALTER TABLE `t1_plugins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_posts`
--
ALTER TABLE `t1_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_rates`
--
ALTER TABLE `t1_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_secret`
--
ALTER TABLE `t1_secret`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_skins`
--
ALTER TABLE `t1_skins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `t1_skins_boxes`
--
ALTER TABLE `t1_skins_boxes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `t1_userdata`
--
ALTER TABLE `t1_userdata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_users`
--
ALTER TABLE `t1_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_user_additional_fields`
--
ALTER TABLE `t1_user_additional_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `t1_admin_logs`
--
ALTER TABLE `t1_admin_logs`
  ADD CONSTRAINT `t1_admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `t1_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `t1_boards`
--
ALTER TABLE `t1_boards`
  ADD CONSTRAINT `t1_fgk` FOREIGN KEY (`category_id`) REFERENCES `t1_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `t1_boxes`
--
ALTER TABLE `t1_boxes`
  ADD CONSTRAINT `t1_boxes_ibfk_1` FOREIGN KEY (`costum_id`) REFERENCES `t1_costum_boxes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `t1_chatbox`
--
ALTER TABLE `t1_chatbox`
  ADD CONSTRAINT `t1_chatbox_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `t1_users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `t1_likeit`
--
ALTER TABLE `t1_likeit`
  ADD CONSTRAINT `t1_likeit_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `t1_posts` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `t1_likeit_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `t1_users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `t1_mailbox`
--
ALTER TABLE `t1_mailbox`
  ADD CONSTRAINT `t1_mailbox_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `t1_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `t1_mailbox_ibfk_2` FOREIGN KEY (`message_id`) REFERENCES `t1_message` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `t1_mailbox_ibfk_3` FOREIGN KEY (`conversation_id`) REFERENCES `t1_conversations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `t1_message`
--
ALTER TABLE `t1_message`
  ADD CONSTRAINT `t1_message_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `t1_conversations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `t1_message_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `t1_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `t1_plotread`
--
ALTER TABLE `t1_plotread`
  ADD CONSTRAINT `t1_plotread_ibfk_1` FOREIGN KEY (`plot_id`) REFERENCES `t1_plots` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `t1_plotread_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `t1_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `t1_plots`
--
ALTER TABLE `t1_plots`
  ADD CONSTRAINT `t1_fgk_2` FOREIGN KEY (`author_id`) REFERENCES `t1_users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `t1_fgk_3` FOREIGN KEY (`board_id`) REFERENCES `t1_boards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `t1_posts`
--
ALTER TABLE `t1_posts`
  ADD CONSTRAINT `t1_posts_ibfk_1` FOREIGN KEY (`plot_id`) REFERENCES `t1_plots` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `t1_posts_ibfk_3` FOREIGN KEY (`edit_by`) REFERENCES `t1_users` (`username`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `t1_posts_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `t1_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `t1_rates`
--
ALTER TABLE `t1_rates`
  ADD CONSTRAINT `t1_rates_ibfk_1` FOREIGN KEY (`plot_id`) REFERENCES `t1_plots` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `t1_rates_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `t1_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `t1_secret`
--
ALTER TABLE `t1_secret`
  ADD CONSTRAINT `t1_secret_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `t1_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `t1_skins_boxes`
--
ALTER TABLE `t1_skins_boxes`
  ADD CONSTRAINT `t1_skins_boxes_ibfk_1` FOREIGN KEY (`skin_id`) REFERENCES `t1_skins` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `t1_skins_boxes_ibfk_2` FOREIGN KEY (`box_id`) REFERENCES `t1_boxes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `t1_userdata`
--
ALTER TABLE `t1_userdata`
  ADD CONSTRAINT `t1_userdata_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `t1_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `t1_users`
--
ALTER TABLE `t1_users`
  ADD CONSTRAINT `t1_users_ibfk_1` FOREIGN KEY (`main_group`) REFERENCES `t1_groups` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `t1_users_ibfk_2` FOREIGN KEY (`avatar`) REFERENCES `t1_images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `t1_user_additional_fields`
--
ALTER TABLE `t1_user_additional_fields`
  ADD CONSTRAINT `t1_user_additional_fields_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `t1_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_additional_fields_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `t1_additional_fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
