-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 17/06/2025 às 11:00
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
-- Banco de dados: `sistema_bmw`
--

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
  `foto_perfil` varchar(255) DEFAULT NULL,
  `registrado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome_completo`, `email`, `cpf`, `telefone`, `rg`, `cidade`, `estado`, `cnh`, `senha`, `admin`, `cargo`, `endereco`, `pis`, `foto_perfil`, `registrado_em`) VALUES
(1, 'a', 'a@gmail.com', '111.111.111-11', '(11) 11111-1122', '1111111111-1', 'FORTALEZA', 'CE', '11111111111', '$2y$10$qz4/R1dWGZ2XZiS8pJyUOunwuI6vWpJrhJFt3Lev36iOgEqiU9kg.', 0, 'Funcionario', 'Rua bem ali', '00000000001', 'img/perfis/perfil_1_1748854813.jpg', '2025-03-25 14:57:44'),
(3, 'dudu', 'dudu@gmail.com', '111.111.111-12', '(11) 11111-1212', '1111111111-2', 'FORTALEZA', 'CE', '11111111112', '$2y$10$O8qhgRi8muvTFkbKBlvmfuA3UBaeSQTP/7ugvT2RBKQW47FnXzsJO', 0, 'Cliente', '', NULL, NULL, '2025-03-25 14:57:44'),
(5, 'ana', 'analinda@gmail.com', '111.111.111-13', '(11) 11111-1113', '1111111111-3', 'SAO PAULO', 'SP', '11111111113', '$2y$10$u0m/mLqzNLsVbYLFcNEJCOdcbIVEPIr8bkWlqP0jnyHQYkAC.hBS6', 0, 'Cliente', '', NULL, NULL, '2025-03-25 14:57:44'),
(8, 'Administrador', 'admin@gmail.com', '12345678910', '999999999', '111234567', 'Fortal', 'CE', '222123456', '$2y$10$Afu4U38UJS88pOf2KtkJ2.3Y9e0BTp11WYhPFADyKYl.2Cl4LF.Bu', 1, 'Admin', '', NULL, 'img/perfis/perfil_8_1750137288.jpg', '2025-03-25 14:57:44'),
(10, 'Isabelly', 'mcqwwr@gmail.com', '111.111.111-14', '(11) 11111-1114', '1111111111-4', 'FORTALEZA', 'CE', '11111111114', '$2y$10$.XzfcmcxeXy64iSFw4psS.jTOt5u.3JRyiPPNs2hoJ3f85GxNeznG', 0, 'Cliente', '', NULL, NULL, '2025-03-25 14:57:44'),
(11, 'Tati Zaqui', 'tati@gmail.com', '111.111.111-15', '(11) 11111-1115', '1111111111-5', 'FORTALEZA', 'CE', '11111111115', '$2y$10$80Gs.agwOSvLU0vIQYM/LOs64/3SmkZI8bhgjohCtqdslZepA9zWm', 0, 'Cliente', '', NULL, NULL, '2025-03-25 14:57:44'),
(12, 'dudu ', 'd@gmail.com', '111.111.111-16', '(11) 11111-1116', '1111111111-6', 'PINDAMONHAGABAA', 'AC', '11111111116', '$2y$10$zldDKpzpBKpBXyTcG1cyEu2mk9TAd3DN7Tk4PxxQw2h0c2skTvCzO', 0, 'Cliente', '', NULL, 'img/perfis/perfil_12_1749994794.jpg', '2025-03-25 14:57:44'),
(29, 'João Silva', 'cliente1@gmail.com', '99999999990', '9999999990', '9999990', 'São Paulo', 'SP', '9999999990', '$2y$10$65f/dY6.TmSHn7C70TLEUuOe376uEsPad09OusrRLGgpAw7/muTfe', 0, 'Cliente', '', NULL, NULL, '2025-03-25 16:19:09'),
(30, 'Maria Souza', 'cliente2@gmail.com', '99999999991', '9999999991', '9999991', 'Rio de Janeiro', 'RJ', '9999999991', '$2y$10$C.JV7nqb5uYiRe.yTl926.ZUh6nDicinHnMqFWH8gBqPBs.jUzOqO', 0, 'Cliente', '', NULL, NULL, '2025-03-25 16:19:09'),
(31, 'Carlos Oliveira', 'cliente3@gmail.com', '99999999992', '9999999992', '9999992', 'Belo Horizonte', 'MG', '9999999992', '$2y$10$l6EjlBQTdZN7BGWWX220JeaaTMctM0XFBHoeqqLpn02Se4byK0F26', 0, 'Cliente', '', NULL, NULL, '2025-03-25 16:19:09'),
(32, 'Ana Costa', 'cliente4@gmail.com', '99999999993', '9999999993', '9999993', 'Curitiba', 'PR', '9999999993', '$2y$10$Wu7YFYBb4fStqls/5bjdLOQHixx5t5gPgdTmkEPJ0oexTXk8kadIi', 0, 'Cliente', '', NULL, NULL, '2025-03-25 16:19:09'),
(33, 'Menina da gym', 'bixaboa@gmail.com', '111.111.111-17', '(11) 11111-1117', '1111111111-7', 'FORTALEZA', 'CE', '11111111117', '$2y$10$B.KZZg8j.u15bre/kEI8q.9AF/olwFSa3UwhPXkwBjdPzWFdtNEVK', 0, 'Cliente', '', NULL, NULL, '2025-03-25 22:08:59'),
(34, 'Paris Morgan', 'parismorgan@gmail.com', '111.111.111-18', '(11) 11111-1118', '1111111111-8', 'BROOKLYN', 'RJ', '1111111118', '$2y$10$9rv2OwoEWWusrIbFeqYkXOPJDnLSUtz/iOXS0vIW5BeOoRNCdbaym', 0, 'Gerente', 'Rua bem ali no escuro 101', '00000000002', 'img/perfis/perfil_34_1749028384.gif', '2025-04-07 01:58:39'),
(35, 'Megan Fox', 'meganfox@gmail.com', '111.111.111-19', '(11) 11111-1119', '1111111111-9', 'WASHINTON DC', 'RJ', '1111111119', '$2y$10$TrMH8y9grAvQn2O5iL7g7OLpLhl74IBjD3KJiv/HAWW10u0.YvEPi', 0, 'Gerente', 'Rua Fulano de Tal, numero 14', '00000000003', 'img/perfis/perfil_35_1748831822.jpeg', '2025-04-07 02:03:10'),
(36, 'Malelly', 'malellysg@gmail.com', '111.111.111-20', '(11) 11111-1120', '1111111112-0', 'BROOKLYN', 'RS', '11111111120', '$2y$10$uW32HaTajccA8AQdIi9Rl.aOYNNMMRb2BJwLBbc0/U5g7nJUbU9X2', 0, 'Gerente', 'Rua bem ali 1020', '00000000004', NULL, '2025-04-12 18:34:39'),
(37, 'Mordecaii', 'morde@gmail.com', '141.848.184-81', '(11) 81774-7177', '1747174714-7', 'BELFORT ROXO', 'RJ', '19427492746', '$2y$10$R.2J4uqs90C5HKg2iCVSUeaFcyqSys7.FKFZSTqGdSP5B5MKZSkxS', 0, 'Cliente', '', NULL, NULL, '2025-06-16 05:15:30'),
(39, 'Lara Silva', 'lara@gmail.com', '187.194.901-98', '(11) 11418-9199', '9187491761-1', 'ALGUM CANTO ', 'AC', '01113674844', '$2y$10$R4y0.CjxbrX19tQVI6/9/OgEyQdOhc0wdoyqrIIpfQvuPxCiw4sqC', 0, 'Cliente', '', NULL, NULL, '2025-06-16 05:20:27'),
(40, 'Rubens', 'rubin@gmail.com', '184.761.891-83', '(17) 35615-6166', '1747174814-4', 'FORTALEZA', 'CE', '41411111184', '$2y$10$nPvR3zI6W7fed3sBe6QqKOnNRsA4PkanrGI2e/FHBAwXKOU49bfta', 0, 'Cliente', '', NULL, NULL, '2025-06-16 05:22:02'),
(41, 'marina rui num sei oq', 'marimari@gmail.com', '141.776.663-63', '(78) 16613-6361', '1457777777-8', 'XIQUE XIQUE ', 'BA', '13663718178', '$2y$10$39Mize7w.mgzmP3WhlPKfuPPJViUKhlSSQ1trFL4CmEVVSWf8Edgy', 0, 'Cliente', '', NULL, NULL, '2025-06-16 05:24:15'),
(42, 'Juliana Paes', 'juju@gmail.com', '643.616.847-21', '(16) 83641-7368', '7836889917-3', 'PINDAMONHAGABAA', 'SP', '19131114441', '$2y$10$/bIp7yapyNgLYCuNC9UEau4BjAYoLlfMjuzApO2yuHXiFXl/gZvIe', 0, 'Cliente', '', NULL, NULL, '2025-06-16 05:26:42'),
(43, 'Bea', 'bea@gmail.com', '141.415.335-76', '(77) 51436-7388', '3531124352-1', 'BELFORT ROXO', 'RJ', '11411114225', '$2y$10$GxEEuNZ58KMmiERYEaaUCuXGpDU6QwwHlyxQY4pzw8iC7AQHLZhVi', 0, 'Cliente', '', NULL, NULL, '2025-06-16 05:29:15'),
(44, 'Bryan o coner', 'bryan@gmail.com', '177.455.551-12', '(77) 77113-5143', '1774616435-1', 'CAUCAIA', 'CE', '20141015069', '$2y$10$gq58dmDFrt9.2bbsQKHig.wOEcBwKqKWmwlTZi2ka05nlmNgMlGy6', 0, 'Funcionario', 'Rua bem ali na casa do lado', '17816388847', NULL, '2025-06-16 05:33:08'),
(45, 'João da Silva', 'joao.silva@email.com', '12345678901', '(11) 91234-5678', '1234567', 'São Paulo', 'SP', '12345678900', '$2y$10$hashfake1', 0, 'Cliente', '', NULL, NULL, '2025-06-16 05:40:54'),
(46, 'Maria Oliveira', 'maria.oliveira@email.com', '98765432100', '(21) 99876-5432', '7654321', 'Rio de Janeiro', 'RJ', '98765432100', '$2y$10$hashfake2', 0, 'Cliente', '', NULL, NULL, '2025-06-16 05:40:54'),
(47, 'Carlos Souza', 'carlos.souza@email.com', '11122233344', '(31) 98888-7777', '1122334', 'Belo Horizonte', 'MG', '11223344556', '$2y$10$hashfunc1', 0, 'Funcionario', 'Rua das Flores, 100', '12345678901', NULL, '2025-06-16 05:41:29'),
(48, 'Duda araújo', 'dudinhaaa@gmail.com', '747.567.439-18', '(31) 51113-4564', '1444444231-2', 'BROOKLYN', 'SP', '11133455665', '$2y$10$K67KgG2AG0AdLTJbSu0liOYm/G4orJ2KR6IA5BMRdp0tFr5QnmoXK', 0, 'Funcionario', 'Rua de cereja, 1100', '91749183811', NULL, '2025-06-16 05:44:01'),
(49, 'Ana Maria braga', 'anamaria@gmail.com', '176.461.837-61', '(77) 34517-3454', '9991654536-7', 'SAO PAULO', 'SP', '99341556628', '$2y$10$Bwkaf.my9d2N44.B72JujeGbs4.Ex8zkQ8XpsBamwd0Z7XBBcCfzu', 0, 'Cliente', '', '', NULL, '2025-06-16 05:52:17'),
(50, 'louro jose', 'louro@gmai.com', '773.554.355-11', '(55) 35547-5565', '7766655674-4', 'FORTALEZA', 'CE', '19427492555', '$2y$10$/jGhekwXnCfsXiRVMIsw4ujIZnXXaunUBOrw5CyKjdzSGN89GxtQ2', 0, 'Cliente', '', '', NULL, '2025-06-16 05:59:26'),
(51, 'Elinardy', 'elinardy@gmail.com', '998.877.665-54', '(88) 95353-5321', '1312421224-2', 'FORTALEZA', 'CE', '86423455543', '$2y$10$1CJ3uL6Eno/xNZrBbBuRb.bAbXuu0/5WYun1WDtrmTv4.WFgetxYu', 0, 'Cliente', '', '', NULL, '2025-06-16 22:17:19'),
(52, 'Margot Robbie', 'margot@gmail.com', '112.347.313-15', '(11) 15252-1145', '1699153778-3', 'WASHINTON DC', 'SP', '99999144124', '$2y$10$/2WG8X0fNQYjcVhs26Gskez1lALS5YQanluW30XF2tIakrjJNvC1W', 0, 'Funcionario', 'Rua muito top aqui', '88776611314', 'img/perfis/perfil_52_1750125789.jpg', '2025-06-16 22:57:14'),
(53, 'anakin', 'anakinisgone@gmail.com', '198.176.378-17', '(71) 77771-1132', '7766559198-2', 'FORTALEZA', 'CE', '24252525256', '$2y$10$k3L6.Dw2P8F9o9YzTdzGn.88qYA3hXUWv0G/4fMATDUOn3txxoTZa', 0, 'Funcionario', 'Rua onde nao existe lei 99', '99776666666', NULL, '2025-06-16 23:11:06'),
(54, 'Scarface', 'scar@gmail.com', '717.461.895-71', '(11) 16666-6666', '1618883788-3', 'FORTALEZA', 'CE', '11444286858', '$2y$10$yKInNED6pZy.xtc7LKlSGul.e7gyV24mcgTVnbcWyu2pkngLlXIO2', 0, 'Cliente', '', '', NULL, '2025-06-16 23:15:01'),
(55, 'Scarface', 'scar2@gmail.com', '063.581.649-17', '(99) 90016-3617', '8651918947-1', 'FORTALEZA', 'CE', '00417600174', '$2y$10$j.R3AGIDnHhz9yU/CnlXuuBN.gcMfOT9PYmc5E7Fvtp7L.R0T3Fva', 0, 'Funcionario', 'Rua Itália 1917', '81764891749', NULL, '2025-06-16 23:20:39'),
(56, 'Clarinha =)', 'mairaescura@gmail.com', '717.437.478-13', '(10) 00414-7184', '1100000517-3', 'BELEM', 'PA', '10041781014', '$2y$10$1hveWMBik7bNpZZBwZHmTOS0hV/D2ig1lLeU9CfwxhQDL.YS588Iy', 0, 'Cliente', '', '', NULL, '2025-06-16 23:50:34'),
(57, 'teste', 'ttt1@gmail.com', '001.437.818-38', '(10) 01473-9173', '1881839103-0', 'FORTALEZA', 'CE', '17361738139', '$2y$10$Zlvp4MwtQMtDtO.Ixo4p..e9v1Q.Gxv6VmXUxRkPU6hfHFadN/K8G', 0, 'Cliente', '', '', NULL, '2025-06-17 01:17:11'),
(58, 'teste', 'teste2@gmail.com', '104.719.317-68', '(74) 93719-7130', '8317893816-3', 'FORTALEZA', 'CE', '', '$2y$10$8aOM3kVdVaIaV2u2FRfXFeYTlgmXTNK1FlOc6af2CR6MJCXt7f42q', 0, 'Cliente', '', '', NULL, '2025-06-17 01:20:17'),
(59, 'teste ', 'teste3@gmail.com', '818.300.371-04', '(11) 73927-6801', '1831738193-9', 'FORTALEZA', 'CE', '18387818381', '$2y$10$x.oH0.9GQY9QPM1pC0RWkeLyjQHPk3UQRQbRxFpmZU2YFmTdG5OUW', 0, 'Funcionario', 'Rua bem ali no escuro 170', '91889001376', NULL, '2025-06-17 01:22:11'),
(60, 'teste', 'teste4@gmail.com', '173.718.909-99', '(11) 94714-8184', '4167481476-1', 'FORTALEZA', 'CE', '71737173717', '$2y$10$1GZZNjXgvNRaaaKBxDCGu.R5GKXiArhMyPwXZG1nEXe6jrCL7puBa', 0, 'Cliente', '', '', NULL, '2025-06-17 01:26:27'),
(61, 'teste', 'teste5@gmail.com', '111.117.371-38', '(11) 74318-3813', '1837163183-8', 'FORTALEZA', 'CE', '11173718381', '$2y$10$Ao1I8pladqjl.zkcKBJXye9U3h3KjBRMPTRngvly5ERib9LGLy9/i', 0, 'Cliente', '', '', NULL, '2025-06-17 01:27:40'),
(62, 'teste', 'teste6@gmail.com', '017.431.938-71', '(11) 74381-3819', '5128191991-9', 'FORTALEZA', 'CE', '00176300317', '$2y$10$geGnMfjIjptkj2RF0fjs9ekbrYa0ZV51TOqHoAreDMa.HgLwnBTMO', 0, 'Cliente', '', '', NULL, '2025-06-17 01:28:45'),
(63, 'teste', 'teste7@gmail.com', '177.138.183-91', '(85) 91736-1381', '1301948104-9', 'FORTALEZA', 'CE', '17361831938', '$2y$10$0qbygKoeXAuBSncoZZOI1.sCKxzTDBs51jBRkFaMpaPmpFkr28gwO', 0, 'Cliente', '', '', NULL, '2025-06-17 01:37:56');

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
(9, 5, 'Azul', '2.0 16V TURBO GASOLINA M SPORT AUTOMÁTICO'),
(10, 7, 'Verde', '2.0 16V GASOLINA CABRIO M SPORT STEPTRONIC'),
(11, 8, 'Prata', '2.0 TWINPOWER PHEV M SPORT AUTOMÁTICO'),
(12, 9, 'Preto', '2.0 16V TURBO GASOLINA M SPORT AUTOMÁTICO'),
(13, 10, 'Branco', '3.0 24V TURBO GASOLINA M SPORT AUTOMÁTICO'),
(14, 11, 'Prata', '3.0 TWINPOWER HÍBRIDO M SPORT AUTOMÁTICO'),
(15, 12, 'Preto-com-Laranja', 'ELÉTRICO EDRIVE UNIQUE FOREVER AUTOMÁTICO'),
(16, 13, 'Vermelho', 'ELÉTRICO EDRIVE40 M SPORT'),
(17, 14, 'Vermelho', 'ELÉTRICO XDRIVE60 M SPORT'),
(18, 15, 'Prata', 'ELÉTRICO XDRIVE60 M SPORT'),
(19, 16, 'Laranja', '1.5 12V HYBRID EDRIVE ROADSTER AUTOMÁTICO'),
(20, 17, 'Azul', 'ELÉTRICO XDRIVE50 SPORT'),
(21, 18, 'Azul', 'ELÉTRICO XDRIVE30 M SPORT'),
(22, 19, 'Vermelho', '64,8 KW ELÉTRICO XDRIVE30 M SPORT'),
(23, 20, 'Azul', 'ELÉTRICO M SPORT'),
(24, 21, 'Azul', '2.0 16V TURBO GASOLINA XDRIVE AUTOMÁTICO'),
(25, 22, 'Azul', '2.0 TWINTURBO GASOLINA XDRIVE GRAN COUPE AUTOMÁTICO'),
(26, 23, 'Azul', '3.0 TWINPOWER GASOLINA XDRIVE STEPTRONIC'),
(27, 24, 'Azul', '3.0 I6 TWINTURBO GASOLINA COUPÉ STEPTRONIC'),
(28, 25, 'Prata', '6.6 V12 TWINPOWER GASOLINA XDRIVE STEPTRONIC'),
(29, 26, 'Preto', '4.4 V8 TWINPOWER GASOLINA XDRIVE STEPTRONIC'),
(30, 27, 'Azul-bebe', '3.0 I6 TWINTURBO GASOLINA COUPÉ TRACK M STEPTRONIC'),
(31, 28, 'Azul', '3.0 I6 TWINTURBO GASOLINA COMPETITION TRACK M STEPTRONIC'),
(32, 29, 'Preto', '3.0 I6 TWINTURBO GASOLINA COUPÉ CS M STEPTRONIC'),
(33, 30, 'Vermelho', '4.4 V8 TWINPOWER GASOLINA COMPETITION M XDRIVE'),
(34, 31, 'Vermelho', '4.4 V8 TWINPOWER GASOLINA XDRIVE GRAN COUPÉ STEPTRONIC'),
(35, 32, 'Preto', '2.0 16V TURBO GASOLINA SDRIVE20I M SPORT STEPTRONIC'),
(36, 33, 'Azul', '2.0 TURBO GASOLINA XDRIVE M35I STEPTRONIC'),
(37, 34, 'Bege', '3.0 TWINPOWER MHEV M50 XDRIVE STEPTRONIC'),
(38, 35, 'Prata', '3.0 TWINPOWER GASOLINA M40I STEPTRONIC'),
(39, 36, 'Prata', '3.0 I6 TURBO HÍBRIDO XDRIVE50E M SPORT AUTOMÁTICO'),
(40, 37, 'Verde', '4.4 V8 BITURBO GASOLINA M COMPETITION AUTOMÁTICO'),
(41, 38, 'Prata', '4.4 V8 GASOLINA M60I STEPTRONIC');

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
(2, 3, 0),
(3, 4, 1),
(4, 5, 1),
(5, 6, 0),
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
(6, 1, 3),
(44, 1, 5),
(43, 1, 6),
(52, 3, 3),
(60, 12, 1),
(67, 12, 2),
(80, 12, 3),
(75, 12, 4),
(42, 34, 1),
(51, 34, 2),
(123, 34, 3),
(120, 34, 4),
(121, 34, 5),
(105, 35, 2),
(104, 35, 3);

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
(1, 'a', 'a@gmail.com', '111.111.111', '1111111111-1', '(11) 11111-1111', '00000000001', 'Rua Fulano de Tal, numero 10', 'XIQUE XIQUE ', 'SP'),
(2, 'Duda araújo', 'dudinhaaa@gmail.com', '747.567.439', '1444444231-2', '(31) 51113-4564', '91749183818', 'Rua de cereja, 1100', 'BROOKLYN', 'SP'),
(3, 'Margot Robbie', 'margot@gmail.com', '112.347.313', '1699153778-3', '(11) 15252-1145', '88776611314', 'Rua muito top aqui', 'WASHINTON DC', 'SP'),
(4, 'anakin', 'anakinisgone@gmail.com', '198.176.378', '7766559198-2', '(71) 77771-1132', '99776666666', 'Rua onde nao existe lei 99', 'FORTALEZA', 'CE'),
(5, 'Scarface', 'scar2@gmail.com', '063.581.649', '8651918947-1', '(99) 90016-3617', '81764891749', 'Rua Itália 1917', 'FORTALEZA', 'CE'),
(6, 'teste ', 'teste3@gmail.com', '818.300.371', '1831738193-9', '(11) 73927-6801', '91889001376', 'Rua bem ali no escuro 101', 'FORTALEZA', 'CE');

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
(111, 3, '9.webp', 'Prata', 9),
(112, 7, '1.webp', 'Verde', 1),
(113, 7, '2.webp', 'Verde', 2),
(114, 7, '3.webp', 'Verde', 3),
(115, 7, '4.webp', 'Verde', 4),
(116, 7, '5.webp', 'Verde', 5),
(117, 7, '6.webp', 'Verde', 6),
(118, 7, '7.webp', 'Verde', 7),
(119, 7, '8.webp', 'Verde', 8),
(120, 8, '1.webp', 'Prata', 1),
(121, 8, '2.webp', 'Prata', 2),
(122, 8, '3.webp', 'Prata', 3),
(123, 8, '4.webp', 'Prata', 4),
(124, 8, '5.webp', 'Prata', 5),
(125, 8, '6.webp', 'Prata', 6),
(126, 8, '7.webp', 'Prata', 7),
(127, 8, '8.webp', 'Prata', 8),
(128, 8, '9.webp', 'Prata', 9),
(129, 9, '1.webp', 'Preto', 1),
(130, 9, '2.webp', 'Preto', 2),
(131, 9, '3.webp', 'Preto', 3),
(132, 9, '4.webp', 'Preto', 4),
(133, 10, '1.webp', 'Branco', 1),
(134, 10, '2.webp', 'Branco', 2),
(135, 10, '3.webp', 'Branco', 3),
(136, 10, '4.webp', 'Branco', 4),
(137, 10, '5.webp', 'Branco', 5),
(138, 10, '6.webp', 'Branco', 6),
(139, 10, '7.webp', 'Branco', 7),
(140, 10, '8.webp', 'Branco', 8),
(141, 11, '1.webp', 'Prata', 1),
(142, 11, '2.webp', 'Prata', 2),
(143, 11, '3.webp', 'Prata', 3),
(144, 11, '4.webp', 'Prata', 4),
(145, 11, '5.webp', 'Prata', 5),
(146, 11, '6.webp', 'Prata', 6),
(147, 11, '7.webp', 'Prata', 7),
(148, 11, '8.webp', 'Prata', 8),
(149, 11, '9.webp', 'Prata', 9),
(150, 11, '1.webp', 'Preto', 1),
(151, 11, '2.webp', 'Preto', 2),
(152, 11, '3.webp', 'Preto', 3),
(162, 13, '1.webp', 'Azul', 1),
(163, 13, '2.webp', 'Azul', 2),
(164, 13, '3.webp', 'Azul', 3),
(165, 13, '4.webp', 'Azul', 4),
(166, 13, '5.webp', 'Azul', 5),
(167, 13, '6.webp', 'Azul', 6),
(168, 13, '7.webp', 'Azul', 7),
(169, 13, '8.webp', 'Azul', 8),
(170, 13, '9.webp', 'Azul', 9),
(171, 13, '1.webp', 'Vermelho', 1),
(172, 13, '2.webp', 'Vermelho', 2),
(173, 13, '3.webp', 'Vermelho', 3),
(174, 13, '4.webp', 'Vermelho', 4),
(175, 13, '5.webp', 'Vermelho', 5),
(176, 13, '6.webp', 'Vermelho', 6),
(177, 13, '7.webp', 'Vermelho', 7),
(178, 13, '8.webp', 'Vermelho', 8),
(179, 13, '9.webp', 'Vermelho', 9),
(180, 14, '1.webp', 'Vermelho', 1),
(181, 14, '2.webp', 'Vermelho', 2),
(182, 14, '3.webp', 'Vermelho', 3),
(183, 14, '4.webp', 'Vermelho', 4),
(184, 14, '5.webp', 'Vermelho', 5),
(185, 14, '6.webp', 'Vermelho', 6),
(186, 14, '7.webp', 'Vermelho', 7),
(187, 14, '8.webp', 'Vermelho', 8),
(188, 14, '9.webp', 'Vermelho', 9),
(189, 15, '1.webp', 'Prata', 1),
(190, 15, '2.webp', 'Prata', 2),
(191, 15, '3.webp', 'Prata', 3),
(192, 15, '4.webp', 'Prata', 4),
(193, 15, '5.webp', 'Prata', 5),
(194, 15, '6.webp', 'Prata', 6),
(195, 15, '7.webp', 'Prata', 7),
(196, 15, '8.webp', 'Prata', 8),
(197, 15, '9.webp', 'Prata', 9),
(198, 16, '1.webp', 'Laranja', 1),
(199, 16, '2.webp', 'Laranja', 2),
(200, 16, '3.webp', 'Laranja', 3),
(201, 16, '4.webp', 'Laranja', 4),
(202, 16, '5.webp', 'Laranja', 5),
(203, 16, '6.webp', 'Laranja', 6),
(204, 16, '7.webp', 'Laranja', 7),
(205, 16, '8.webp', 'Laranja', 8),
(206, 16, '9.webp', 'Laranja', 9),
(207, 16, '1.webp', 'Preto', 1),
(208, 16, '2.webp', 'Preto', 2),
(209, 16, '3.webp', 'Preto', 3),
(210, 16, '4.webp', 'Preto', 4),
(211, 16, '5.webp', 'Preto', 5),
(212, 16, '6.webp', 'Preto', 6),
(213, 16, '7.webp', 'Preto', 7),
(214, 17, '1.webp', 'Azul', 1),
(215, 17, '2.webp', 'Azul', 2),
(216, 17, '3.webp', 'Azul', 3),
(217, 17, '4.webp', 'Azul', 4),
(218, 17, '5.webp', 'Azul', 5),
(219, 17, '6.webp', 'Azul', 6),
(220, 17, '7.webp', 'Azul', 7),
(221, 17, '8.webp', 'Azul', 8),
(222, 17, '9.webp', 'Azul', 9),
(223, 17, '1.webp', 'Verde', 1),
(224, 17, '2.webp', 'Verde', 2),
(225, 17, '3.webp', 'Verde', 3),
(226, 17, '4.webp', 'Verde', 4),
(227, 17, '5.webp', 'Verde', 5),
(228, 17, '6.webp', 'Verde', 6),
(229, 17, '7.webp', 'Verde', 7),
(230, 17, '8.webp', 'Verde', 8),
(231, 17, '9.webp', 'Verde', 9),
(232, 18, '1.webp', 'Azul', 1),
(233, 18, '2.webp', 'Azul', 2),
(234, 18, '3.webp', 'Azul', 3),
(235, 18, '4.webp', 'Azul', 4),
(236, 18, '5.webp', 'Azul', 5),
(237, 18, '6.webp', 'Azul', 6),
(238, 18, '7.webp', 'Azul', 7),
(239, 18, '8.webp', 'Azul', 8),
(240, 18, '9.webp', 'Azul', 9),
(241, 18, '1.webp', 'Prata', 1),
(242, 18, '2.webp', 'Prata', 2),
(243, 18, '3.webp', 'Prata', 3),
(244, 18, '4.webp', 'Prata', 4),
(245, 18, '5.webp', 'Prata', 5),
(246, 18, '6.webp', 'Prata', 6),
(247, 18, '7.webp', 'Prata', 7),
(248, 18, '8.webp', 'Prata', 8),
(249, 18, '9.webp', 'Prata', 9),
(250, 19, '1.webp', 'Vermelho', 1),
(251, 19, '2.webp', 'Vermelho', 2),
(252, 19, '3.webp', 'Vermelho', 3),
(253, 19, '4.webp', 'Vermelho', 4),
(254, 19, '5.webp', 'Vermelho', 5),
(255, 19, '6.webp', 'Vermelho', 6),
(256, 19, '7.webp', 'Vermelho', 7),
(257, 19, '8.webp', 'Vermelho', 8),
(258, 19, '9.webp', 'Vermelho', 9),
(259, 20, '1.webp', 'Azul', 1),
(260, 20, '2.webp', 'Azul', 2),
(261, 20, '3.webp', 'Azul', 3),
(262, 20, '4.webp', 'Azul', 4),
(263, 20, '5.webp', 'Azul', 5),
(264, 20, '6.webp', 'Azul', 6),
(265, 20, '7.webp', 'Azul', 7),
(266, 20, '8.webp', 'Azul', 8),
(267, 20, '9.webp', 'Azul', 9),
(268, 21, '1.webp', 'Azul', 1),
(269, 21, '2.webp', 'Azul', 2),
(270, 21, '3.webp', 'Azul', 3),
(271, 21, '4.webp', 'Azul', 4),
(272, 21, '5.webp', 'Azul', 5),
(273, 21, '6.webp', 'Azul', 6),
(274, 21, '7.webp', 'Azul', 7),
(275, 21, '8.webp', 'Azul', 8),
(276, 21, '1.webp', 'Preto', 1),
(277, 21, '2.webp', 'Preto', 2),
(278, 21, '3.webp', 'Preto', 3),
(279, 22, '1.webp', 'Azul', 1),
(280, 22, '2.webp', 'Azul', 2),
(281, 22, '3.webp', 'Azul', 3),
(282, 22, '4.webp', 'Azul', 4),
(283, 22, '5.webp', 'Azul', 5),
(284, 22, '6.webp', 'Azul', 6),
(285, 22, '7.webp', 'Azul', 7),
(286, 22, '8.webp', 'Azul', 8),
(287, 22, '1.webp', 'Preto', 1),
(288, 22, '2.webp', 'Preto', 2),
(289, 22, '3.webp', 'Preto', 3),
(290, 23, '1.webp', 'Azul', 1),
(291, 23, '2.webp', 'Azul', 2),
(292, 23, '3.webp', 'Azul', 3),
(293, 23, '4.webp', 'Azul', 4),
(294, 23, '5.webp', 'Azul', 5),
(295, 23, '6.webp', 'Azul', 7),
(296, 23, '7.webp', 'Azul', 6),
(297, 23, '8.webp', 'Azul', 8),
(298, 23, '9.webp', 'Azul', 9),
(299, 24, '1.webp', 'Azul', 1),
(300, 24, '2.webp', 'Azul', 2),
(301, 24, '3.webp', 'Azul', 3),
(302, 24, '4.webp', 'Azul', 4),
(303, 24, '5.webp', 'Azul', 5),
(304, 24, '6.webp', 'Azul', 6),
(305, 24, '7.webp', 'Azul', 7),
(306, 24, '8.webp', 'Azul', 8),
(307, 25, '1.webp', 'Prata', 1),
(308, 25, '2.webp', 'Prata', 2),
(309, 25, '3.webp', 'Prata', 3),
(310, 25, '4.webp', 'Prata', 4),
(315, 26, '1.webp', 'Preto', 1),
(316, 26, '2.webp', 'Preto', 2),
(317, 26, '3.webp', 'Preto', 3),
(318, 26, '4.webp', 'Preto', 4),
(319, 27, '1.webp', 'Azul bebe', 1),
(320, 27, '2.webp', 'Azul bebe', 2),
(321, 27, '3.webp', 'Azul bebe', 3),
(322, 27, '4.webp', 'Azul bebe', 4),
(323, 27, '5.webp', 'Azul bebe', 5),
(324, 27, '6.webp', 'Azul bebe', 6),
(325, 27, '7.webp', 'Azul bebe', 7),
(326, 27, '8.webp', 'Azul bebe', 8),
(327, 27, '9.webp', 'Azul bebe', 9),
(328, 27, '1.webp', 'Prata', 1),
(329, 27, '2.webp', 'Prata', 2),
(330, 27, '3.webp', 'Prata', 3),
(331, 27, '4.webp', 'Prata', 4),
(332, 27, '5.webp', 'Prata', 5),
(333, 27, '6.webp', 'Prata', 6),
(334, 27, '7.webp', 'Prata', 7),
(335, 27, '8.webp', 'Prata', 8),
(336, 27, '9.webp', 'Prata', 9),
(337, 27, '1.webp', 'Azul-bebe', 1),
(338, 27, '2.webp', 'Azul-bebe', 2),
(339, 27, '3.webp', 'Azul-bebe', 3),
(340, 27, '4.webp', 'Azul-bebe', 4),
(341, 27, '5.webp', 'Azul-bebe', 5),
(342, 27, '6.webp', 'Azul-bebe', 6),
(343, 27, '7.webp', 'Azul-bebe', 7),
(344, 27, '8.webp', 'Azul-bebe', 8),
(345, 27, '9.webp', 'Azul-bebe', 9),
(346, 12, '1.webp', 'Preto-com-Laranja', 1),
(347, 12, '2.webp', 'Preto-com-Laranja', 2),
(348, 12, '3.webp', 'Preto-com-Laranja', 3),
(349, 12, '4.webp', 'Preto-com-Laranja', 4),
(350, 12, '5.webp', 'Preto-com-Laranja', 5),
(351, 12, '6.webp', 'Preto-com-Laranja', 6),
(352, 12, '7.webp', 'Preto-com-Laranja', 7),
(353, 12, '8.webp', 'Preto-com-Laranja', 8),
(354, 12, '9.webp', 'Preto-com-Laranja', 9),
(355, 12, '1.webp', 'Preto', 1),
(356, 12, '2.webp', 'Preto', 2),
(357, 12, '3.webp', 'Preto', 3),
(358, 12, '4.webp', 'Preto', 4),
(359, 28, '1.webp', 'Azul', 1),
(360, 28, '2.webp', 'Azul', 2),
(361, 28, '3.webp', 'Azul', 3),
(362, 28, '4.webp', 'Azul', 4),
(363, 28, '5.webp', 'Azul', 5),
(364, 28, '6.webp', 'Azul', 6),
(365, 28, '7.webp', 'Azul', 7),
(366, 28, '8.webp', 'Azul', 8),
(367, 28, '9.webp', 'Azul', 9),
(368, 28, '1.webp', 'Vermelho', 1),
(369, 28, '2.webp', 'Vermelho', 2),
(370, 28, '3.webp', 'Vermelho', 3),
(371, 28, '4.webp', 'Vermelho', 4),
(372, 28, '5.webp', 'Vermelho', 5),
(373, 28, '6.webp', 'Vermelho', 6),
(374, 28, '7.webp', 'Vermelho', 7),
(375, 28, '8.webp', 'Vermelho', 8),
(376, 28, '9.webp', 'Vermelho', 9),
(377, 29, '1.webp', 'Preto', 1),
(378, 29, '2.webp', 'Preto', 2),
(379, 29, '3.webp', 'Preto', 3),
(380, 29, '4.webp', 'Preto', 4),
(381, 29, '5.webp', 'Preto', 5),
(382, 29, '6.webp', 'Preto', 6),
(383, 29, '7.webp', 'Preto', 7),
(384, 29, '8.webp', 'Preto', 8),
(385, 29, '9.webp', 'Preto', 9),
(386, 30, '1.webp', 'Vermelho', 1),
(387, 30, '2.webp', 'Vermelho', 2),
(388, 30, '3.webp', 'Vermelho', 3),
(389, 30, '4.webp', 'Vermelho', 4),
(390, 30, '5.webp', 'Vermelho', 5),
(391, 30, '6.webp', 'Vermelho', 6),
(392, 30, '7.webp', 'Vermelho', 7),
(393, 30, '8.webp', 'Vermelho', 8),
(394, 30, '9.webp', 'Vermelho', 9),
(395, 31, '1.webp', 'Vermelho', 1),
(396, 31, '2.webp', 'Vermelho', 3),
(397, 31, '3.webp', 'Vermelho', 2),
(398, 31, '4.webp', 'Vermelho', 4),
(399, 31, '5.webp', 'Vermelho', 5),
(400, 31, '6.webp', 'Vermelho', 6),
(401, 31, '7.webp', 'Vermelho', 7),
(402, 31, '8.webp', 'Vermelho', 8),
(403, 31, '9.webp', 'Vermelho', 9),
(404, 32, '1.webp', 'Preto', 1),
(405, 32, '2.webp', 'Preto', 2),
(406, 32, '3.webp', 'Preto', 3),
(407, 32, '4.webp', 'Preto', 4),
(408, 32, '5.webp', 'Preto', 5),
(409, 32, '6.webp', 'Preto', 6),
(410, 32, '7.webp', 'Preto', 7),
(411, 32, '8.webp', 'Preto', 8),
(412, 32, '9.webp', 'Preto', 9),
(413, 32, '1.webp', 'Prata', 1),
(414, 32, '2.webp', 'Prata', 2),
(415, 32, '3.webp', 'Prata', 3),
(416, 32, '4.webp', 'Prata', 4),
(417, 32, '5.webp', 'Prata', 5),
(418, 32, '6.webp', 'Prata', 6),
(419, 32, '7.webp', 'Prata', 7),
(420, 32, '8.webp', 'Prata', 8),
(421, 32, '9.webp', 'Prata', 9),
(422, 32, '1.webp', 'Azul', 1),
(423, 32, '2.webp', 'Azul', 2),
(424, 32, '3.webp', 'Azul', 3),
(425, 32, '4.webp', 'Azul', 4),
(426, 32, '5.webp', 'Azul', 5),
(427, 32, '6.webp', 'Azul', 6),
(428, 32, '7.webp', 'Azul', 7),
(429, 32, '8.webp', 'Azul', 8),
(430, 32, '9.webp', 'Azul', 9),
(431, 33, '1.webp', 'Azul', 1),
(432, 33, '2.webp', 'Azul', 2),
(433, 33, '3.webp', 'Azul', 3),
(434, 33, '4.webp', 'Azul', 4),
(435, 33, '5.webp', 'Azul', 5),
(436, 33, '6.webp', 'Azul', 6),
(437, 33, '7.webp', 'Azul', 7),
(438, 33, '8.webp', 'Azul', 8),
(439, 34, '1.webp', 'Bege', 1),
(440, 34, '2.webp', 'Bege', 2),
(441, 34, '3.webp', 'Bege', 3),
(442, 34, '4.webp', 'Bege', 4),
(443, 34, '5.webp', 'Bege', 5),
(444, 34, '6.webp', 'Bege', 6),
(445, 34, '7.webp', 'Bege', 7),
(446, 34, '8.webp', 'Bege', 8),
(447, 34, '9.webp', 'Bege', 9),
(448, 35, '1.webp', 'Preto', 1),
(449, 35, '2.webp', 'Preto', 2),
(450, 35, '3.webp', 'Preto', 3),
(451, 35, '4.webp', 'Preto', 4),
(452, 35, '5.webp', 'Preto', 5),
(453, 35, '6.webp', 'Preto', 6),
(454, 35, '7.webp', 'Preto', 7),
(455, 35, '8.webp', 'Preto', 8),
(456, 35, '9.webp', 'Preto', 9),
(457, 35, '1.webp', 'Prata', 1),
(458, 35, '2.webp', 'Prata', 2),
(459, 35, '3.webp', 'Prata', 3),
(460, 35, '4.webp', 'Prata', 4),
(461, 35, '5.webp', 'Prata', 5),
(462, 35, '6.webp', 'Prata', 6),
(463, 35, '7.webp', 'Prata', 7),
(464, 35, '8.webp', 'Prata', 8),
(465, 35, '9.webp', 'Prata', 9),
(466, 35, '1.webp', 'Azul', 1),
(467, 35, '2.webp', 'Azul', 2),
(468, 35, '3.webp', 'Azul', 3),
(469, 35, '4.webp', 'Azul', 4),
(470, 35, '5.webp', 'Azul', 5),
(471, 35, '6.webp', 'Azul', 6),
(472, 35, '7.webp', 'Azul', 7),
(473, 35, '8.webp', 'Azul', 8),
(474, 35, '9.webp', 'Azul', 9),
(475, 36, '1.webp', 'Branco', 1),
(476, 36, '2.webp', 'Branco', 2),
(477, 36, '3.webp', 'Branco', 3),
(478, 36, '4.webp', 'Branco', 4),
(479, 36, '5.webp', 'Branco', 5),
(480, 36, '6.webp', 'Branco', 6),
(481, 36, '7.webp', 'Branco', 7),
(482, 36, '8.webp', 'Branco', 8),
(483, 36, '9.webp', 'Branco', 9),
(484, 36, '1.webp', 'Prata', 1),
(485, 36, '2.webp', 'Prata', 2),
(486, 36, '3.webp', 'Prata', 3),
(487, 36, '4.webp', 'Prata', 4),
(488, 36, '5.webp', 'Prata', 5),
(489, 36, '6.webp', 'Prata', 6),
(490, 36, '7.webp', 'Prata', 7),
(491, 36, '8.webp', 'Prata', 8),
(492, 36, '9.webp', 'Prata', 9),
(493, 37, '1.webp', 'Verde', 1),
(494, 37, '2.webp', 'Verde', 2),
(495, 37, '3.webp', 'Verde', 3),
(496, 37, '4.webp', 'Verde', 4),
(497, 37, '5.webp', 'Verde', 5),
(498, 37, '6.webp', 'Verde', 6),
(499, 37, '7.webp', 'Verde', 7),
(500, 37, '8.webp', 'Verde', 8),
(501, 37, '9.webp', 'Verde', 9),
(502, 37, '1.webp', 'Vermelho', 1),
(503, 37, '2.webp', 'Vermelho', 2),
(504, 37, '3.webp', 'Vermelho', 3),
(505, 37, '4.webp', 'Vermelho', 4),
(506, 37, '5.webp', 'Vermelho', 5),
(507, 37, '6.webp', 'Vermelho', 6),
(508, 37, '7.webp', 'Vermelho', 7),
(509, 37, '8.webp', 'Vermelho', 8),
(510, 37, '9.webp', 'Vermelho', 9),
(511, 38, '1.webp', 'Prata', 1),
(512, 38, '2.webp', 'Prata', 2),
(513, 38, '3.webp', 'Prata', 3),
(514, 38, '4.webp', 'Prata', 4),
(515, 38, '5.webp', 'Prata', 5),
(516, 38, '6.webp', 'Prata', 6),
(517, 38, '7.webp', 'Prata', 7),
(518, 38, '8.webp', 'Prata', 8),
(519, 38, '9.webp', 'Prata', 9);

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
(5, 'BMW 330i', 'BMW', 'Azul', 2021, 229990.00, 0),
(6, 'BMW 420I', 'BMW', 'Azul', 2025, 503950.00, 0),
(7, 'BMW 430i', 'BMW', 'Verde', 2022, 503950.00, 0),
(8, 'BMW 530E', 'BMW', 'Prata', 2025, 601950.00, 0),
(9, 'BMW 530i', 'BMW', 'Preto', 2020, 430950.00, 0),
(10, 'BMW 540i', 'BMW', 'Branco', 2020, 334458.00, 0),
(11, 'BMW 745LE', 'BMW', 'Preto,Prata', 2022, 578571.00, 0),
(12, 'BMW i3', 'BMW', 'Preto,Preto-com-Laranja', 2022, 169008.00, 0),
(13, 'BMW i4', 'BMW', 'Azul,Vermelho', 2025, 674950.00, 0),
(14, 'BMW i5', 'BMW', 'Vermelho', 2025, 794950.00, 0),
(15, 'BMW i7', 'BMW', 'Prata', 2025, 1372950.00, 0),
(16, 'BMW i8', 'BMW', 'Preto,Laranja', 2020, 649950.00, 0),
(17, 'BMW ix', 'BMW', 'Azul,Verde', 2025, 923500.00, 0),
(18, 'BMW ix1', 'BMW', 'Azul,Prata', 2025, 454950.00, 0),
(19, 'BMW ix2', 'BMW', 'Vermelho', 2025, 463950.00, 0),
(20, 'BMW ix3', 'BMW', 'Azul', 2025, 519950.00, 0),
(21, 'BMW M 135i', 'BMW', 'Preto,Azul', 2022, 271002.00, 0),
(22, 'BMW M 235i', 'BMW', 'Preto,Azul', 2022, 354463.00, 0),
(23, 'Bmw M 340i', 'BMW', 'Azul', 2022, 569950.00, 0),
(24, 'Bmw M 440i', 'BMW', 'Azul', 2022, 405977.00, 0),
(25, 'BMW M 760LI', 'BMW', 'Prata', 2022, 1204919.00, 0),
(26, 'BMW M 850i', 'BMW', 'Preto', 2022, 881950.00, 0),
(27, 'BMW M2', 'BMW', 'Prata,Azul-bebe', 2025, 726950.00, 0),
(28, 'Bmw M3', 'BMW', 'Azul,Vermelho', 2025, 998950.00, 0),
(29, 'BMW M4', 'BMW', 'Preto', 2025, 1399950.00, 0),
(30, 'BMW M5', 'BMW', 'Vermelho', 2022, 776750.00, 0),
(31, 'BMW M8', 'BMW', 'Vermelho', 2022, 886892.00, 0),
(32, 'Bmw X1', 'BMW', 'Preto,Prata,Azul', 2025, 382950.00, 0),
(33, 'BMW X2', 'BMW', 'Azul', 2025, 532950.00, 0),
(34, 'BMW X3', 'BMW', 'Bege', 2025, 624950.00, 0),
(35, 'BMW X4', 'BMW', 'Preto,Prata,Azul', 2025, 6499.00, 0),
(36, 'BMW X5', 'BMW', 'Branco,Prata', 2025, 837950.00, 0),
(37, 'BMW X6', 'BMW', 'Verde,Vermelho', 2025, 1298950.00, 0),
(38, 'BMW X7', 'BMW', 'Prata', 2025, 1282950.00, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamentos_pix`
--

CREATE TABLE `pagamentos_pix` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `veiculo_id` int(11) NOT NULL,
  `cor` varchar(50) NOT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `expira_em` datetime NOT NULL,
  `status` enum('pendente','expirado','cancelado','aprovado','recusado') DEFAULT 'pendente',
  `valor` decimal(15,2) DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `forma_pagamento` enum('pix','boleto','cartao') NOT NULL DEFAULT 'pix'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pagamentos_pix`
--

INSERT INTO `pagamentos_pix` (`id`, `usuario_id`, `veiculo_id`, `cor`, `criado_em`, `expira_em`, `status`, `valor`, `observacao`, `forma_pagamento`) VALUES
(15, 34, 1, 'Azul', '2025-06-16 04:08:54', '2025-06-16 04:23:54', 'aprovado', 320950.00, NULL, 'pix'),
(16, 34, 27, 'Azul-bebe', '2025-06-16 04:17:12', '2025-06-16 04:32:12', 'aprovado', 726950.00, NULL, 'pix'),
(17, 34, 6, 'Azul', '2025-06-16 04:29:13', '2025-06-16 04:44:13', 'aprovado', 503950.00, NULL, 'pix'),
(18, 12, 27, 'Azul-bebe', '2025-06-17 02:50:10', '2025-06-17 03:05:10', 'aprovado', 726950.00, NULL, 'pix'),
(19, 12, 16, 'Laranja', '2025-06-17 02:55:14', '2025-06-17 03:10:14', 'aprovado', 649950.00, NULL, 'pix');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamento_boleto`
--

CREATE TABLE `pagamento_boleto` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `veiculo_id` int(11) NOT NULL,
  `cor` varchar(50) NOT NULL,
  `codigo_barras` varchar(100) NOT NULL,
  `status` enum('pendente','expirado','cancelado','aprovado','recusado') DEFAULT 'pendente',
  `data_criacao` datetime NOT NULL DEFAULT current_timestamp(),
  `data_expiracao` datetime NOT NULL,
  `valor` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pagamento_boleto`
--

INSERT INTO `pagamento_boleto` (`id`, `usuario_id`, `veiculo_id`, `cor`, `codigo_barras`, `status`, `data_criacao`, `data_expiracao`, `valor`) VALUES
(1, 12, 2, 'Preto', '34191.79001 01043.510047 91020.150008 6 12340000010000', 'recusado', '2025-06-15 17:51:43', '2025-06-15 17:52:43', 320950.00),
(2, 12, 27, 'Prata', '34191.79001 01043.510047 91020.150008 6 12340000010000', 'expirado', '2025-06-15 17:59:19', '2025-06-15 18:00:19', 726950.00),
(3, 12, 3, 'Branco', '34191.79001 01043.510047 91020.150008 6 12340000010000', 'aprovado', '2025-06-15 18:03:49', '2025-06-15 18:04:49', 371655.00),
(4, 12, 2, 'Preto', '34191.79001 01043.510047 91020.150008 6 12340000010000', 'cancelado', '2025-06-15 18:39:53', '2025-06-15 18:40:53', 320950.00),
(5, 12, 2, 'Preto', '34191.79001 01043.510047 91020.150008 6 12340000010000', 'expirado', '2025-06-15 19:28:12', '2025-06-15 19:29:12', 320950.00),
(6, 12, 2, 'Preto', '34191.79001 01043.510047 91020.150008 6 12340000010000', 'expirado', '2025-06-15 19:34:56', '2025-06-15 19:35:56', 320950.00),
(7, 12, 1, 'Prata', '34191.79001 01043.510047 91020.150008 6 12340000010000', 'expirado', '2025-06-15 20:47:01', '2025-06-15 20:48:01', 320950.00);

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
(4, 3, 10.00, 371655.00, '2025-06-20 14:56:00', 1, 'Ativa');

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
-- Índices de tabela `pagamentos_pix`
--
ALTER TABLE `pagamentos_pix`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unico_pix_pendente` (`usuario_id`,`veiculo_id`,`cor`,`status`);

--
-- Índices de tabela `pagamento_boleto`
--
ALTER TABLE `pagamento_boleto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `veiculo_id` (`veiculo_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT de tabela `detalhes_modelos`
--
ALTER TABLE `detalhes_modelos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de tabela `estoque`
--
ALTER TABLE `estoque`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `favoritos`
--
ALTER TABLE `favoritos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `imagens_secundarias`
--
ALTER TABLE `imagens_secundarias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=520;

--
-- AUTO_INCREMENT de tabela `modelos`
--
ALTER TABLE `modelos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de tabela `pagamentos_pix`
--
ALTER TABLE `pagamentos_pix`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `pagamento_boleto`
--
ALTER TABLE `pagamento_boleto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
-- Restrições para tabelas `pagamento_boleto`
--
ALTER TABLE `pagamento_boleto`
  ADD CONSTRAINT `pagamento_boleto_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `pagamento_boleto_ibfk_2` FOREIGN KEY (`veiculo_id`) REFERENCES `modelos` (`id`);

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
