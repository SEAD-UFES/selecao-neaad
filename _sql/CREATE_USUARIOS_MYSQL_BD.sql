-- Usuários para o banco de desenvolvimento.
-- Local
CREATE USER 'selecaoneaaddev'@'localhost' IDENTIFIED BY 'selecaoneaaddev';
GRANT INSERT, DELETE, UPDATE, SELECT, EXECUTE, TRIGGER ON TABLE selecaoneaaddev.* TO 'selecaoneaaddev'@'localhost';

-- Remoto
CREATE USER 'selecaoneaaddev'@'172.30.4.%' IDENTIFIED BY 'selecaoneaaddev';
GRANT INSERT, DELETE, UPDATE, SELECT, EXECUTE, TRIGGER ON TABLE selecaoneaaddev.* TO 'selecaoneaaddev'@'172.30.4.%';

-- Usuário para o banco de produçao.
CREATE USER 'selecaoneaad'@'localhost' IDENTIFIED BY 'pduH9OXSead';
GRANT INSERT, DELETE, UPDATE, SELECT, EXECUTE, TRIGGER ON TABLE selecaoneaad.* TO 'selecaoneaad'@'localhost';

-- Usuário para o banco de teste. Mesma permissão do usuário de produção
CREATE USER 'selecaoneaadtest'@'localhost' IDENTIFIED BY 'tstH9OXSead';
GRANT INSERT, DELETE, UPDATE, SELECT, EXECUTE, TRIGGER ON TABLE selecaoneaadteste.* TO 'selecaoneaadtest'@'localhost';

-- Remoto (banco de teste - opcional)
CREATE USER 'selecaoneaadtest'@'172.30.4.%' IDENTIFIED BY 'selecaoneaadtest';
GRANT INSERT, DELETE, UPDATE, SELECT, EXECUTE, TRIGGER ON TABLE selecaoneaadteste.* TO 'selecaoneaadtest'@'172.30.4.%';

-- Usuário para scripts de teste. Permissão total na base de teste
CREATE USER 'selecaoneaadcmdt'@'localhost' IDENTIFIED BY 'sctH9OXSead';
GRANT ALL PRIVILEGES ON selecaoneaadteste.* TO 'selecaoneaadcmdt'@'localhost';


-- Opcional: Usuário master para atualização dos BD'S - Pode fazer tudo em tudo!
-- Altere o IP da máquina autorizada, se necessário.
CREATE USER 'selecaoneaadm90'@'172.30.4.%' IDENTIFIED BY 'm90MZquSead';
GRANT ALL PRIVILEGES ON `selecaoneaad`.* TO 'selecaoneaadm90'@'172.30.4.%';
GRANT ALL PRIVILEGES ON `selecaoneaaddev`.* TO 'selecaoneaadm90'@'172.30.4.%';
GRANT ALL PRIVILEGES ON `selecaoneaadteste`.* TO 'selecaoneaadm90'@'172.30.4.%';