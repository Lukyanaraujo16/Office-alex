-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 25/04/2024 às 18:00
-- Versão do servidor: 10.3.39-MariaDB-0ubuntu0.20.04.2
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `pain_office`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `chatbot`
--

CREATE TABLE `chatbot` (
  `id` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `rule_type` varchar(255) DEFAULT NULL,
  `rule_action` varchar(255) DEFAULT NULL,
  `response` text DEFAULT NULL,
  `reseller` int(11) DEFAULT NULL,
  `runs` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `chatbot`
--

INSERT INTO `chatbot` (`id`, `status`, `rule_type`, `rule_action`, `response`, `reseller`, `runs`, `created_at`, `updated_at`) VALUES
(1, 1, 'contains', 'text', 'Para renovar sua assinatura automaticamente, acesse o painel do cliente através do link abaixo, faça o login com o usuário e senha da sua assinatura, logo em seguida clique nos três pontinhos do lado superior esquerdo da tela, em seguida na opção, renovar plano, depois selecione a quantidade de meses, clique em prosseguir, MercadoPago, finalize seu pagamento.\n\nLink do painel do cliente: https://playonegestor.com/client_area/', 4, 0, '2024-03-09 23:53:10', '2024-03-09 23:53:10'),
(2, 1, 'contains', 'test_iptv', '*Bem vindo a #server_name#*\n\n✅Usuário: #username#\n✅Senha: #password#\n⚠️Seu teste é valido até: #duration#\n\n✅ Lojinha WEB Baixar  apps !\nhttps://apps.upcinetv.com\n\n✅ APLICATIVO NA PLAY STORE\nhttps://bit.ly/app-vuiptv\n\n✅ WebPlayer para PC navegador:\nhttp://web.upcinetv.com\nOu\nhttp://web.upcinetv.me\n\n✅  URL DNS para aplicativo universal : \nhttp://cdn.upcinetv.com\nOu\nhttp://cdn.upcinetv.me\n\n✅  *DNS  STB V3*\n\n*Opção 1*\n185.238.3.177\n\n⚠️Link M3U ENCURTADO:\n #m3u_link#\n\n⚠️Link M3U HLS GRANDE:\n#m3u_link_hls#\n\n⚠️Link M3U MPEGTS GRANDE:\n#m3u_link_mpegts#\n\n⚠️LINK APENAS PARA SSIPTV\n#ssiptv_link#\n\nAtt: #server_name#', 4, 0, '2024-03-09 23:54:02', '2024-03-09 23:54:02'),
(8, 1, 'contains', 'test_iptv', '*Bem-vindo a #server_name#*\n\n✅ Usuário:  #username#\n✅ Senha:  #password#\n⚠️ Sua assinatura vence dia:\n⚠️ #exp_date#\n\n✅ Lojinha WEB Baixar aplicativos!\n\nhttps://web.playonelojinha.com/\n\n✅  APLICATIVO NA PLAY STORE\naplicativo na PLAY STORE VIA NTDOWN\nconsulte o PIN no SITE da lojinha para baixar o seu APK preferido.\n\n✅  Web Player para PC navegador:\n Em construção!\n✅  URL DNS para aplicativo universal: \n Em construção!\n\n⚠️ Link M3U ENCURTADO:\n #m3u_link#\n\n⚠️ Link M3U HLS GRANDE:\n#m3u_link_hls#\n\n⚠️ Link M3U MPEGTS GRANDE:\n#m3u_link_mpegts#\n\n⚠️ LINK APENAS PARA SSIPTV\n#ssiptv_link#\n\nAtt.: #server_name#', 17, 25, '2024-03-19 16:26:39', '2024-04-05 18:19:51'),
(9, 1, 'contains', 'test_code', 'Bem-vindo a #server_name#\n\nCÓDIGO: #username#\n\nENTRE NA LOJINHA PLAYONE TV E BAIXE O APLICATIVO P2P COM DNS\nVIA CÓDIGO \n\n✅ APLICATIVO NO SITE\n\nhttps://web.playonelojinha.com/\n\n✅ APLICATIVO NA PLAY STORE\n\naplicativo na PLAY STORE VIA NTDOWN\npara fazer o DOWNLOAD digite o PIN DESEJADO\n\nSeu usuário é valido até: #exp_date#\n\nAtt.: #server_name#', 17, 6, '2024-03-19 16:27:51', '2024-04-05 18:18:56');

-- --------------------------------------------------------

--
-- Estrutura para tabela `chatbot_messages`
--

CREATE TABLE `chatbot_messages` (
  `id` int(11) NOT NULL,
  `chatbot_id` int(11) DEFAULT NULL,
  `reseller` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `chatbot_messages`
--

INSERT INTO `chatbot_messages` (`id`, `chatbot_id`, `reseller`, `message`, `created_at`, `updated_at`) VALUES
(1, 1, 4, 'Renovar', '2024-03-09 23:53:10', '2024-03-09 23:53:10'),
(2, 1, 4, 'renovar', '2024-03-09 23:53:10', '2024-03-09 23:53:10'),
(3, 2, 4, 'teste', '2024-03-09 23:54:02', '2024-03-09 23:54:02'),
(4, 2, 4, 'Teste', '2024-03-09 23:54:02', '2024-03-09 23:54:02'),
(19, 8, 17, 'Teste', '2024-03-19 16:26:39', '2024-03-19 16:26:39'),
(20, 8, 17, 'teste', '2024-03-19 16:26:39', '2024-03-19 16:26:39'),
(28, 9, 17, 'p2p', '2024-04-04 20:41:38', '2024-04-04 20:41:38'),
(29, 9, 17, 'P2p', '2024-04-04 20:41:38', '2024-04-04 20:41:38');

-- --------------------------------------------------------

--
-- Estrutura para tabela `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `alert` int(11) DEFAULT NULL,
  `groups` varchar(255) DEFAULT NULL,
  `active` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `office_properties`
--

CREATE TABLE `office_properties` (
  `id` int(11) NOT NULL,
  `property` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `office_properties`
--

INSERT INTO `office_properties` (`id`, `property`, `value`, `created_at`, `updated_at`) VALUES
(1, 'admin_data', '{\"username\":\"admin17\",\"password\":\"admin18\"}', '2023-07-05 18:04:28', '2023-09-01 18:22:44'),
(2, 'allowed_bouquets', '[\"1\",\"2\",\"5\",\"7\",\"27\",\"28\",\"30\",\"31\",\"33\",\"34\",\"35\"]', '2023-07-05 18:04:28', '2023-08-03 17:58:36'),
(3, 'allowed_groups', '[\"1\",\"2\"]', '2023-07-05 18:04:28', '2024-03-09 23:38:24'),
(4, 'automatic_test', '1', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(5, 'automatic_test_min_credits', '1', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(6, 'automatic_test_packages', '[\"1\"]', '2023-07-05 18:04:28', '2024-03-19 18:52:50'),
(7, 'binstream_allowed_packages', '[\"64cf9b736a4d77041dea9b94\",\"64d3d462dc9374042d041726\"]', '2023-07-05 18:04:28', '2023-08-17 13:39:26'),
(8, 'binstream_automatic_test_packages', 'null', '2023-07-05 18:04:28', '2024-03-09 23:45:17'),
(9, 'binstream_fast_test_package', '', '2023-07-05 18:04:28', '2024-03-09 23:44:42'),
(10, 'binstream_status', '1', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(11, 'binstream_test_time', '8', '2023-07-05 18:04:28', '2023-08-21 02:48:02'),
(12, 'binstream_trust_renew_status', '0', '2023-07-05 18:04:28', '2024-03-09 23:39:10'),
(13, 'binstream_trust_renew_time', '2', '2023-07-05 18:04:28', '2023-07-09 20:09:18'),
(14, 'binstream_user_char', '1', '2023-07-05 18:04:28', '2023-07-09 21:19:09'),
(15, 'binstream_user_length', '8', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(16, 'code_automatic_test_packages', '[\"1\"]', '2023-07-05 18:04:28', '2024-03-19 18:52:50'),
(17, 'code_default_pass', 'playonetv', '2023-07-05 18:04:28', '2024-03-17 18:40:35'),
(18, 'code_fast_test_package', '1', '2023-07-05 18:04:28', '2024-03-09 23:44:42'),
(19, 'code_max_connections', '1', '2023-07-05 18:04:28', '2024-03-17 18:40:35'),
(20, 'code_max_connections_status', '1', '2023-07-05 18:04:28', '2023-07-09 20:09:18'),
(21, 'code_status', '1', '2023-07-05 18:04:28', '2024-03-17 18:40:35'),
(22, 'code_user_char', '1', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(23, 'code_user_length', '6', '2023-07-05 18:04:28', '2023-07-09 20:09:18'),
(24, 'custom_dns', '', '2023-07-05 18:04:28', '2024-03-17 02:06:05'),
(25, 'dark_mode', '1', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(26, 'dash_fast_test', '1', '2023-07-05 18:04:28', '2023-07-06 12:27:17'),
(27, 'default_expiring_template', 'OLÁ!\r\n*ATENÇÃO!* O ACESSO A SUA CONTA:  >  #username#  < *EXPIRA EM BREVE!*\r\n\r\n*O SEU VENCIMENTO É DIA!*\r\n#exp_info#\r\n\r\n*Evite o bloqueio automático do seu sinal.*\r\n\r\n*Por favor, nos envie o comprovante de pagamento assim que possível.*\r\n\r\n#whatsapp#\r\n\r\n*É sempre um prazer te atender.*\r\n\r\n*Att.:* #server_name#\r\n\r\n', '2023-07-05 18:04:28', '2024-04-01 09:45:46'),
(28, 'default_test_template_code', '*Bem-vindo* a #server_name#\r\n\r\n*CÓDIGO:* #username#\r\n\r\n⚠️ Entre na lojinha da *PLAYONE TV* e baixe o aplicativo *P2P VIA CÓDIGO* \r\n\r\n✅ *APLICATIVO NO SITE*\r\n\r\nhttps://web.playonelojinha.com/\r\n\r\n✅ *APLICATIVO NA PLAY STORE*\r\n\r\nAplicativo na Play Store via *NTDOWN.*\r\nPara fazer o DOWNLOAD digite o PIN escolhido do aplicativo\r\n\r\n*Seu usuário é valido até:* #exp_date#\r\n\r\n*Att.:* #server_name#', '2023-07-05 18:04:28', '2024-04-01 19:25:55'),
(29, 'default_test_template_iptv', '*Bem-vindo a #server_name#*\r\n\r\n✅ *Usuário:* #username#\r\n✅ *Senha:* #password#\r\n⚠️ *Sua assinatura vence dia:*\r\n⚠️ #exp_date#\r\n\r\n✅ *Lojinha WEB *PLAYONE TV* para Baixar aplicativos!*\r\n\r\nhttps://web.playonelojinha.com/\r\n\r\n✅  *APLICATIVO NA PLAY STORE*\r\naplicativo na Play Store via *NTDOWN*\r\nconsulte o PIN no SITE:  https://web.playonelojinha.com/ \r\nbaixar o seu APK preferido atraves do *NTDOWN*\r\n\r\n✅  *Web Player para PC navegador:*\r\n Em construção!\r\n\r\n✅  *URL DNS para aplicativo universal:*\r\n Em construção!\r\n\r\n⚠️ *Link M3U HLS GRANDE:*\r\n#m3u_link_hls#\r\n\r\n⚠️ *Link M3U MPEGTS GRANDE:*\r\n#m3u_link_mpegts#\r\n\r\n⚠️ *LINK APENAS PARA SSIPTV*\r\n#ssiptv_link#\r\n\r\n*Att.:* #server_name#', '2023-07-05 18:04:28', '2024-04-01 19:22:47'),
(30, 'default_test_template_p2p', 'Bem vindo a #server_name#\r\nDados de acesso P2P\r\n\r\nUsuário: #username#\r\nSenha: #password#\r\nValido até: #exp_date#\r\n\r\n⚠️ ENTRE NA LOJINHA E BAIXE O APLICATIVO*\r\n\r\n✅ APLICATIVO NA PLAY STORE\r\n\r\nAplicativo na PLAY STORE VIA NTDOWN.\r\nPIN DE DOWNLOAD/ PIN : 33393\r\n\r\nAtt: #server_name#', '2023-07-05 18:04:28', '2024-03-18 23:39:21'),
(31, 'disabled_days_automatic_test', '', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(32, 'email_messages', '{\"auto_test_subject\":\"Seu teste gratuito\",\"auto_test_message\":\"\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t<div style=\\\"padding:0;margin:0;background-color:#f4f4f4\\\">\\r\\n<table border=\\\"0\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" height=\\\"100%\\\" style=\\\"margin:0;background-color:#f0f0f0\\\" width=\\\"100%\\\">\\r\\n\\t<tbody>\\r\\n\\t\\t<tr>\\r\\n\\t\\t\\t<td align=\\\"center\\\" style=\\\"font-family:Arial,sans-serif;line-height:1.3em;color:#606060;padding:0\\\" valign=\\\"top\\\">\\r\\n\\t\\t\\t<table align=\\\"center\\\" border=\\\"0\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" style=\\\"background-color:#fff;max-width:600px;margin:0;padding-bottom:20px\\\">\\r\\n\\t\\t\\t\\t<tbody>\\r\\n\\t\\t\\t\\t\\t\\r\\n\\t\\t\\t\\t\\t<tr>\\r\\n\\t\\t\\t\\t\\t\\t<td style=\\\"padding:0;font-family:Arial,sans-serif;text-align:center;line-height:1.5em;font-size:1.5em;color:#333;font-weight:200\\\">\\r\\n\\t\\t\\t\\t\\t\\t<p style=\\\"margin:30px 50px\\\">Seja bem vindo(a) <strong>#username#!<\\/strong><br>\\r\\n\\t\\t\\t\\t\\t\\tSeu teste come\\u00e7ou!<\\/p>\\r\\n\\t\\t\\t\\t\\t\\t<\\/td>\\r\\n\\t\\t\\t\\t\\t<\\/tr>\\r\\n\\t\\t\\t\\t\\t\\r\\n\\t\\t\\t\\t\\t<tr>\\r\\n\\t\\t\\t\\t\\t\\t<td style=\\\"padding:0;font-family:Arial,sans-serif;text-align:center;line-height:1.5em;font-size:1.5em;color:#333;font-weight:200\\\">\\r\\n\\t\\t\\t\\t\\t\\t<table border=\\\"0\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" width=\\\"100%\\\">\\r\\n\\t\\t\\t\\t\\t\\t\\t<tbody>\\r\\n\\t\\t\\t\\t\\t\\t\\t\\t<tr>\\r\\n\\t\\t\\t\\t\\t\\t\\t\\t\\t<td colspan=\\\"2\\\" style=\\\"background-color:#ff6828;color:#ffffff;font-family:sans-serif;font-size:14px;line-height:40px;margin-bottom:10px;text-align:center;text-decoration:none;width:100%;font-weight:600\\\">INFORMA\\u00c7\\u00d5ES<\\/td>\\r\\n\\t\\t\\t\\t\\t\\t\\t\\t<\\/tr>\\r\\n\\t\\t\\t\\t\\t\\t\\t\\t\\r\\n\\t\\t\\t\\t\\t\\t\\t\\t\\r\\n\\t\\t\\t\\t\\t\\t\\t\\t<tr>\\r\\n\\t\\t\\t\\t\\t\\t\\t\\t\\t<td style=\\\"padding:10px;font-family:Arial,sans-serif;text-align:left;color:#333;font-weight:600\\\">Usu\\u00e1rio<\\/td>\\r\\n\\t\\t\\t\\t\\t\\t\\t\\t\\t<td style=\\\"padding:10px;font-family:Arial,sans-serif;text-align:left;color:#333;font-weight:600\\\">#username#<\\/td>\\r\\n\\t\\t\\t\\t\\t\\t\\t\\t<\\/tr>\\r\\n\\t\\t\\t\\t\\t\\t\\t\\t<tr>\\r\\n\\t\\t\\t\\t\\t\\t\\t\\t\\t<td style=\\\"padding:10px;font-family:Arial,sans-serif;text-align:left;color:#333;font-weight:600\\\">Senha<\\/td>\\r\\n\\t\\t\\t\\t\\t\\t\\t\\t\\t<td style=\\\"padding:10px;font-family:Arial,sans-serif;text-align:left;color:#333;font-weight:600\\\">#password#<\\/td>\\r\\n<\\/tr>\\r\\n<tr><td style=\\\"padding:10px;font-family:Arial,sans-serif;text-align:left;color:#333;font-weight:600\\\">Lista Completa<\\/td>\\r\\n\\t\\t\\t\\t\\t\\t\\t\\t\\t<td style=\\\"padding:10px;font-family:Arial,sans-serif;text-align:left;color:#333;font-weight:600\\\"><a href=\\\"#m3u_link#\\\" target=\\\"_blank\\\">#m3u_link#<\\/a><\\/td>\\r\\n\\t\\t\\t\\t\\t\\t\\t\\t<\\/tr>\\r\\n<tr><td style=\\\"padding:10px;font-family:Arial,sans-serif;text-align:left;color:#333;font-weight:600\\\">Lista SSIPTV<\\/td>\\r\\n\\t\\t\\t\\t\\t\\t\\t\\t\\t<td style=\\\"padding:10px;font-family:Arial,sans-serif;text-align:left;color:#333;font-weight:600\\\"><a href=\\\"#ssiptv_link#\\\" target=\\\"_blank\\\">#ssiptv_link#<\\/a><\\/td>\\r\\n\\t\\t\\t\\t\\t\\t\\t\\t<\\/tr>\\r\\n\\t\\t\\t\\t\\t\\t\\t<\\/tbody>\\r\\n\\t\\t\\t\\t\\t\\t<\\/table>\\r\\n\\t\\t\\t\\t\\t\\t<\\/td>\\r\\n\\t\\t\\t\\t\\t<\\/tr>\\r\\n\\t\\t\\t\\t\\t<tr>\\r\\n\\t\\t\\t\\t\\t\\t<td style=\\\"background-color:#ff6828;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:14px;line-height:40px;margin-bottom:10px;text-align:center;text-decoration:none;width:100%;font-weight:600\\\">APLICATIVOS<\\/td>\\r\\n\\t\\t\\t\\t\\t<\\/tr>\\r\\n\\t\\t\\t\\t\\t<tr>\\r\\n\\t\\t\\t\\t\\t\\t<td style=\\\"padding:0;font-family:Arial,sans-serif;text-align:left;line-height:1.25em;font-size:1.25em;color:#333;font-weight:400\\\">\\r\\n\\t\\t\\t\\t\\t\\t<p style=\\\"margin:30px 50px\\\"><span style=\\\"font-weight:600\\\">Assistir Navegador:<\\/span> <a href=\\\"\\\" target=\\\"_blank\\\"><\\/a><br>\\r\\n\\t\\t\\t\\t\\t\\t<span style=\\\"font-weight:600\\\">Assistir Android(app):<\\/span> <a href=\\\"\\\" target=\\\"_blank\\\"><\\/a><br>\\r\\n\\t\\t\\t\\t\\t\\t <\\/p>\\r\\n\\r\\n\\t\\t\\t\\t\\t\\t<p style=\\\"margin:30px 50px\\\"><span style=\\\"font-weight:600\\\">TV Smart LG:<\\/span> Use o aplicativo Smarters IPTV (\\u00edcone roxo)<br>\\r\\n\\t\\t\\t\\t\\t\\t<span style=\\\"font-weight:600\\\">TV Smart demais marcas:<\\/span> Use o aplicativo SSIPTV (\\u00edcone cinza)<\\/p>\\r\\n\\t\\t\\t\\t\\t\\t<\\/td>\\r\\n\\t\\t\\t\\t\\t<\\/tr>\\r\\n\\t\\t\\t\\t\\t<tr>\\r\\n\\t\\t\\t\\t\\t\\t\\r\\n\\t\\t\\t\\t\\t<\\/tr>\\r\\n\\t\\t\\t\\t\\t<tr>\\r\\n\\t\\t\\t\\t\\t\\t<td style=\\\"background-color:#ff6828;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:14px;line-height:40px;margin-bottom:10px;text-align:center;text-decoration:none;width:100%;font-weight:600\\\">FORMAS DE PAGAMENTO<\\/td>\\r\\n\\t\\t\\t\\t\\t<\\/tr>\\r\\n\\t\\t\\t\\t\\t<tr>\\r\\n\\t\\t\\t\\t\\t\\t\\r\\n\\t\\t\\t\\t\\t<\\/tr>\\r\\n\\t\\t\\t\\t\\t<tr>\\r\\n\\t\\t\\t\\t\\t\\t<td style=\\\"padding:20px 20px 40px 0;font-family:Arial,sans-serif;text-align:center;line-height:1.4em;font-size:1.35em;color:#fff;font-weight:700\\\"><a href=\\\"\\\" style=\\\"border-radius:3px;padding:10px 40px;background-color:#e70d01;color:#fff;text-transform:uppercase;text-decoration:none;display:block;width:300px;margin:0 auto\\\" target=\\\"_blank\\\">Pagar agora! <\\/a><\\/td>\\r\\n\\t\\t\\t\\t\\t<\\/tr>\\r\\n\\t\\t\\t\\t\\t<tr>\\r\\n\\t\\t\\t\\t\\t\\t<td style=\\\"background-color:#ff6828;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:14px;line-height:40px;margin-bottom:10px;text-align:center;text-decoration:none;width:100%;font-weight:600\\\">FORMAS DE CONTATO<\\/td>\\r\\n\\t\\t\\t\\t\\t<\\/tr>\\r\\n\\t\\t\\t\\t\\t<tr>\\r\\n\\t\\t\\t\\t\\t\\t<td style=\\\"padding:0;font-family:Arial,sans-serif;text-align:left;line-height:1.25em;font-size:1.25em;color:#333;font-weight:400\\\">\\r\\n\\t\\t\\t\\t\\t\\t<p style=\\\"margin:30px 50px\\\"> \\r\\n\\t\\t\\t\\t\\t\\t<span style=\\\"font-weight:600\\\">Whatsapp:<\\/span> <a href=\\\"#whatsapp#\\\" style=\\\"text-decoration:none\\\" target=\\\"_blank\\\">#whatsapp#<\\/a><\\/p>\\r\\n\\t\\t\\t\\t\\t\\t<\\/td>\\r\\n\\t\\t\\t\\t\\t<\\/tr>\\r\\n\\t\\t\\t\\t<\\/tbody>\\r\\n\\t\\t\\t<\\/table>\\r\\n\\t\\t\\t<\\/td>\\r\\n\\t\\t<\\/tr>\\r\\n\\t<\\/tbody>\\r\\n<\\/table>\\r\\n<\\/div>\\r\\n\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\",\"files\":\"\",\"auto_test_subject_code\":\"\",\"auto_test_message_code\":\"\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\",\"pass_recovery_subject\":\"Recupera\\u00e7\\u00e3o de senha\",\"pass_recovery_message\":\"\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t<p>Ol\\u00e1 #username#,<\\/p>\\r\\n\\r\\n<p>Algu\\u00e9m solicitou uma nova senha. Para alterar sua senha, clique no seguinte link: #reset_link#<\\/p>\\r\\n\\r\\n<p>Atenciosamente,\\u00a0<span style=\\\"font-size: 0.875rem;\\\">#server_name#<\\/span><span style=\\\"font-size: 0.875rem;\\\">.<\\/span><\\/p>\\r\\n\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\",\"expiring_subject\":\"\",\"expiring_message\":\"\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\\t\"}', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(33, 'email_settings', '{\"sender_name\":\"PLAYONE TV \",\"sender_email\":\"office@npanel.app\",\"use_smtp\":\"0\",\"smtp_server\":\"smtp.sendgrid.net\",\"smtp_port\":\"465\",\"smtp_username\":\"apikey\",\"smtp_password\":\"\",\"encryption_type\":\"SSL\"}', '2023-07-05 18:04:28', '2024-03-15 04:41:15'),
(34, 'fast_packages', '[\"1\",\"2\"]', '2023-07-05 18:04:28', '2024-03-09 23:47:10'),
(35, 'fast_test_default_package', '', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(36, 'fast_test_package', '1', '2023-07-05 18:04:28', '2024-03-09 23:44:42'),
(37, 'fixed_informations', '																																								<blockquote class=\"blockquote\"><b>SEJA BEM-VINDO!</b></blockquote><h4><b style=\"background-color:rgb(189,148,0);\">REGRAS</b></h4><div><b style=\"color:inherit;font-size:1.25rem;\">ESTÁ <span style=\"background-color:rgb(206,0,0);\">PROIBIDO  </span>  DIVULGAR O ENDEREÇO DOS PAINÉIS OU IMAGEM DO PAINEL EM REDE SOCIAIS.</b><br /></div><h5><b>O SEU PAINEL PODE SER BLOQUEADO, CASO UTILIZE A IMAGEM DO SERVIDOR DE FORMA ERRADA.<br /></b></h5><h5><b style=\"color:inherit;font-size:1.5rem;background-color:rgb(99,0,0);\">PROIBIDO:</b><br /></h5><h5><b>DIVULGAR O NOME DO SERVIDOR.<br /></b><b>DIVULGAR IMAGENS DO PAINEL ADMINISTRATIVO.<br /></b><b>DIVULGAR ENDEREÇO DOS PAINÉIS.<br /></b><b>DIVULGAR PREÇOS ABAIXO DA TABELA.</b></h5><h4><b style=\"background-color:rgb(57,123,33);\">LIBERADO:</b></h4><h5><b>UTILIZAR SUA PRÓPRIA LOGO PARA PUBLICIDADE.<br /></b><b>DIVULGAR BANNERS DA PLAYONE TV.<br /></b><b>DIVULGAR ATUALIZAÇÕES DE CONTEÚDO AO SEUS CLIENTES.</b></h5><h5><b style=\"color:inherit;font-size:1.25rem;\">ATT:<span style=\"background-color:rgb(181,99,8);\"> PLAYONE TV.</span></b><br /></h5>																																								', '2023-07-05 18:04:28', '2024-03-15 04:24:25'),
(38, 'group_settings', '{\"admin\":\"1\",\"partner\":\"0\",\"ultra\":\"2\",\"master\":\"2\",\"reseller\":\"2\"}', '2023-07-05 18:04:28', '2024-03-10 08:36:20'),
(39, 'iptv_code_characters', '1', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(40, 'iptv_code_size', '8', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(41, 'iptv_max_connections', '1', '2023-07-05 18:04:28', '2024-03-09 23:39:10'),
(42, 'iptv_max_connections_status', '1', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(43, 'iptv_migration_fee', '0', '2023-07-05 18:04:28', '2024-04-01 19:37:58'),
(44, 'iptv_migration_status', '1', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(45, 'iptv_show_m3u_link', '1', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(46, 'iptv_show_online_clients', '1', '2023-07-05 18:04:28', '2023-07-10 00:29:41'),
(47, 'iptv_trust_renew_status', '1', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(48, 'iptv_trust_renew_time', '2', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(49, 'login_background', 'https://i.postimg.cc/fyrTkBCh/image-1.png', '2023-07-05 18:04:28', '2024-03-11 12:47:46'),
(50, 'maintenance', '{\"status\":0,\"message\":\"\",\"button_text\":\"\",\"button_link\":\"\"}', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(51, 'only_valid_emails_automatic_test', '1', '2023-07-05 18:04:28', '2024-03-11 11:35:10'),
(52, 'panel_expiration', '', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(53, 'partner_allowed_pages', 'null', '2023-07-05 18:04:28', '2024-03-09 23:38:24'),
(54, 'random_name_automatic_test', '1', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(55, 'recaptcha_enable', '0', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(56, 'recaptcha_secret_key', '', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(57, 'recaptcha_site_key', '', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(58, 'server_logo_big', 'https://i.postimg.cc/jqJQygYz/222222222222222222222222.png', '2023-07-05 18:04:28', '2024-03-09 22:53:30'),
(59, 'server_logo_small', 'https://i.postimg.cc/jqJQygYz/222222222222222222222222.png', '2023-07-05 18:04:28', '2024-03-09 22:53:30'),
(60, 'server_name', 'PLAYONE TV', '2023-07-05 18:04:28', '2024-03-09 23:04:21'),
(61, 'telegram', 'https://t.me/+siCq1RDayGAzOTVh', '2023-07-05 18:04:28', '2024-03-14 18:37:25'),
(62, 'test_min_credits', '1', '2023-07-05 18:04:28', '2023-07-05 18:04:28'),
(63, 'test_time', '6', '2023-07-05 18:04:28', '2024-03-17 19:32:09'),
(64, 'test_time_custom', '[\"6\"]', '2023-07-05 18:04:28', '2024-03-17 19:16:37'),
(65, 'whatsapp', 'https://web.playonelojinha.com/', '2023-07-05 18:04:28', '2024-03-15 15:55:25');

-- --------------------------------------------------------

--
-- Estrutura para tabela `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) NOT NULL,
  `status` varchar(255) NOT NULL,
  `plan_type` varchar(255) NOT NULL,
  `plan_id` varchar(255) NOT NULL,
  `gateway_name` varchar(255) DEFAULT NULL,
  `seller_id` bigint(20) NOT NULL,
  `buyer_id` bigint(20) NOT NULL,
  `amount` float NOT NULL,
  `ip` varchar(255) NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  `modified_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `payments`
--

INSERT INTO `payments` (`id`, `status`, `plan_type`, `plan_id`, `gateway_name`, `seller_id`, `buyer_id`, `amount`, `ip`, `created_at`, `modified_at`) VALUES
(1, 'approved', 'client_renew', 'plan1', 'mercadopago', 10, 116, 2, 'unknown', 1710154063, 1710154180),
(2, 'approved', 'client_renew', 'plan1', 'mercadopago', 19, 202, 2, 'unknown', 1710477297, 1710477325);

-- --------------------------------------------------------

--
-- Estrutura para tabela `test_historic`
--

CREATE TABLE `test_historic` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `test_historic`
--

INSERT INTO `test_historic` (`id`, `email`, `ip`, `user_agent`, `created_at`, `type`) VALUES
(1, 'stephanoreinaldops@hotmail.com', '187.19.185.44', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36', '2024-03-10 04:51:07', 'iptv');

-- --------------------------------------------------------

--
-- Estrutura para tabela `urls`
--

CREATE TABLE `urls` (
  `id` int(10) UNSIGNED NOT NULL,
  `url` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_url` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `creator_id` int(11) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_properties`
--

CREATE TABLE `user_properties` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `property` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `user_properties`
--

INSERT INTO `user_properties` (`id`, `userid`, `property`, `value`, `created_at`, `updated_at`) VALUES
(294, 232, 'dark_mode', '1', '2023-08-17 00:36:38', '2023-08-17 00:36:38'),
(412, 12, 'chatbot_token', '90d62947-cfa6-4baf-9e3e-91a9c13ab7cc', '2024-03-14 00:20:11', '2024-03-14 00:20:11'),
(443, 17, 'chatbot_token', '5634b9e4-702c-4f8d-b668-d50d9af85d6b', '2024-03-14 20:48:20', '2024-03-14 20:48:20'),
(480, 17, 'client_area_plans', '[{\"id\":\"plan1\",\"name\":\"1 Mês\",\"price\":\"0\", \"duration\":\"1\"},{\"id\":\"plan2\",\"name\":\"2 Meses\",\"price\":\"0\", \"duration\":\"2\"},{\"id\":\"plan3\",\"name\":\"3 Meses\",\"price\":\"0\", \"duration\":\"3\"},{\"id\":\"plan6\",\"name\":\"6 Meses\",\"price\":\"0\", \"duration\":\"6\"},{\"id\":\"plan12\",\"name\":\"1 Ano\",\"price\":\"0\", \"duration\":\"12\"},{\"id\":\"plan24\",\"name\":\"2 Anos\",\"price\":\"0\", \"duration\":\"24\"}]', '2024-03-15 04:57:34', '2024-03-15 04:57:34'),
(484, 21, 'whatsapp', '', '2024-03-17 13:46:01', '2024-03-17 13:46:01'),
(485, 21, 'telegram', '', '2024-03-17 13:46:01', '2024-03-17 13:46:01'),
(486, 21, 'fast_test_template', '', '2024-03-17 13:46:01', '2024-03-17 13:46:01'),
(487, 21, 'client_price', '6.00', '2024-03-17 13:46:01', '2024-04-01 19:30:12'),
(488, 21, 'code_client_price', '6.00', '2024-03-17 13:46:01', '2024-04-01 19:30:12'),
(489, 21, 'binstream_client_price', '', '2024-03-17 13:46:01', '2024-03-17 13:46:01'),
(490, 21, 'iptv_enabled', 'enabled', '2024-03-17 13:46:01', '2024-03-17 13:46:01'),
(491, 21, 'binstream_enabled', 'disabled', '2024-03-17 13:46:01', '2024-03-17 13:46:01'),
(492, 21, 'codes_enabled', 'enabled', '2024-03-17 13:46:01', '2024-03-18 22:58:33'),
(494, 17, 'test_key_code', '1eb93f87-7113-4525-9ceb-fb0f6643ccf4', '2024-03-17 22:31:58', '2024-03-17 22:31:58'),
(503, 17, 'dark_mode', '1', '2024-03-19 21:46:49', '2024-03-19 21:46:49'),
(515, 17, 'whatsapp', '', '2024-04-01 19:02:56', '2024-04-01 19:02:56'),
(516, 17, 'telegram', '', '2024-04-01 19:02:56', '2024-04-01 19:02:56'),
(519, 21, 'last_recharge', '1712155297', '2024-04-03 14:41:37', '2024-04-03 14:41:37'),
(520, 17, 'client_price', '0.00', '2024-04-04 02:44:28', '2024-04-04 02:44:28'),
(521, 17, 'code_client_price', '0.00', '2024-04-04 02:44:28', '2024-04-04 02:44:28'),
(522, 17, 'binstream_client_price', '', '2024-04-04 02:44:28', '2024-04-04 02:44:28'),
(523, 17, 'iptv_enabled', 'enabled', '2024-04-04 02:44:28', '2024-04-04 02:44:28'),
(524, 17, 'codes_enabled', 'enabled', '2024-04-04 02:44:28', '2024-04-04 02:44:28'),
(525, 17, 'binstream_enabled', 'disabled', '2024-04-04 02:44:28', '2024-04-04 02:44:28'),
(526, 23, 'whatsapp', '', '2024-04-04 02:51:37', '2024-04-04 07:00:59'),
(527, 23, 'telegram', '', '2024-04-04 02:51:37', '2024-04-04 02:51:37'),
(528, 23, 'fast_test_template', '', '2024-04-04 02:51:37', '2024-04-04 02:51:37'),
(529, 23, 'client_price', '6.00', '2024-04-04 02:51:37', '2024-04-04 02:51:37'),
(530, 23, 'code_client_price', '6.00', '2024-04-04 02:51:37', '2024-04-04 02:51:37'),
(531, 23, 'binstream_client_price', '', '2024-04-04 02:51:37', '2024-04-04 02:51:37'),
(532, 23, 'iptv_enabled', 'enabled', '2024-04-04 02:51:37', '2024-04-04 02:51:37'),
(533, 23, 'binstream_enabled', 'disabled', '2024-04-04 02:51:37', '2024-04-04 02:51:37'),
(534, 23, 'codes_enabled', 'enabled', '2024-04-04 02:51:37', '2024-04-04 02:51:37'),
(535, 23, 'last_recharge', '1712199097', '2024-04-04 02:51:37', '2024-04-04 02:51:37'),
(536, 24, 'whatsapp', '', '2024-04-04 15:04:42', '2024-04-04 15:04:42'),
(537, 24, 'telegram', '', '2024-04-04 15:04:42', '2024-04-04 15:04:42'),
(538, 24, 'fast_test_template', '', '2024-04-04 15:04:42', '2024-04-04 15:04:42'),
(539, 24, 'client_price', '0.00', '2024-04-04 15:04:42', '2024-04-04 15:04:42'),
(540, 24, 'code_client_price', '0.00', '2024-04-04 15:04:43', '2024-04-04 15:04:43'),
(541, 24, 'binstream_client_price', '', '2024-04-04 15:04:43', '2024-04-04 15:04:43'),
(542, 24, 'iptv_enabled', 'enabled', '2024-04-04 15:04:43', '2024-04-04 15:04:43'),
(543, 24, 'binstream_enabled', 'disabled', '2024-04-04 15:04:43', '2024-04-04 15:04:43'),
(544, 24, 'codes_enabled', 'enabled', '2024-04-04 15:04:43', '2024-04-04 15:04:43'),
(545, 24, 'last_recharge', '1712243083', '2024-04-04 15:04:43', '2024-04-04 15:04:43'),
(548, 25, 'fast_test_template', '', '2024-04-04 21:26:57', '2024-04-04 21:26:57'),
(549, 25, 'client_price', '6.00', '2024-04-04 21:26:57', '2024-04-04 21:26:57'),
(550, 25, 'code_client_price', '6.00', '2024-04-04 21:26:57', '2024-04-04 21:26:57'),
(551, 25, 'binstream_client_price', '', '2024-04-04 21:26:57', '2024-04-04 21:26:57'),
(552, 25, 'iptv_enabled', 'enabled', '2024-04-04 21:26:57', '2024-04-04 21:26:57'),
(553, 25, 'binstream_enabled', 'disabled', '2024-04-04 21:26:57', '2024-04-04 21:26:57'),
(554, 25, 'codes_enabled', 'enabled', '2024-04-04 21:26:57', '2024-04-04 21:26:57'),
(555, 25, 'last_recharge', '1712266017', '2024-04-04 21:26:57', '2024-04-04 21:26:57'),
(560, 26, 'whatsapp', '', '2024-04-04 21:31:51', '2024-04-04 21:31:51'),
(561, 26, 'telegram', '', '2024-04-04 21:31:51', '2024-04-04 21:31:51'),
(562, 26, 'fast_test_template', '', '2024-04-04 21:31:51', '2024-04-04 21:31:51'),
(563, 26, 'client_price', '6.00', '2024-04-04 21:31:51', '2024-04-04 21:31:51'),
(564, 26, 'code_client_price', '6.00', '2024-04-04 21:31:51', '2024-04-04 21:31:51'),
(565, 26, 'binstream_client_price', '', '2024-04-04 21:31:51', '2024-04-04 21:31:51'),
(566, 26, 'iptv_enabled', 'enabled', '2024-04-04 21:31:51', '2024-04-04 21:31:51'),
(567, 26, 'binstream_enabled', 'disabled', '2024-04-04 21:31:51', '2024-04-04 21:31:51'),
(568, 26, 'codes_enabled', 'enabled', '2024-04-04 21:31:51', '2024-04-04 21:31:51'),
(569, 26, 'last_recharge', '1712266311', '2024-04-04 21:31:51', '2024-04-04 21:31:51'),
(570, 27, 'whatsapp', '', '2024-04-04 21:34:41', '2024-04-04 21:34:41'),
(571, 27, 'telegram', '', '2024-04-04 21:34:41', '2024-04-04 21:34:41'),
(572, 27, 'fast_test_template', '', '2024-04-04 21:34:41', '2024-04-04 21:34:41'),
(573, 27, 'client_price', '6.00', '2024-04-04 21:34:41', '2024-04-04 21:34:41'),
(574, 27, 'code_client_price', '6.00', '2024-04-04 21:34:41', '2024-04-04 21:34:41'),
(575, 27, 'binstream_client_price', '', '2024-04-04 21:34:41', '2024-04-04 21:34:41'),
(576, 27, 'iptv_enabled', 'enabled', '2024-04-04 21:34:41', '2024-04-04 21:34:41'),
(577, 27, 'binstream_enabled', 'disabled', '2024-04-04 21:34:41', '2024-04-04 21:34:41'),
(578, 27, 'codes_enabled', 'enabled', '2024-04-04 21:34:41', '2024-04-04 21:34:41'),
(579, 27, 'last_recharge', '1712266481', '2024-04-04 21:34:41', '2024-04-04 21:34:41'),
(580, 28, 'whatsapp', '', '2024-04-04 21:46:14', '2024-04-04 21:46:14'),
(581, 28, 'telegram', '', '2024-04-04 21:46:14', '2024-04-04 21:46:14'),
(582, 28, 'fast_test_template', '', '2024-04-04 21:46:14', '2024-04-04 21:46:14'),
(583, 28, 'client_price', '6.00', '2024-04-04 21:46:14', '2024-04-04 21:46:14'),
(584, 28, 'code_client_price', '6.00', '2024-04-04 21:46:14', '2024-04-04 21:46:14'),
(585, 28, 'binstream_client_price', '', '2024-04-04 21:46:14', '2024-04-04 21:46:14'),
(586, 28, 'iptv_enabled', 'enabled', '2024-04-04 21:46:14', '2024-04-04 21:46:14'),
(587, 28, 'binstream_enabled', 'disabled', '2024-04-04 21:46:14', '2024-04-04 21:46:14'),
(588, 28, 'codes_enabled', 'enabled', '2024-04-04 21:46:14', '2024-04-04 21:46:14'),
(589, 28, 'last_recharge', '1712267174', '2024-04-04 21:46:14', '2024-04-04 21:46:14'),
(590, 29, 'whatsapp', '', '2024-04-04 21:58:24', '2024-04-04 21:58:24'),
(591, 29, 'telegram', '', '2024-04-04 21:58:24', '2024-04-04 21:58:24'),
(592, 29, 'fast_test_template', '', '2024-04-04 21:58:25', '2024-04-04 21:58:25'),
(593, 29, 'client_price', '6.00', '2024-04-04 21:58:25', '2024-04-04 21:58:25'),
(594, 29, 'code_client_price', '6.00', '2024-04-04 21:58:25', '2024-04-04 21:58:25'),
(595, 29, 'binstream_client_price', '', '2024-04-04 21:58:25', '2024-04-04 21:58:25'),
(596, 29, 'iptv_enabled', 'enabled', '2024-04-04 21:58:25', '2024-04-04 21:58:25'),
(597, 29, 'binstream_enabled', 'disabled', '2024-04-04 21:58:25', '2024-04-04 21:58:25'),
(598, 29, 'codes_enabled', 'enabled', '2024-04-04 21:58:25', '2024-04-04 21:58:25'),
(599, 29, 'last_recharge', '1712267905', '2024-04-04 21:58:25', '2024-04-04 21:58:25'),
(600, 30, 'whatsapp', '', '2024-04-04 22:02:19', '2024-04-04 22:02:19'),
(601, 30, 'telegram', '', '2024-04-04 22:02:19', '2024-04-04 22:02:19'),
(602, 30, 'fast_test_template', '', '2024-04-04 22:02:19', '2024-04-04 22:02:19'),
(603, 30, 'client_price', '6.00', '2024-04-04 22:02:19', '2024-04-04 22:02:19'),
(604, 30, 'code_client_price', '6.00', '2024-04-04 22:02:19', '2024-04-04 22:02:19'),
(605, 30, 'binstream_client_price', '', '2024-04-04 22:02:19', '2024-04-04 22:02:19'),
(606, 30, 'iptv_enabled', 'enabled', '2024-04-04 22:02:19', '2024-04-04 22:02:19'),
(607, 30, 'binstream_enabled', 'disabled', '2024-04-04 22:02:19', '2024-04-04 22:02:19'),
(608, 30, 'codes_enabled', 'enabled', '2024-04-04 22:02:19', '2024-04-04 22:02:19'),
(609, 30, 'last_recharge', '1712268139', '2024-04-04 22:02:19', '2024-04-04 22:02:19'),
(610, 31, 'whatsapp', '', '2024-04-04 22:09:37', '2024-04-04 22:09:37'),
(611, 31, 'telegram', '', '2024-04-04 22:09:37', '2024-04-04 22:09:37'),
(612, 31, 'fast_test_template', '', '2024-04-04 22:09:37', '2024-04-04 22:09:37'),
(613, 31, 'client_price', '6.00', '2024-04-04 22:09:37', '2024-04-04 22:09:37'),
(614, 31, 'code_client_price', '6.00', '2024-04-04 22:09:37', '2024-04-04 22:09:37'),
(615, 31, 'binstream_client_price', '', '2024-04-04 22:09:37', '2024-04-04 22:09:37'),
(616, 31, 'iptv_enabled', 'enabled', '2024-04-04 22:09:37', '2024-04-04 22:09:37'),
(617, 31, 'binstream_enabled', 'disabled', '2024-04-04 22:09:37', '2024-04-04 22:09:37'),
(618, 31, 'codes_enabled', 'enabled', '2024-04-04 22:09:37', '2024-04-04 22:09:37'),
(619, 31, 'last_recharge', '1712268577', '2024-04-04 22:09:37', '2024-04-04 22:09:37'),
(620, 32, 'whatsapp', '', '2024-04-04 22:14:32', '2024-04-04 22:14:32'),
(621, 32, 'telegram', '', '2024-04-04 22:14:32', '2024-04-04 22:14:32'),
(622, 32, 'fast_test_template', '', '2024-04-04 22:14:32', '2024-04-04 22:14:32'),
(623, 32, 'client_price', '6.00', '2024-04-04 22:14:32', '2024-04-04 22:14:32'),
(624, 32, 'code_client_price', '6.00', '2024-04-04 22:14:32', '2024-04-04 22:14:32'),
(625, 32, 'binstream_client_price', '', '2024-04-04 22:14:32', '2024-04-04 22:14:32'),
(626, 32, 'iptv_enabled', 'enabled', '2024-04-04 22:14:32', '2024-04-04 22:14:32'),
(627, 32, 'binstream_enabled', 'disabled', '2024-04-04 22:14:32', '2024-04-04 22:14:32'),
(628, 32, 'codes_enabled', 'enabled', '2024-04-04 22:14:32', '2024-04-04 22:14:32'),
(629, 32, 'last_recharge', '1712268872', '2024-04-04 22:14:32', '2024-04-04 22:14:32'),
(630, 33, 'whatsapp', '', '2024-04-04 22:20:23', '2024-04-04 22:20:23'),
(631, 33, 'telegram', '', '2024-04-04 22:20:23', '2024-04-04 22:20:23'),
(632, 33, 'fast_test_template', '', '2024-04-04 22:20:23', '2024-04-04 22:20:23'),
(633, 33, 'client_price', '6.00', '2024-04-04 22:20:23', '2024-04-04 22:20:23'),
(634, 33, 'code_client_price', '6.00', '2024-04-04 22:20:23', '2024-04-04 22:20:23'),
(635, 33, 'binstream_client_price', '', '2024-04-04 22:20:23', '2024-04-04 22:20:23'),
(636, 33, 'iptv_enabled', 'enabled', '2024-04-04 22:20:23', '2024-04-04 22:20:23'),
(637, 33, 'binstream_enabled', 'disabled', '2024-04-04 22:20:23', '2024-04-04 22:20:23'),
(638, 33, 'codes_enabled', 'enabled', '2024-04-04 22:20:23', '2024-04-04 22:20:23'),
(639, 33, 'last_recharge', '1712269223', '2024-04-04 22:20:23', '2024-04-04 22:20:23'),
(640, 34, 'whatsapp', '', '2024-04-04 22:23:26', '2024-04-04 22:23:26'),
(641, 34, 'telegram', '', '2024-04-04 22:23:26', '2024-04-04 22:23:26'),
(642, 34, 'fast_test_template', '', '2024-04-04 22:23:26', '2024-04-04 22:23:26'),
(643, 34, 'client_price', '6.00', '2024-04-04 22:23:26', '2024-04-04 22:23:26'),
(644, 34, 'code_client_price', '6.00', '2024-04-04 22:23:26', '2024-04-04 22:23:26'),
(645, 34, 'binstream_client_price', '', '2024-04-04 22:23:26', '2024-04-04 22:23:26'),
(646, 34, 'iptv_enabled', 'enabled', '2024-04-04 22:23:26', '2024-04-04 22:23:26'),
(647, 34, 'binstream_enabled', 'disabled', '2024-04-04 22:23:26', '2024-04-04 22:23:26'),
(648, 34, 'codes_enabled', 'enabled', '2024-04-04 22:23:26', '2024-04-04 22:23:26'),
(649, 34, 'last_recharge', '1712269406', '2024-04-04 22:23:26', '2024-04-04 22:23:26'),
(650, 35, 'whatsapp', '', '2024-04-04 22:28:47', '2024-04-04 22:28:47'),
(651, 35, 'telegram', '', '2024-04-04 22:28:47', '2024-04-04 22:28:47'),
(652, 35, 'fast_test_template', '', '2024-04-04 22:28:47', '2024-04-04 22:28:47'),
(653, 35, 'client_price', '10.00', '2024-04-04 22:28:47', '2024-04-04 22:28:47'),
(654, 35, 'code_client_price', '10.00', '2024-04-04 22:28:47', '2024-04-04 22:28:47'),
(655, 35, 'binstream_client_price', '', '2024-04-04 22:28:47', '2024-04-04 22:28:47'),
(656, 35, 'iptv_enabled', 'enabled', '2024-04-04 22:28:47', '2024-04-04 22:28:47'),
(657, 35, 'binstream_enabled', '0', '2024-04-04 22:28:47', '2024-04-04 22:28:47'),
(658, 35, 'codes_enabled', 'enabled', '2024-04-04 22:28:47', '2024-04-04 22:28:47'),
(659, 35, 'last_recharge', '1712269770', '2024-04-04 22:29:30', '2024-04-04 22:29:30'),
(660, 36, 'whatsapp', '', '2024-04-04 22:32:23', '2024-04-04 22:32:23'),
(661, 36, 'telegram', '', '2024-04-04 22:32:23', '2024-04-04 22:32:23'),
(662, 36, 'fast_test_template', '', '2024-04-04 22:32:23', '2024-04-04 22:32:23'),
(663, 36, 'client_price', '6.00', '2024-04-04 22:32:23', '2024-04-04 22:32:23'),
(664, 36, 'code_client_price', '6.00', '2024-04-04 22:32:23', '2024-04-04 22:32:23'),
(665, 36, 'binstream_client_price', '', '2024-04-04 22:32:23', '2024-04-04 22:32:23'),
(666, 36, 'iptv_enabled', 'enabled', '2024-04-04 22:32:23', '2024-04-04 22:32:23'),
(667, 36, 'binstream_enabled', 'disabled', '2024-04-04 22:32:23', '2024-04-04 22:32:23'),
(668, 36, 'codes_enabled', 'enabled', '2024-04-04 22:32:23', '2024-04-04 22:32:23'),
(669, 36, 'last_recharge', '1712269943', '2024-04-04 22:32:23', '2024-04-04 22:32:23'),
(670, 21, 'dark_mode', '1', '2024-04-04 22:57:48', '2024-04-04 22:57:48'),
(671, 37, 'whatsapp', '', '2024-04-04 23:08:05', '2024-04-04 23:08:05'),
(672, 37, 'telegram', '', '2024-04-04 23:08:05', '2024-04-04 23:08:05'),
(673, 37, 'fast_test_template', '', '2024-04-04 23:08:05', '2024-04-04 23:08:05'),
(674, 37, 'client_price', '6.00', '2024-04-04 23:08:05', '2024-04-04 23:08:05'),
(675, 37, 'code_client_price', '6.00', '2024-04-04 23:08:05', '2024-04-04 23:08:05'),
(676, 37, 'binstream_client_price', '', '2024-04-04 23:08:05', '2024-04-04 23:08:05'),
(677, 37, 'iptv_enabled', 'enabled', '2024-04-04 23:08:05', '2024-04-04 23:08:05'),
(678, 37, 'binstream_enabled', 'disabled', '2024-04-04 23:08:05', '2024-04-04 23:08:05'),
(679, 37, 'codes_enabled', 'enabled', '2024-04-04 23:08:05', '2024-04-04 23:08:05'),
(680, 37, 'last_recharge', '1712272085', '2024-04-04 23:08:05', '2024-04-04 23:08:05'),
(681, 38, 'whatsapp', '', '2024-04-04 23:11:40', '2024-04-04 23:11:40'),
(682, 38, 'telegram', '', '2024-04-04 23:11:40', '2024-04-04 23:11:40'),
(683, 38, 'fast_test_template', '', '2024-04-04 23:11:40', '2024-04-04 23:11:40'),
(684, 38, 'client_price', '6.00', '2024-04-04 23:11:40', '2024-04-04 23:11:40'),
(685, 38, 'code_client_price', '6.00', '2024-04-04 23:11:40', '2024-04-04 23:11:40'),
(686, 38, 'binstream_client_price', '', '2024-04-04 23:11:40', '2024-04-04 23:11:40'),
(687, 38, 'iptv_enabled', 'enabled', '2024-04-04 23:11:40', '2024-04-04 23:11:40'),
(688, 38, 'binstream_enabled', 'disabled', '2024-04-04 23:11:40', '2024-04-04 23:11:40'),
(689, 38, 'codes_enabled', 'enabled', '2024-04-04 23:11:40', '2024-04-04 23:11:40'),
(690, 38, 'last_recharge', '1712272300', '2024-04-04 23:11:40', '2024-04-04 23:11:40'),
(693, 39, 'fast_test_template', '', '2024-04-05 00:43:09', '2024-04-05 00:43:09'),
(694, 39, 'client_price', '6.00', '2024-04-05 00:43:09', '2024-04-05 00:43:09'),
(695, 39, 'code_client_price', '6.00', '2024-04-05 00:43:09', '2024-04-05 00:43:09'),
(696, 39, 'binstream_client_price', '', '2024-04-05 00:43:09', '2024-04-05 00:43:09'),
(697, 39, 'iptv_enabled', 'enabled', '2024-04-05 00:43:09', '2024-04-05 00:43:09'),
(698, 39, 'binstream_enabled', 'disabled', '2024-04-05 00:43:09', '2024-04-05 00:43:09'),
(699, 39, 'codes_enabled', 'enabled', '2024-04-05 00:43:09', '2024-04-05 00:43:09'),
(700, 39, 'last_recharge', '1712277789', '2024-04-05 00:43:09', '2024-04-05 00:43:09'),
(701, 28, 'dark_mode', '1', '2024-04-05 00:45:11', '2024-04-05 00:45:11'),
(703, 27, 'dark_mode', '0', '2024-04-05 00:54:31', '2024-04-05 00:54:31'),
(704, 39, 'whatsapp', '', '2024-04-05 00:54:52', '2024-04-05 00:54:52'),
(705, 39, 'telegram', '', '2024-04-05 00:54:52', '2024-04-05 00:54:52'),
(708, 25, 'client_area_plans', '[{\"id\":\"plan1\",\"name\":\"1 Mês\",\"price\":\"0\", \"duration\":\"1\"},{\"id\":\"plan2\",\"name\":\"2 Meses\",\"price\":\"0\", \"duration\":\"2\"},{\"id\":\"plan3\",\"name\":\"3 Meses\",\"price\":\"0\", \"duration\":\"3\"},{\"id\":\"plan6\",\"name\":\"6 Meses\",\"price\":\"0\", \"duration\":\"6\"},{\"id\":\"plan12\",\"name\":\"1 Ano\",\"price\":\"0\", \"duration\":\"12\"},{\"id\":\"plan24\",\"name\":\"2 Anos\",\"price\":\"0\", \"duration\":\"24\"}]', '2024-04-05 10:45:36', '2024-04-05 10:45:36'),
(709, 25, 'dark_mode', '1', '2024-04-05 10:45:45', '2024-04-05 10:45:45'),
(710, 25, 'whatsapp', '', '2024-04-05 10:46:52', '2024-04-05 10:46:52'),
(711, 25, 'telegram', '', '2024-04-05 10:46:52', '2024-04-05 10:46:52'),
(713, 29, 'dark_mode', '0', '2024-04-05 11:54:31', '2024-04-05 11:54:31'),
(714, 40, 'whatsapp', '', '2024-04-05 13:09:15', '2024-04-05 13:09:15'),
(715, 40, 'telegram', '', '2024-04-05 13:09:15', '2024-04-05 13:09:15'),
(716, 40, 'fast_test_template', '', '2024-04-05 13:09:15', '2024-04-05 13:09:15'),
(717, 40, 'client_price', '6.00', '2024-04-05 13:09:15', '2024-04-05 13:09:15'),
(718, 40, 'code_client_price', '6.00', '2024-04-05 13:09:15', '2024-04-05 13:09:15'),
(719, 40, 'binstream_client_price', '', '2024-04-05 13:09:15', '2024-04-05 13:09:15'),
(720, 40, 'iptv_enabled', 'enabled', '2024-04-05 13:09:15', '2024-04-05 13:09:15'),
(721, 40, 'binstream_enabled', 'disabled', '2024-04-05 13:09:15', '2024-04-05 13:09:15'),
(722, 40, 'codes_enabled', 'enabled', '2024-04-05 13:09:15', '2024-04-05 13:09:15'),
(723, 40, 'last_recharge', '1712322555', '2024-04-05 13:09:15', '2024-04-05 13:09:15'),
(724, 29, 'chatbot_token', 'dfccb0de-8b2d-4b3e-84e6-822dbf1c4162', '2024-04-05 16:13:25', '2024-04-05 16:13:25'),
(726, 40, 'dark_mode', '0', '2024-04-05 17:10:28', '2024-04-05 17:10:28');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `chatbot`
--
ALTER TABLE `chatbot`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `chatbot_messages`
--
ALTER TABLE `chatbot_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chatbot_id` (`chatbot_id`);

--
-- Índices de tabela `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `office_properties`
--
ALTER TABLE `office_properties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `property` (`property`);

--
-- Índices de tabela `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `test_historic`
--
ALTER TABLE `test_historic`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `urls`
--
ALTER TABLE `urls`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `user_properties`
--
ALTER TABLE `user_properties`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `chatbot`
--
ALTER TABLE `chatbot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `chatbot_messages`
--
ALTER TABLE `chatbot_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de tabela `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `office_properties`
--
ALTER TABLE `office_properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT de tabela `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `test_historic`
--
ALTER TABLE `test_historic`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `urls`
--
ALTER TABLE `urls`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=780;

--
-- AUTO_INCREMENT de tabela `user_properties`
--
ALTER TABLE `user_properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=727;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `chatbot_messages`
--
ALTER TABLE `chatbot_messages`
  ADD CONSTRAINT `chatbot_messages_ibfk_1` FOREIGN KEY (`chatbot_id`) REFERENCES `chatbot` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
