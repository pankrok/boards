

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `brd_boards` (
  `id` int(11) NOT NULL,
  `board_name` varchar(255) NOT NULL,
  `board_description` varchar(1023) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `board_order` int(11) DEFAULT NULL,
  `plots_number` int(11) DEFAULT NULL,
  `posts_number` int(11) DEFAULT NULL,
  `last_post_date` int(11) DEFAULT NULL,
  `last_post_author` varchar(255) DEFAULT NULL,
  `visability` tinyint(1) DEFAULT '1',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '1999-12-31 23:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `brd_categories`
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
-- Struktura tabeli dla tabeli `brd_chatbox`
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
-- Struktura tabeli dla tabeli `brd_groups`
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
-- Zrzut danych tabeli `brd_groups`
--

INSERT INTO `brd_groups` (`id`, `username_html`, `grupe_name`, `grupe_level`, `updated_at`, `created_at`) VALUES
(1, '<i class=\"fa fa-star\" aria-hidden=\"true\" style=\"color:red\">{{username}}</i>\r\n', '<strong style=\"color:red\"><i class=\"fas fa-circle-notch fa-spin\"></i> Admin</strong>', 10, '2020-11-06 14:06:32', '0000-00-00 00:00:00'),
(2, '{{username}}', 'user', 1, '2020-11-06 14:06:45', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `brd_images`
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
-- Struktura tabeli dla tabeli `brd_plotread`
--

CREATE TABLE `brd_plotread` (
  `id` int(11) NOT NULL,
  `plot_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `timeline` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `brd_plots`
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
  `views` int(11) NOT NULL DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `brd_plugins`
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

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `brd_posts`
--

CREATE TABLE `brd_posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plot_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT '1',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '1999-12-31 23:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `brd_users`
--

CREATE TABLE `brd_users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_group` varchar(255) DEFAULT NULL,
  `additional_groups` varchar(255) DEFAULT NULL,
  `main_group` varchar(255) DEFAULT NULL,
  `posts` varchar(255) NOT NULL DEFAULT '0',
  `plots` varchar(255) NOT NULL DEFAULT '0',
  `avatar` varchar(255) DEFAULT NULL,
  `reg_date` varchar(255) NOT NULL,
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
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `brd_boards`
--
ALTER TABLE `brd_boards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brd_fgk` (`category_id`);

--
-- Indeksy dla tabeli `brd_categories`
--
ALTER TABLE `brd_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `brd_chatbox`
--
ALTER TABLE `brd_chatbox`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `brd_groups`
--
ALTER TABLE `brd_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `brd_images`
--
ALTER TABLE `brd_images`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `brd_plotread`
--
ALTER TABLE `brd_plotread`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `brd_plots`
--
ALTER TABLE `brd_plots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brd_fgk_2` (`author_id`),
  ADD KEY `brd_fgk_3` (`board_id`);

--
-- Indeksy dla tabeli `brd_plugins`
--
ALTER TABLE `brd_plugins`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `brd_posts`
--
ALTER TABLE `brd_posts`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `brd_users`
--
ALTER TABLE `brd_users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT dla tabel zrzutów
--

--
-- AUTO_INCREMENT dla tabeli `brd_boards`
--
ALTER TABLE `brd_boards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `brd_categories`
--
ALTER TABLE `brd_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `brd_chatbox`
--
ALTER TABLE `brd_chatbox`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `brd_groups`
--
ALTER TABLE `brd_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT dla tabeli `brd_images`
--
ALTER TABLE `brd_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `brd_plotread`
--
ALTER TABLE `brd_plotread`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `brd_plots`
--
ALTER TABLE `brd_plots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `brd_plugins`
--
ALTER TABLE `brd_plugins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `brd_posts`
--
ALTER TABLE `brd_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `brd_users`
--
ALTER TABLE `brd_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `brd_boards`
--
ALTER TABLE `brd_boards`
  ADD CONSTRAINT `brd_fgk` FOREIGN KEY (`category_id`) REFERENCES `brd_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ograniczenia dla tabeli `brd_plots`
--
ALTER TABLE `brd_plots`
  ADD CONSTRAINT `brd_fgk_2` FOREIGN KEY (`author_id`) REFERENCES `brd_users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `brd_fgk_3` FOREIGN KEY (`board_id`) REFERENCES `brd_boards` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
