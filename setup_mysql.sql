-- Script para configurar o banco de dados MySQL para o Mapa de Árvores de Paracambi-RJ
-- Execute este script no MySQL Workbench antes de rodar as migrações do Laravel

-- Criar o banco de dados
CREATE DATABASE IF NOT EXISTS tree_map_paracambi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usar o banco de dados
USE tree_map_paracambi;

-- Mensagem de sucesso
SELECT 'Banco de dados tree_map_paracambi criado com sucesso!' AS Status;
