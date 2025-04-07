-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 07/04/2025 às 06:15
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
(1, 'a', 'a@gmail.com', '111.111.111-11', '(11) 11111-1122', '1111111111-1', 'XIQUE XIQUE ', 'SP', '11111111111', '$2y$10$io0pIXUD0YCmXCLRKY745O5EnMW6zS8LoSKllppvsAN/7DhGFPqZ6', 0, 'Funcionario', 'Rua Fulano de Tal, numero 11', '00000000001', '2025-03-25 14:57:44'),
(3, 'dudu', 'teste@gmail.com', '111.111.111-12', '(11) 11111-1112', '1111111111-2', 'FORTALEZA', 'CE', '11111111112', '$2y$10$wm95eVC6ziG0vUNZn2UEz.nSFqbmjXngSdtXT/sqC8EHNlGW4Xm8C', 0, 'Cliente', '', NULL, '2025-03-25 14:57:44'),
(5, 'ana', 'analinda@gmail.com', '111.111.111-13', '(11) 11111-1113', '1111111111-3', 'SAO PAULO', 'SP', '11111111113', '$2y$10$u0m/mLqzNLsVbYLFcNEJCOdcbIVEPIr8bkWlqP0jnyHQYkAC.hBS6', 0, 'Cliente', '', NULL, '2025-03-25 14:57:44'),
(8, 'Administrador', 'admin@gmail.com', '12345678910', '999999999', '111234567', 'Fortal', 'CE', '222123456', '$2y$10$fjmwvZiAFB3Fl95KWYKPkufmIlXQDJjMpRXuKtCTED9Txp0R3lWuy', 1, 'Admin', '', NULL, '2025-03-25 14:57:44'),
(10, 'Isabelly', 'mcqwwr@gmail.com', '111.111.111-14', '(11) 11111-1114', '1111111111-4', 'FORTALEZA', 'CE', '11111111114', '$2y$10$.XzfcmcxeXy64iSFw4psS.jTOt5u.3JRyiPPNs2hoJ3f85GxNeznG', 0, 'Cliente', '', NULL, '2025-03-25 14:57:44'),
(11, 'Tati Zaqui', 'tati@gmail.com', '111.111.111-15', '(11) 11111-1115', '1111111111-5', 'FORTALEZA', 'CE', '11111111115', '$2y$10$80Gs.agwOSvLU0vIQYM/LOs64/3SmkZI8bhgjohCtqdslZepA9zWm', 0, 'Cliente', '', NULL, '2025-03-25 14:57:44'),
(12, 'dudu ', 'd@gmail.com', '111.111.111-16', '(11) 11111-1116', '1111111111-6', 'PINDAMONHAGABAA', 'AC', '11111111116', '$2y$10$zldDKpzpBKpBXyTcG1cyEu2mk9TAd3DN7Tk4PxxQw2h0c2skTvCzO', 0, 'Cliente', '', NULL, '2025-03-25 14:57:44'),
(29, 'João Silva', 'cliente1@gmail.com', '99999999990', '9999999990', '9999990', 'São Paulo', 'SP', '9999999990', '$2y$10$65f/dY6.TmSHn7C70TLEUuOe376uEsPad09OusrRLGgpAw7/muTfe', 0, 'Cliente', '', NULL, '2025-03-25 16:19:09'),
(30, 'Maria Souza', 'cliente2@gmail.com', '99999999991', '9999999991', '9999991', 'Rio de Janeiro', 'RJ', '9999999991', '$2y$10$C.JV7nqb5uYiRe.yTl926.ZUh6nDicinHnMqFWH8gBqPBs.jUzOqO', 0, 'Cliente', '', NULL, '2025-03-25 16:19:09'),
(31, 'Carlos Oliveira', 'cliente3@gmail.com', '99999999992', '9999999992', '9999992', 'Belo Horizonte', 'MG', '9999999992', '$2y$10$l6EjlBQTdZN7BGWWX220JeaaTMctM0XFBHoeqqLpn02Se4byK0F26', 0, 'Cliente', '', NULL, '2025-03-25 16:19:09'),
(32, 'Ana Costa', 'cliente4@gmail.com', '99999999993', '9999999993', '9999993', 'Curitiba', 'PR', '9999999993', '$2y$10$Wu7YFYBb4fStqls/5bjdLOQHixx5t5gPgdTmkEPJ0oexTXk8kadIi', 0, 'Cliente', '', NULL, '2025-03-25 16:19:09'),
(33, 'Menina da gym', 'bixaboa@gmail.com', '111.111.111-17', '(11) 11111-1117', '1111111111-7', 'FORTALEZA', 'CE', '11111111117', '$2y$10$B.KZZg8j.u15bre/kEI8q.9AF/olwFSa3UwhPXkwBjdPzWFdtNEVK', 0, 'Cliente', '', NULL, '2025-03-25 22:08:59');

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
(12, 13, 1);

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
-- Estrutura para tabela `modelos`
--

CREATE TABLE `modelos` (
  `id` int(11) NOT NULL,
  `modelo` varchar(255) NOT NULL,
  `fabricante` varchar(255) NOT NULL,
  `cor` varchar(50) NOT NULL,
  `ano` int(11) NOT NULL,
  `preco` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `modelos`
--

INSERT INTO `modelos` (`id`, `modelo`, `fabricante`, `cor`, `ano`, `preco`) VALUES
(1, 'BMW 118i', 'BMW', 'Azul,Prata', 2024, 320950.00),
(2, 'BMW 218i', 'BMW', 'Preto', 2024, 320950.00),
(3, 'BMW 320i', 'BMW', 'Branco,Azul,Prata', 2025, 412950.00),
(4, 'BMW 330e', 'BMW', 'Azul', 2025, 454950.00);

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
(13, 4, 'BMW00000000000013');

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
-- Índices de tabela `estoque`
--
ALTER TABLE `estoque`
  ADD PRIMARY KEY (`id`),
  ADD KEY `veiculo_id` (`veiculo_id`);

--
-- Índices de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD UNIQUE KEY `pis` (`pis`);

--
-- Índices de tabela `modelos`
--
ALTER TABLE `modelos`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de tabela `estoque`
--
ALTER TABLE `estoque`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `modelos`
--
ALTER TABLE `modelos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `veiculos`
--
ALTER TABLE `veiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `estoque`
--
ALTER TABLE `estoque`
  ADD CONSTRAINT `estoque_ibfk_1` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `veiculos`
--
ALTER TABLE `veiculos`
  ADD CONSTRAINT `veiculos_ibfk_1` FOREIGN KEY (`modelo_id`) REFERENCES `modelos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
