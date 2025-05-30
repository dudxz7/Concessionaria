-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 27/05/2025 às 11:14
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `phpmyadmin`
--
CREATE DATABASE IF NOT EXISTS `phpmyadmin` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `phpmyadmin`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int(10) UNSIGNED NOT NULL,
  `dbase` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `query` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) NOT NULL,
  `col_name` varchar(64) NOT NULL,
  `col_type` varchar(64) NOT NULL,
  `col_length` text DEFAULT NULL,
  `col_collation` varchar(64) NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) DEFAULT '',
  `col_default` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Central list of columns';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int(5) UNSIGNED NOT NULL,
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `column_name` varchar(64) NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `transformation` varchar(255) NOT NULL DEFAULT '',
  `transformation_options` varchar(255) NOT NULL DEFAULT '',
  `input_transformation` varchar(255) NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__designer_settings`
--

CREATE TABLE `pma__designer_settings` (
  `username` varchar(64) NOT NULL,
  `settings_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Settings related to Designer';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__export_templates`
--

CREATE TABLE `pma__export_templates` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `export_type` varchar(10) NOT NULL,
  `template_name` varchar(64) NOT NULL,
  `template_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved export templates';

--
-- Despejando dados para a tabela `pma__export_templates`
--

INSERT INTO `pma__export_templates` (`id`, `username`, `export_type`, `template_name`, `template_data`) VALUES
(1, 'root', 'database', 'Sistema_BMW', '{\"quick_or_custom\":\"quick\",\"what\":\"sql\",\"structure_or_data_forced\":\"0\",\"table_select[]\":\"clientes\",\"table_structure[]\":\"clientes\",\"table_data[]\":\"clientes\",\"aliases_new\":\"\",\"output_format\":\"sendit\",\"filename_template\":\"@DATABASE@\",\"remember_template\":\"on\",\"charset\":\"utf-8\",\"compression\":\"none\",\"maxsize\":\"\",\"codegen_structure_or_data\":\"data\",\"codegen_format\":\"0\",\"csv_separator\":\",\",\"csv_enclosed\":\"\\\"\",\"csv_escaped\":\"\\\"\",\"csv_terminated\":\"AUTO\",\"csv_null\":\"NULL\",\"csv_columns\":\"something\",\"csv_structure_or_data\":\"data\",\"excel_null\":\"NULL\",\"excel_columns\":\"something\",\"excel_edition\":\"win\",\"excel_structure_or_data\":\"data\",\"json_structure_or_data\":\"data\",\"json_unicode\":\"something\",\"latex_caption\":\"something\",\"latex_structure_or_data\":\"structure_and_data\",\"latex_structure_caption\":\"Estrutura da tabela @TABLE@\",\"latex_structure_continued_caption\":\"Estrutura da tabela @TABLE@ (continuação)\",\"latex_structure_label\":\"tab:@TABLE@-structure\",\"latex_relation\":\"something\",\"latex_comments\":\"something\",\"latex_mime\":\"something\",\"latex_columns\":\"something\",\"latex_data_caption\":\"Conteúdo da tabela @TABLE@\",\"latex_data_continued_caption\":\"Conteúdo da tabela @TABLE@ (continuação)\",\"latex_data_label\":\"tab:@TABLE@-data\",\"latex_null\":\"\\\\textit{NULL}\",\"mediawiki_structure_or_data\":\"structure_and_data\",\"mediawiki_caption\":\"something\",\"mediawiki_headers\":\"something\",\"htmlword_structure_or_data\":\"structure_and_data\",\"htmlword_null\":\"NULL\",\"ods_null\":\"NULL\",\"ods_structure_or_data\":\"data\",\"odt_structure_or_data\":\"structure_and_data\",\"odt_relation\":\"something\",\"odt_comments\":\"something\",\"odt_mime\":\"something\",\"odt_columns\":\"something\",\"odt_null\":\"NULL\",\"pdf_report_title\":\"\",\"pdf_structure_or_data\":\"structure_and_data\",\"phparray_structure_or_data\":\"data\",\"sql_include_comments\":\"something\",\"sql_header_comment\":\"\",\"sql_use_transaction\":\"something\",\"sql_compatibility\":\"NONE\",\"sql_structure_or_data\":\"structure_and_data\",\"sql_create_table\":\"something\",\"sql_auto_increment\":\"something\",\"sql_create_view\":\"something\",\"sql_procedure_function\":\"something\",\"sql_create_trigger\":\"something\",\"sql_backquotes\":\"something\",\"sql_type\":\"INSERT\",\"sql_insert_syntax\":\"both\",\"sql_max_query_size\":\"50000\",\"sql_hex_for_binary\":\"something\",\"sql_utc_time\":\"something\",\"texytext_structure_or_data\":\"structure_and_data\",\"texytext_null\":\"NULL\",\"xml_structure_or_data\":\"data\",\"xml_export_events\":\"something\",\"xml_export_functions\":\"something\",\"xml_export_procedures\":\"something\",\"xml_export_tables\":\"something\",\"xml_export_triggers\":\"something\",\"xml_export_views\":\"something\",\"xml_export_contents\":\"something\",\"yaml_structure_or_data\":\"data\",\"\":null,\"lock_tables\":null,\"as_separate_files\":null,\"csv_removeCRLF\":null,\"excel_removeCRLF\":null,\"json_pretty_print\":null,\"htmlword_columns\":null,\"ods_columns\":null,\"sql_dates\":null,\"sql_relation\":null,\"sql_mime\":null,\"sql_disable_fk\":null,\"sql_views_as_tables\":null,\"sql_metadata\":null,\"sql_create_database\":null,\"sql_drop_table\":null,\"sql_if_not_exists\":null,\"sql_simple_view_export\":null,\"sql_view_current_user\":null,\"sql_or_replace_view\":null,\"sql_truncate\":null,\"sql_delayed\":null,\"sql_ignore\":null,\"texytext_columns\":null}');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__favorite`
--

