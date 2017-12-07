-- phpMyAdmin SQL Dump
-- version 4.3.8
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Июл 05 2017 г., 04:26
-- Версия сервера: 5.6.32-78.1-log
-- Версия PHP: 5.6.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `findmypr_fmpvs2`
--

-- --------------------------------------------------------

--
-- Структура таблицы `wp_term_relationships`
--

CREATE TABLE IF NOT EXISTS `wp_term_relationships` (
  `object_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `term_taxonomy_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `term_order` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `wp_term_relationships`
--

INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES
(4332, 3, 0),
(3143, 3, 0),
(3676, 21, 0),
(2645, 5, 0),
(3131, 3, 0),
(34, 4, 0),
(36, 4, 0),
(4032, 16, 0),
(71, 5, 0),
(72, 5, 0),
(3999, 16, 0),
(259, 4, 0),
(262, 4, 0),
(263, 4, 0),
(2614, 3, 0),
(2613, 9, 0),
(2821, 10, 0),
(4334, 10, 0),
(3921, 3, 0),
(2608, 9, 0),
(2617, 3, 0),
(2618, 3, 0),
(2620, 3, 0),
(2622, 3, 0),
(3933, 16, 0),
(3931, 16, 0),
(4026, 16, 0),
(3996, 16, 0),
(4029, 16, 0),
(4022, 16, 0),
(2784, 10, 0),
(2806, 9, 0),
(4087, 23, 0),
(3625, 21, 0),
(3638, 21, 0),
(3634, 11, 0),
(3659, 16, 0),
(3666, 16, 0),
(3668, 21, 0),
(3674, 21, 0),
(3672, 21, 0),
(3729, 16, 0),
(3731, 16, 0),
(3690, 11, 0),
(3867, 11, 0),
(3780, 11, 0),
(3929, 16, 0),
(4090, 23, 0),
(3685, 20, 0),
(3110, 1, 0),
(3112, 1, 0),
(3114, 1, 0),
(3116, 1, 0),
(3118, 1, 0),
(3120, 1, 0),
(3122, 1, 0),
(3128, 3, 0),
(3129, 3, 0),
(3130, 3, 0),
(3139, 2, 0),
(3206, 10, 0),
(3783, 4, 0),
(4056, 23, 0),
(3678, 20, 0),
(3681, 20, 0),
(3683, 20, 0),
(4129, 23, 0),
(3687, 11, 0),
(3689, 11, 0),
(3695, 11, 0),
(3862, 11, 0),
(4276, 16, 0),
(4333, 9, 0),
(4262, 16, 0),
(4266, 16, 0),
(4271, 16, 0),
(4258, 16, 0),
(4474, 16, 0),
(4377, 16, 0),
(4383, 11, 0),
(4386, 11, 0),
(4390, 20, 0),
(4392, 21, 0),
(4399, 16, 0),
(4499, 16, 0),
(4405, 11, 0),
(4470, 16, 0),
(4396, 16, 0),
(4477, 16, 0),
(4480, 21, 0),
(4486, 16, 0),
(4489, 16, 0),
(4495, 16, 0),
(4497, 16, 0),
(4499, 21, 0),
(4667, 21, 0),
(4661, 16, 0),
(4664, 11, 0),
(4670, 21, 0),
(4673, 20, 0),
(4676, 16, 0),
(4686, 16, 0),
(4679, 16, 0),
(4681, 16, 0),
(4683, 16, 0),
(4689, 16, 0),
(4691, 16, 0),
(4693, 16, 0),
(4707, 21, 0),
(4695, 16, 0),
(4701, 20, 0),
(4704, 21, 0),
(4808, 21, 0),
(4824, 16, 0),
(4812, 21, 0),
(4816, 21, 0),
(4820, 21, 0),
(4827, 16, 0),
(4698, 11, 0),
(4861, 16, 0),
(4900, 16, 0),
(4892, 21, 0),
(5075, 16, 0),
(4896, 16, 0),
(4903, 16, 0),
(4906, 16, 0),
(4909, 16, 0),
(4888, 16, 0),
(5071, 21, 0),
(5111, 16, 0),
(5068, 16, 0),
(4854, 21, 0),
(5157, 21, 0),
(5114, 16, 0),
(5123, 16, 0),
(5129, 16, 0),
(5126, 16, 0),
(5117, 16, 0),
(5108, 16, 0),
(5120, 16, 0),
(5161, 16, 0),
(5194, 11, 0),
(5199, 20, 0),
(5201, 16, 0),
(5079, 16, 0),
(5199, 13, 0),
(3729, 13, 0),
(3674, 13, 0),
(3634, 13, 0),
(3625, 13, 0),
(5369, 21, 0),
(5289, 11, 0),
(5341, 21, 0),
(5472, 21, 0),
(5442, 20, 0),
(5479, 21, 0),
(5455, 13, 0),
(5472, 13, 0),
(5337, 11, 0),
(5455, 21, 0),
(5590, 5, 0),
(5632, 13, 0),
(5680, 5, 0),
(5742, 11, 0),
(5735, 13, 0),
(5742, 13, 0),
(5790, 13, 0),
(5790, 16, 0),
(5790, 21, 0),
(5790, 20, 0),
(5790, 11, 0),
(5821, 13, 0),
(5821, 21, 0),
(5847, 1, 0),
(5897, 13, 0),
(5897, 16, 0);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `wp_term_relationships`
--
ALTER TABLE `wp_term_relationships`
  ADD PRIMARY KEY (`object_id`,`term_taxonomy_id`), ADD KEY `term_taxonomy_id` (`term_taxonomy_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
