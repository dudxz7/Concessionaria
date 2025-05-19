-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 19/05/2025 às 10:54
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
  `registrado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome_completo`, `email`, `cpf`, `telefone`, `rg`, `cidade`, `estado`, `cnh`, `senha`, `admin`, `cargo`, `endereco`, `pis`, `registrado_em`) VALUES
(1, 'a', 'a@gmail.com', '111.111.111-11', '(11) 11111-1122', '1111111111-1', 'XIQUE XIQUE ', 'SP', '11111111111', '$2y$10$qz4/R1dWGZ2XZiS8pJyUOunwuI6vWpJrhJFt3Lev36iOgEqiU9kg.', 0, 'Funcionario', 'Rua Fulano de Tal, numero 11', '00000000001', '2025-03-25 14:57:44'),
(3, 'dudu', 'teste@gmail.com', '111.111.111-12', '(11) 11111-1212', '1111111111-2', 'FORTALEZA', 'CE', '11111111112', '$2y$10$wm95eVC6ziG0vUNZn2UEz.nSFqbmjXngSdtXT/sqC8EHNlGW4Xm8C', 0, 'Cliente', '', NULL, '2025-03-25 14:57:44'),
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
(4, 12, 5),
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
(75, 3, '9.webp', 'Azul', 9);

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
(1, 4, 10.00, 409455.00, '2025-05-13 10:27:00', 1, 'Ativa'),
(2, 2, 10.00, 288855.00, '2025-05-23 22:01:00', 1, 'Ativa'),
(3, 3, 10.00, 371655.00, '2025-04-09 22:01:00', 1, 'Inativa');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `imagens_secundarias`
--
ALTER TABLE `imagens_secundarias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT de tabela `modelos`
--
ALTER TABLE `modelos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `promocoes`
--
ALTER TABLE `promocoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