CREATE TABLE `pma__favorite` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Favorite tables';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__history`
--

CREATE TABLE `pma__history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db` varchar(64) NOT NULL DEFAULT '',
  `table` varchar(64) NOT NULL DEFAULT '',
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp(),
  `sqlquery` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='SQL history for phpMyAdmin';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__navigationhiding`
--

CREATE TABLE `pma__navigationhiding` (
  `username` varchar(64) NOT NULL,
  `item_name` varchar(64) NOT NULL,
  `item_type` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Hidden items of navigation tree';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__pdf_pages`
--

CREATE TABLE `pma__pdf_pages` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `page_nr` int(10) UNSIGNED NOT NULL,
  `page_descr` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='PDF relation pages for phpMyAdmin';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__recent`
--

CREATE TABLE `pma__recent` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Recently accessed tables';

--
-- Despejando dados para a tabela `pma__recent`
--

INSERT INTO `pma__recent` (`username`, `tables`) VALUES
('root', '[{\"db\":\"sistema_bmw\",\"table\":\"clientes\"}]');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__relation`
--

CREATE TABLE `pma__relation` (
  `master_db` varchar(64) NOT NULL DEFAULT '',
  `master_table` varchar(64) NOT NULL DEFAULT '',
  `master_field` varchar(64) NOT NULL DEFAULT '',
  `foreign_db` varchar(64) NOT NULL DEFAULT '',
  `foreign_table` varchar(64) NOT NULL DEFAULT '',
  `foreign_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Relation table';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__savedsearches`
--

CREATE TABLE `pma__savedsearches` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `search_name` varchar(64) NOT NULL DEFAULT '',
  `search_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved searches';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__table_coords`
--

CREATE TABLE `pma__table_coords` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `pdf_page_number` int(11) NOT NULL DEFAULT 0,
  `x` float UNSIGNED NOT NULL DEFAULT 0,
  `y` float UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__table_info`
--

CREATE TABLE `pma__table_info` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `display_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__table_uiprefs`
--

CREATE TABLE `pma__table_uiprefs` (
  `username` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `prefs` text NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tables'' UI preferences';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__tracking`
--

CREATE TABLE `pma__tracking` (
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `schema_snapshot` text NOT NULL,
  `schema_sql` text DEFAULT NULL,
  `data_sql` longtext DEFAULT NULL,
  `tracking` set('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') DEFAULT NULL,
  `tracking_active` int(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Database changes tracking for phpMyAdmin';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__userconfig`
--

CREATE TABLE `pma__userconfig` (
  `username` varchar(64) NOT NULL,
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `config_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User preferences storage for phpMyAdmin';

--
-- Despejando dados para a tabela `pma__userconfig`
--

INSERT INTO `pma__userconfig` (`username`, `timevalue`, `config_data`) VALUES
('root', '2025-03-19 23:46:39', '{\"Console\\/Mode\":\"collapse\",\"lang\":\"pt_BR\"}');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__usergroups`
--

CREATE TABLE `pma__usergroups` (
  `usergroup` varchar(64) NOT NULL,
  `tab` varchar(64) NOT NULL,
  `allowed` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User groups with configured menu items';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__users`
--

CREATE TABLE `pma__users` (
  `username` varchar(64) NOT NULL,
  `usergroup` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Users and their assignments to user groups';

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pma__central_columns`
--
ALTER TABLE `pma__central_columns`
  ADD PRIMARY KEY (`db_name`,`col_name`);

--
-- Índices de tabela `pma__column_info`
--
ALTER TABLE `pma__column_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`);

--
-- Índices de tabela `pma__designer_settings`
--
ALTER TABLE `pma__designer_settings`
  ADD PRIMARY KEY (`username`);

--
-- Índices de tabela `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_user_type_template` (`username`,`export_type`,`template_name`);

--
-- Índices de tabela `pma__favorite`
--
ALTER TABLE `pma__favorite`
  ADD PRIMARY KEY (`username`);

--
-- Índices de tabela `pma__history`
--
ALTER TABLE `pma__history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`,`db`,`table`,`timevalue`);

--
-- Índices de tabela `pma__navigationhiding`
--
ALTER TABLE `pma__navigationhiding`
  ADD PRIMARY KEY (`username`,`item_name`,`item_type`,`db_name`,`table_name`);

--
-- Índices de tabela `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  ADD PRIMARY KEY (`page_nr`),
  ADD KEY `db_name` (`db_name`);

--
-- Índices de tabela `pma__recent`
--
ALTER TABLE `pma__recent`
  ADD PRIMARY KEY (`username`);

--
-- Índices de tabela `pma__relation`
--
ALTER TABLE `pma__relation`
  ADD PRIMARY KEY (`master_db`,`master_table`,`master_field`),
  ADD KEY `foreign_field` (`foreign_db`,`foreign_table`);

--
-- Índices de tabela `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_savedsearches_username_dbname` (`username`,`db_name`,`search_name`);

--
-- Índices de tabela `pma__table_coords`
--
ALTER TABLE `pma__table_coords`
  ADD PRIMARY KEY (`db_name`,`table_name`,`pdf_page_number`);

--
-- Índices de tabela `pma__table_info`
--
ALTER TABLE `pma__table_info`
  ADD PRIMARY KEY (`db_name`,`table_name`);

--
-- Índices de tabela `pma__table_uiprefs`
--
ALTER TABLE `pma__table_uiprefs`
  ADD PRIMARY KEY (`username`,`db_name`,`table_name`);

--
-- Índices de tabela `pma__tracking`
--
ALTER TABLE `pma__tracking`
  ADD PRIMARY KEY (`db_name`,`table_name`,`version`);

--
-- Índices de tabela `pma__userconfig`
--
ALTER TABLE `pma__userconfig`
  ADD PRIMARY KEY (`username`);

--
-- Índices de tabela `pma__usergroups`
--
ALTER TABLE `pma__usergroups`
  ADD PRIMARY KEY (`usergroup`,`tab`,`allowed`);

--
-- Índices de tabela `pma__users`
--
ALTER TABLE `pma__users`
  ADD PRIMARY KEY (`username`,`usergroup`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `pma__history`
--
ALTER TABLE `pma__history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  MODIFY `page_nr` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Banco de dados: `sistema_bmw`
--
CREATE DATABASE IF NOT EXISTS `sistema_bmw` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `sistema_bmw`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome_completo` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cpf` varchar(15) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `rg` varchar(20) NOT NULL,
  `cidade` varchar(100) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `cnh` varchar(20) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `admin` tinyint(1) DEFAULT 0,
  `cargo` enum('Admin','Cliente','Gerente','Funcionario') NOT NULL DEFAULT 'Cliente',
  `endereco` varchar(100) NOT NULL DEFAULT '',
  `pis` varchar(11) DEFAULT NULL,
  `registrado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome_completo`, `email`, `cpf`, `telefone`, `rg`, `cidade`, `estado`, `cnh`, `senha`, `admin`, `cargo`, `endereco`, `pis`, `registrado_em`) VALUES
(1, 'a', 'a@gmail.com', '111.111.111-11', '(11) 11111-1122', '1111111111-1', 'XIQUE XIQUE ', 'SP', '11111111111', '$2y$10$qz4/R1dWGZ2XZiS8pJyUOunwuI6vWpJrhJFt3Lev36iOgEqiU9kg.', 0, 'Funcionario', 'Rua Fulano de Tal, numero 11', '00000000001', '2025-03-25 14:57:44'),
(3, 'dudu', 'teste@gmail.com', '111.111.111-12', '(11) 11111-1212', '1111111111-2', 'FORTALEZA', 'CE', '11111111112', '$2y$10$O8qhgRi8muvTFkbKBlvmfuA3UBaeSQTP/7ugvT2RBKQW47FnXzsJO', 0, 'Cliente', '', NULL, '2025-03-25 14:57:44'),
(5, 'ana', 'analinda@gmail.com', '111.111.111-13', '(11) 11111-1113', '1111111111-3', 'SAO PAULO', 'SP', '11111111113', '$2y$10$u0m/mLqzNLsVbYLFcNEJCOdcbIVEPIr8bkWlqP0jnyHQYkAC.hBS6', 0, 'Cliente', '', NULL, '2025-03-25 14:57:44'),
(8, 'Administrador', 'admin@gmail.com', '12345678910', '999999999', '111234567', 'Fortal', 'CE', '222123456', '$2y$10$Afu4U38UJS88pOf2KtkJ2.3Y9e0BTp11WYhPFADyKYl.2Cl4LF.Bu', 1, 'Admin', '', NULL, '2025-03-25 14:57:44'),
(10, 'Isabelly', 'mcqwwr@gmail.com', '111.111.111-14', '(11) 11111-1114', '1111111111-4', 'FORTALEZA', 'CE', '11111111114', '$2y$10$.XzfcmcxeXy64iSFw4psS.jTOt5u.3JRyiPPNs2hoJ3f85GxNeznG', 0, 'Cliente', '', NULL, '2025-03-25 14:57:44'),
(11, 'Tati Zaqui', 'tati@gmail.com', '111.111.111-15', '(11) 11111-1115', '1111111111-5', 'FORTALEZA', 'CE', '11111111115', '$2y$10$80Gs.agwOSvLU0vIQYM/LOs64/3SmkZI8bhgjohCtqdslZepA9zWm', 0, 'Cliente', '', NULL, '2025-03-25 14:57:44'),
(12, 'dudu ', 'd@gmail.com', '111.111.111-16', '(11) 11111-1116', '1111111111-6', 'PINDAMONHAGABAA', 'AC', '11111111116', '$2y$10$zldDKpzpBKpBXyTcG1cyEu2mk9TAd3DN7Tk4PxxQw2h0c2skTvCzO', 0, 'Cliente', '', NULL, '2025-03-25 14:57:44'),
(29, 'João Silva', 'cliente1@gmail.com', '99999999990', '9999999990', '9999990', 'São Paulo', 'SP', '9999999990', '$2y$10$65f/dY6.TmSHn7C70TLEUuOe376uEsPad09OusrRLGgpAw7/muTfe', 0, 'Cliente', '', NULL, '2025-03-25 16:19:09'),
(30, 'Maria Souza', 'cliente2@gmail.com', '99999999991', '9999999991', '9999991', 'Rio de Janeiro', 'RJ', '9999999991', '$2y$10$C.JV7nqb5uYiRe.yTl926.ZUh6nDicinHnMqFWH8gBqPBs.jUzOqO', 0, 'Cliente', '', NULL, '2025-03-25 16:19:09'),
(31, 'Carlos Oliveira', 'cliente3@gmail.com', '99999999992', '9999999992', '9999992', 'Belo Horizonte', 'MG', '9999999992', '$2y$10$l6EjlBQTdZN7BGWWX220JeaaTMctM0XFBHoeqqLpn02Se4byK0F26', 0, 'Cliente', '', NULL, '2025-03-25 16:19:09'),
(32, 'Ana Costa', 'cliente4@gmail.com', '99999999993', '9999999993', '9999993', 'Curitiba', 'PR', '9999999993', '$2y$10$Wu7YFYBb4fStqls/5bjdLOQHixx5t5gPgdTmkEPJ0oexTXk8kadIi', 0, 'Cliente', '', NULL, '2025-03-25 16:19:09'),
(33, 'Menina da gym', 'bixaboa@gmail.com', '111.111.111-17', '(11) 11111-1117', '1111111111-7', 'FORTALEZA', 'CE', '11111111117', '$2y$10$B.KZZg8j.u15bre/kEI8q.9AF/olwFSa3UwhPXkwBjdPzWFdtNEVK', 0, 'Cliente', '', NULL, '2025-03-25 22:08:59'),
(34, 'Paris Morgan', 'parismorgan@gmail.com', '111.111.111-18', '(11) 11111-1118', '1111111111-8', 'BROOKLYN', 'RJ', '1111111118', '$2y$10$nxMkgbKA74vI1T2dxBHCvemB3axWCG15srDtY8x2RIp6IjWiabjC.', 0, 'Gerente', 'Rua bem ali no escuro 101', '00000000002', '2025-04-07 01:58:39'),
(35, 'Megan Fox', 'meganfox@gmail.com', '111.111.111-19', '(11) 11111-1119', '1111111111-9', 'WASHINTON DC', 'RJ', '1111111119', '$2y$10$TrMH8y9grAvQn2O5iL7g7OLpLhl74IBjD3KJiv/HAWW10u0.YvEPi', 0, 'Gerente', 'Rua Fulano de Tal, numero 15', '00000000003', '2025-04-07 02:03:10'),
(36, 'Malelly', 'malellysg@gmail.com', '111.111.111-20', '(11) 11111-1120', '1111111112-0', 'BROOKLYN', 'RS', '11111111120', '$2y$10$uW32HaTajccA8AQdIi9Rl.aOYNNMMRb2BJwLBbc0/U5g7nJUbU9X2', 0, 'Gerente', 'Rua bem ali 1020', '00000000004', '2025-04-12 18:34:39');

-- --------------------------------------------------------

--
-- Estrutura para tabela `detalhes_modelos`
--

CREATE TABLE `detalhes_modelos` (
  `id` int(11) NOT NULL,
  `modelo_id` int(11) NOT NULL,
  `cor_principal` varchar(62) DEFAULT NULL,
  `descricao` varchar(62) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `detalhes_modelos`
--

INSERT INTO `detalhes_modelos` (`id`, `modelo_id`, `cor_principal`, `descricao`) VALUES
(1, 1, 'Azul', '1.5 12V GASOLINA SPORT GP STEPTRONIC'),
(2, 2, 'Preto', '1.5 TWINTURBO GASOLINA GRAN COUPE M SPORT STEPTRONIC'),
(6, 3, 'Branco', '2.0 16V TURBO FLEX M SPORT 10TH ANNIVERSARY EDITION AUTOMÁTICO'),
(7, 4, 'Azul', '2.0 16V TURBO HÍBRIDO M SPORT AUTOMÁTICO'),
(8, 6, 'Azul', '2.0 16V GASOLINA CABRIO M SPORT STEPTRONIC'),
(9, 5, 'Azul', '2.0 16V TURBO GASOLINA M SPORT AUTOMÁTICO');

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque`
--

CREATE TABLE `estoque` (
  `id` int(11) NOT NULL,
  `veiculo_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `estoque`
--

INSERT INTO `estoque` (`id`, `veiculo_id`, `quantidade`) VALUES
(1, 2, 1),
(2, 3, 1),
(3, 4, 1),
(4, 5, 1),
(5, 6, 1),
(6, 7, 1),
(7, 8, 1),
(8, 9, 1),
(9, 10, 1),
(10, 11, 1),
(11, 12, 1),
(12, 13, 1),
(13, 14, 1),
(14, 15, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `favoritos`
--

CREATE TABLE `favoritos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `modelo_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `favoritos`
--

INSERT INTO `favoritos` (`id`, `usuario_id`, `modelo_id`) VALUES
(5, 1, 1),
(6, 1, 3),
(44, 1, 5),
(43, 1, 6),
(52, 3, 3),
(60, 12, 1),
(67, 12, 2),
(80, 12, 3),
(75, 12, 4),
(94, 12, 5),
(42, 34, 1),
(51, 34, 2),
(45, 34, 5);

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcionarios`
--

CREATE TABLE `funcionarios` (
  `id` int(11) NOT NULL,
  `nome_completo` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `cpf` varchar(11) NOT NULL,
  `rg` varchar(15) NOT NULL,
  `telefone` varchar(15) NOT NULL,
  `pis` varchar(11) NOT NULL,
  `endereco` varchar(100) DEFAULT NULL,
  `cidade` varchar(50) NOT NULL,
  `estado` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `funcionarios`
--

INSERT INTO `funcionarios` (`id`, `nome_completo`, `email`, `cpf`, `rg`, `telefone`, `pis`, `endereco`, `cidade`, `estado`) VALUES
(1, 'a', 'a@gmail.com', '111.111.111', '1111111111-1', '(11) 11111-1111', '00000000001', 'Rua Fulano de Tal, numero 10', 'XIQUE XIQUE ', 'SP');

-- --------------------------------------------------------

--
-- Estrutura para tabela `imagens_secundarias`
--

CREATE TABLE `imagens_secundarias` (
  `id` int(11) NOT NULL,
  `modelo_id` int(11) NOT NULL,
  `imagem` varchar(255) NOT NULL,
  `cor` varchar(100) DEFAULT NULL,
  `ordem` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `imagens_secundarias`
--

INSERT INTO `imagens_secundarias` (`id`, `modelo_id`, `imagem`, `cor`, `ordem`) VALUES
(1, 1, '1.webp', 'Azul', 1),
(2, 1, '2.webp', 'Azul', 2),
(3, 1, '3.webp', 'Azul', 3),
(4, 1, '4.webp', 'Azul', 4),
(5, 1, '5.webp', 'Azul', 5),
(6, 1, '6.webp', 'Azul', 6),
(7, 1, '7.webp', 'Azul', 7),
(8, 1, '8.webp', 'Azul', 8),
(9, 1, '1.webp', 'Prata', 1),
(10, 1, '2.webp', 'Prata', 2),
(11, 1, '3.webp', 'Prata', 3),
(12, 1, '4.png', 'Prata', 4),
(13, 1, '5.webp', 'Prata', 5),
(14, 1, '6.webp', 'Prata', 6),
(15, 1, '7.webp', 'Prata', 7),
(16, 1, '8.webp', 'Prata', 8),
(17, 1, '9.webp', 'Prata', 9),
(18, 1, '9.webp', 'Azul', 9),
(22, 2, '1.webp', 'Preto', 1),
(23, 2, '2.webp', 'Preto', 2),
(24, 2, '3.webp', 'Preto', 3),
(25, 2, '4.webp', 'Preto', 4),
(26, 2, '5.webp', 'Preto', 5),
(27, 2, '6.webp', 'Preto', 6),
(28, 2, '7.webp', 'Preto', 7),
(29, 2, '8.webp', 'Preto', 8),
(30, 2, '9.webp', 'Preto', 9),
(31, 4, '1.webp', 'Azul', 1),
(32, 4, '2.webp', 'Azul', 2),
(33, 4, '3.webp', 'Azul', 3),
(34, 4, '4.webp', 'Azul', 4),
(35, 4, '5.webp', 'Azul', 5),
(36, 4, '6.webp', 'Azul', 6),
(37, 4, '7.webp', 'Azul', 7),
(38, 4, '8.webp', 'Azul', 8),
(39, 4, '9.webp', 'Azul', 9),
(40, 6, '1.webp', 'Azul', 1),
(41, 6, '2.webp', 'Azul', 2),
(42, 6, '3.webp', 'Azul', 3),
(43, 6, '4.webp', 'Azul', 4),
(44, 6, '5.webp', 'Azul', 5),
(45, 6, '6.webp', 'Azul', 6),
(46, 6, '7.webp', 'Azul', 7),
(47, 6, '8.webp', 'Azul', 8),
(48, 6, '9.webp', 'Azul', 9),
(49, 5, '1.webp', 'Azul', 1),
(50, 5, '2.webp', 'Azul', 2),
(51, 5, '3.webp', 'Azul', 3),
(52, 5, '4.webp', 'Azul', 4),
(53, 5, '5.webp', 'Azul', 5),
(54, 5, '6.webp', 'Azul', 6),
(55, 5, '7.webp', 'Azul', 7),
(56, 5, '8.webp', 'Azul', 8),
(57, 5, '9.webp', 'Azul', 9),
(58, 3, '1.webp', 'Branco', 1),
(59, 3, '2.webp', 'Branco', 2),
(60, 3, '3.webp', 'Branco', 3),
(61, 3, '4.webp', 'Branco', 4),
(62, 3, '5.webp', 'Branco', 5),
(63, 3, '6.webp', 'Branco', 6),
(64, 3, '7.webp', 'Branco', 7),
(65, 3, '8.webp', 'Branco', 8),
(66, 3, '9.webp', 'Branco', 9),
(67, 3, '2.webp', 'Azul', 2),
(68, 3, '3.webp', 'Azul', 3),
(69, 3, '1.webp', 'Azul', 1),
(70, 3, '6.webp', 'Azul', 6),
(71, 3, '4.webp', 'Azul', 4),
(72, 3, '5.webp', 'Azul', 5),
(73, 3, '7.webp', 'Azul', 7),
(74, 3, '8.webp', 'Azul', 8),
(75, 3, '9.webp', 'Azul', 9),
(103, 3, '1.webp', 'Prata', 1),
(104, 3, '2.webp', 'Prata', 2),
(105, 3, '3.webp', 'Prata', 3),
(106, 3, '4.webp', 'Prata', 4),
(107, 3, '5.webp', 'Prata', 5),
(108, 3, '6.webp', 'Prata', 6),
(109, 3, '7.webp', 'Prata', 7),
(110, 3, '8.webp', 'Prata', 8),
(111, 3, '9.webp', 'Prata', 9);

-- --------------------------------------------------------

--
-- Estrutura para tabela `modelos`
--

CREATE TABLE `modelos` (
  `id` int(11) NOT NULL,
  `modelo` varchar(255) NOT NULL,
  `fabricante` varchar(255) NOT NULL,
  `cor` varchar(50) NOT NULL,
  `ano` int(11) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `estoque` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `modelos`
--

INSERT INTO `modelos` (`id`, `modelo`, `fabricante`, `cor`, `ano`, `preco`, `estoque`) VALUES
(1, 'BMW 118i', 'BMW', 'Azul,Prata', 2024, 320950.00, 0),
(2, 'BMW 218i', 'BMW', 'Preto', 2024, 320950.00, 0),
(3, 'BMW 320i', 'BMW', 'Branco,Azul,Prata', 2025, 412950.00, 0),
(4, 'BMW 330e', 'BMW', 'Azul', 2025, 454950.00, 0),
(5, 'BMW 330I', 'BMW', 'Azul', 2021, 229990.00, 0),
(6, 'BMW 420I', 'BMW', 'Azul', 2025, 503950.00, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamentos_pix_historico`
--

CREATE TABLE `pagamentos_pix_historico` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `veiculo_id` int(11) NOT NULL,
  `cor` varchar(50) NOT NULL,
  `criado_em` int(11) NOT NULL,
  `expira_em` int(11) NOT NULL,
  `status` enum('pendente','expirado','concluido','cancelado') DEFAULT 'pendente',
  `valor` decimal(15,2) DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `forma_pagamento` enum('pix','boleto','cartao') NOT NULL DEFAULT 'pix'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pagamentos_pix_historico`
--

INSERT INTO `pagamentos_pix_historico` (`id`, `usuario_id`, `veiculo_id`, `cor`, `criado_em`, `expira_em`, `status`, `valor`, `observacao`, `forma_pagamento`) VALUES
(1, 34, 3, 'Branco', 1747973442, 1747974342, 'pendente', NULL, NULL, 'pix'),
(2, 34, 3, 'Azul', 1747973546, 1747974446, 'pendente', 412950.00, NULL, 'pix'),
(3, 34, 3, 'Prata', 1747973688, 1747974588, 'pendente', 412950.00, NULL, 'pix'),
(4, 34, 2, 'Preto', 1747973725, 1747974625, 'pendente', 320950.00, NULL, 'pix'),
(5, 34, 6, 'Azul', 1747973956, 1747974856, 'pendente', 503950.00, NULL, 'pix'),
(6, 1, 3, 'Branco', 1748045082, 1748045982, 'pendente', 412950.00, NULL, 'pix'),
(7, 1, 1, 'Azul', 1748078838, 1748079738, 'pendente', 320950.00, NULL, 'pix'),
(8, 1, 1, 'Prata', 1748078911, 1748079811, 'pendente', 320950.00, NULL, 'pix'),
(9, 1, 2, 'Preto', 1748079117, 1748080017, 'pendente', 320950.00, NULL, 'pix'),
(10, 1, 2, 'Preto', 1748079249, 1748080149, 'pendente', 320950.00, NULL, 'pix'),
(11, 1, 3, 'Branco', 1748079395, 1748080295, 'pendente', 412950.00, NULL, 'pix'),
(12, 1, 3, 'Azul', 1748079525, 1748080425, 'pendente', 412950.00, NULL, 'pix'),
(13, 1, 4, 'Azul', 1748079616, 1748080516, 'pendente', 454950.00, NULL, 'pix'),
(14, 1, 5, 'Azul', 1748079742, 1748080642, 'pendente', 229990.00, NULL, 'pix'),
(15, 1, 5, 'Azul', 1748080316, 1748081216, 'pendente', 229990.00, NULL, 'pix'),
(16, 1, 2, 'Preto', 1748080554, 1748081454, 'pendente', 320950.00, NULL, 'pix'),
(17, 1, 1, 'Azul', 1748110614, 1748111514, 'pendente', 320950.00, NULL, 'pix'),
(18, 1, 1, 'Azul', 1748110685, 1748111585, 'pendente', 320950.00, NULL, 'pix'),
(19, 1, 1, 'Azul', 1748110695, 1748111595, 'pendente', 320950.00, NULL, 'pix'),
(20, 12, 1, 'Azul', 1748110757, 1748111657, 'pendente', 320950.00, NULL, 'pix'),
(21, 12, 1, 'Prata', 1748110835, 1748111735, 'pendente', 320950.00, NULL, 'pix'),
(22, 12, 2, 'Preto', 1748118507, 1748119407, 'pendente', 320950.00, NULL, 'pix'),
(23, 12, 2, 'Preto', 1748121746, 1748122646, 'pendente', 320950.00, NULL, 'pix'),
(24, 12, 3, 'Azul', 1748165047, 1748165947, 'pendente', 412950.00, NULL, 'pix');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamentos_pix_pendentes`
--

CREATE TABLE `pagamentos_pix_pendentes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `veiculo_id` int(11) NOT NULL,
  `cor` varchar(100) NOT NULL,
  `expira_em` int(11) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `promocoes`
--

CREATE TABLE `promocoes` (
  `id` int(11) NOT NULL,
  `modelo_id` int(11) NOT NULL,
  `desconto` decimal(5,2) NOT NULL,
  `preco_com_desconto` decimal(10,2) NOT NULL,
  `data_limite` datetime NOT NULL DEFAULT current_timestamp(),
  `ativo` tinyint(1) DEFAULT 1,
  `status` enum('Ativa','Inativa') DEFAULT 'Ativa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `promocoes`
--

INSERT INTO `promocoes` (`id`, `modelo_id`, `desconto`, `preco_com_desconto`, `data_limite`, `ativo`, `status`) VALUES
(1, 4, 10.00, 409455.00, '2025-05-13 10:27:00', 1, 'Inativa'),
(2, 2, 10.00, 288855.00, '2025-05-23 22:01:00', 1, 'Inativa'),
(3, 3, 10.00, 371655.00, '2025-04-09 22:01:00', 1, 'Inativa'),
(4, 3, 10.00, 371655.00, '2025-05-28 14:56:00', 1, 'Ativa');

-- --------------------------------------------------------

--
-- Estrutura para tabela `veiculos`
--

CREATE TABLE `veiculos` (
  `id` int(11) NOT NULL,
  `modelo_id` int(11) NOT NULL,
  `numero_chassi` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `veiculos`
--

INSERT INTO `veiculos` (`id`, `modelo_id`, `numero_chassi`) VALUES
(1, 2, 'BMW00000000000001'),
(2, 2, 'BMW00000000000002'),
(3, 3, 'BMW00000000000003'),
(4, 2, 'BMW00000000000004'),
(5, 2, 'BMW00000000000005'),
(6, 3, 'BMW00000000000006'),
(7, 3, 'BMW00000000000007'),
(8, 3, 'BMW00000000000008'),
(9, 4, 'BMW00000000000009'),
(10, 4, 'BMW00000000000010'),
(11, 1, 'BMW00000000000011'),
(12, 4, 'BMW00000000000012'),
(13, 4, 'BMW00000000000013'),
(14, 1, 'BMW00000000000014'),
(15, 1, 'BMW00000000000015');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD UNIQUE KEY `rg` (`rg`),
  ADD UNIQUE KEY `telefone` (`telefone`),
  ADD UNIQUE KEY `cnh` (`cnh`);

--
-- Índices de tabela `detalhes_modelos`
--
ALTER TABLE `detalhes_modelos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `modelo_id` (`modelo_id`);

--
-- Índices de tabela `estoque`
--
ALTER TABLE `estoque`
  ADD PRIMARY KEY (`id`),
  ADD KEY `veiculo_id` (`veiculo_id`);

--
-- Índices de tabela `favoritos`
--
ALTER TABLE `favoritos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`,`modelo_id`),
  ADD KEY `modelo_id` (`modelo_id`);

--
-- Índices de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD UNIQUE KEY `pis` (`pis`);

--
-- Índices de tabela `imagens_secundarias`
--
ALTER TABLE `imagens_secundarias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `modelo_id` (`modelo_id`);

--
-- Índices de tabela `modelos`
--
ALTER TABLE `modelos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pagamentos_pix_historico`
--
ALTER TABLE `pagamentos_pix_historico`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pagamentos_pix_pendentes`
--
ALTER TABLE `pagamentos_pix_pendentes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`,`veiculo_id`,`cor`);

--
-- Índices de tabela `promocoes`
--
ALTER TABLE `promocoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `modelo_id` (`modelo_id`);

--
-- Índices de tabela `veiculos`
--
ALTER TABLE `veiculos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_chassi` (`numero_chassi`),
  ADD KEY `modelo_id` (`modelo_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de tabela `detalhes_modelos`
--
ALTER TABLE `detalhes_modelos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `estoque`
--
ALTER TABLE `estoque`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `favoritos`
--
ALTER TABLE `favoritos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `imagens_secundarias`
--
ALTER TABLE `imagens_secundarias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT de tabela `modelos`
--
ALTER TABLE `modelos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `pagamentos_pix_historico`
--
ALTER TABLE `pagamentos_pix_historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de tabela `pagamentos_pix_pendentes`
--
ALTER TABLE `pagamentos_pix_pendentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `promocoes`
--
ALTER TABLE `promocoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `veiculos`
--
ALTER TABLE `veiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `detalhes_modelos`
--
ALTER TABLE `detalhes_modelos`
  ADD CONSTRAINT `detalhes_modelos_ibfk_1` FOREIGN KEY (`modelo_id`) REFERENCES `modelos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `estoque`
--
ALTER TABLE `estoque`
  ADD CONSTRAINT `estoque_ibfk_1` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `favoritos`
--
ALTER TABLE `favoritos`
  ADD CONSTRAINT `favoritos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `favoritos_ibfk_2` FOREIGN KEY (`modelo_id`) REFERENCES `modelos` (`id`);

--
-- Restrições para tabelas `imagens_secundarias`
--
ALTER TABLE `imagens_secundarias`
  ADD CONSTRAINT `imagens_secundarias_ibfk_1` FOREIGN KEY (`modelo_id`) REFERENCES `modelos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `promocoes`
--
ALTER TABLE `promocoes`
  ADD CONSTRAINT `promocoes_ibfk_1` FOREIGN KEY (`modelo_id`) REFERENCES `modelos` (`id`);

--
-- Restrições para tabelas `veiculos`
--
ALTER TABLE `veiculos`
  ADD CONSTRAINT `veiculos_ibfk_1` FOREIGN KEY (`modelo_id`) REFERENCES `modelos` (`id`) ON DELETE CASCADE;
--
-- Banco de dados: `test`
--
CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test`;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
