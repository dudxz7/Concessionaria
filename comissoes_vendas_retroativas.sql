-- Script para criar a tabela de comissões (caso não exista)
CREATE TABLE IF NOT EXISTS comissoes_vendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venda_id INT NOT NULL,
    funcionario_id INT NOT NULL,
    valor_comissao DECIMAL(10,2) NOT NULL,
    data_comissao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (venda_id) REFERENCES vendas_fisicas(id),
    FOREIGN KEY (funcionario_id) REFERENCES clientes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Script para gerar comissões retroativas para vendas já realizadas
INSERT INTO comissoes_vendas (venda_id, funcionario_id, valor_comissao, data_comissao)
SELECT
    v.id,
    v.usuario_id,
    ROUND(v.total * 0.005, 2),
    v.data_venda
FROM vendas_fisicas v
LEFT JOIN comissoes_vendas c ON c.venda_id = v.id
WHERE c.id IS NULL;
