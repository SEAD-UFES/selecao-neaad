-- Cadastro de pólos
INSERT INTO `tb_pol_polo` VALUES (1,'Afonso Cláudio');
INSERT INTO `tb_pol_polo` VALUES (2,'Alegre');
INSERT INTO `tb_pol_polo` VALUES (3,'Aracruz');
INSERT INTO `tb_pol_polo` VALUES (4,'Baixo Guandu');
INSERT INTO `tb_pol_polo` VALUES (5,'Bom Jesus do Norte');
INSERT INTO `tb_pol_polo` VALUES (6,'Cachoeiro de Itapemirim');
INSERT INTO `tb_pol_polo` VALUES (7,'Castelo');
INSERT INTO `tb_pol_polo` VALUES (8,'Colatina');
INSERT INTO `tb_pol_polo` VALUES (9,'Conceição da Barra');
INSERT INTO `tb_pol_polo` VALUES (10,'Domingos Martins');
INSERT INTO `tb_pol_polo` VALUES (11,'Ecoporanga');
INSERT INTO `tb_pol_polo` VALUES (12,'Itapemirim');
INSERT INTO `tb_pol_polo` VALUES (13,'Iúna');
INSERT INTO `tb_pol_polo` VALUES (14,'Linhares');
INSERT INTO `tb_pol_polo` VALUES (15,'Mantenópolis');
INSERT INTO `tb_pol_polo` VALUES (16,'Mimoso do Sul');
INSERT INTO `tb_pol_polo` VALUES (17,'Nova Venécia');
INSERT INTO `tb_pol_polo` VALUES (18,'Pinheiros');
INSERT INTO `tb_pol_polo` VALUES (19,'Piúma');
INSERT INTO `tb_pol_polo` VALUES (20,'Santa Leopoldina');
INSERT INTO `tb_pol_polo` VALUES (21,'Santa Teresa');
INSERT INTO `tb_pol_polo` VALUES (22,'São Mateus');
INSERT INTO `tb_pol_polo` VALUES (23,'Venda Nova do Imigrante');
INSERT INTO `tb_pol_polo` VALUES (24,'Vila Velha');
INSERT INTO `tb_pol_polo` VALUES (25,'Vitória');
INSERT INTO `tb_pol_polo` VALUES (26,'Vargem Alta');
INSERT INTO `tb_pol_polo` VALUES (27,'Montanha');
INSERT INTO `tb_pol_polo` VALUES (28,'Cariacica');
INSERT INTO `tb_pol_polo` VALUES (29,'Serra');
INSERT INTO `tb_pol_polo` VALUES (30,'Viana');
INSERT INTO `tb_pol_polo` VALUES (31,'Guarapari');
INSERT INTO `tb_pol_polo` VALUES (32,'Fundão');


-- Cadastro de tipos de cargo
insert into TB_TIC_TIPO_CARGO values(1, 'Aluno', 'aluno', 'A');
insert into TB_TIC_TIPO_CARGO values(2, 'Coordenador de Polo', 'coordenador-de-polo', 'I');
insert into TB_TIC_TIPO_CARGO values(3, 'Coordenador de Tutoria', 'coordenador-de-tutoria', 'I');
insert into TB_TIC_TIPO_CARGO values(4, 'Professor', 'professor', 'A');
insert into TB_TIC_TIPO_CARGO values(5, 'Tutor a Distância', 'tutor-a-distancia', 'A');
insert into TB_TIC_TIPO_CARGO values(6, 'Tutor Presencial', 'tutor-presencial', 'A');


-- Cadastro de tipos de curso
INSERT INTO TB_TPC_TIPO_CURSO VALUES(1, 1, 'Aperfeiçoamento');
INSERT INTO TB_TPC_TIPO_CURSO VALUES(2, 2, 'Capacitação');
INSERT INTO TB_TPC_TIPO_CURSO VALUES(3, 3, 'Ensino Fundamental');
INSERT INTO TB_TPC_TIPO_CURSO VALUES(4, 4, 'Ensino Médio');
INSERT INTO TB_TPC_TIPO_CURSO VALUES(5, 5, 'Ensino Profissional de Nível Técnico');
INSERT INTO TB_TPC_TIPO_CURSO VALUES(6, 6, 'Graduação');
INSERT INTO TB_TPC_TIPO_CURSO VALUES(7, 7, 'Especialização - Residência Médica');
INSERT INTO TB_TPC_TIPO_CURSO VALUES(8, 8, 'Especialização');
INSERT INTO TB_TPC_TIPO_CURSO VALUES(9, 9, 'Mestrado Profissionalizante');
INSERT INTO TB_TPC_TIPO_CURSO VALUES(10, 10, 'Mestrado');
INSERT INTO TB_TPC_TIPO_CURSO VALUES(11, 11, 'Doutorado');
INSERT INTO TB_TPC_TIPO_CURSO VALUES(12, 12, 'Pós-Doutorado');


-- Cadastro de reservas de vagas
INSERT INTO `tb_rvg_reserva_vaga` (`RVG_ID_RESERVA_VAGA`, `RVG_NM_RESERVA_VAGA`, `RVG_DS_RESERVA_VAGA`, `RVG_ST_RESERVA_VAGA`) VALUES ('1', 'Negros', 'Concorrer às vagas destinadas aos candidatos declarados negros.', 'A');
INSERT INTO `tb_rvg_reserva_vaga` (`RVG_ID_RESERVA_VAGA`, `RVG_NM_RESERVA_VAGA`, `RVG_DS_RESERVA_VAGA`, `RVG_ST_RESERVA_VAGA`) VALUES ('2', 'Educadores  - Rede Pública', 'Concorrer às vagas destinadas aos educadores da rede pública.', 'A');
INSERT INTO `tb_rvg_reserva_vaga` (`RVG_ID_RESERVA_VAGA`, `RVG_NM_RESERVA_VAGA`, `RVG_DS_RESERVA_VAGA`, `RVG_ST_RESERVA_VAGA`) VALUES ('3', 'Educadores - Geral', 'Concorrer às vagas destinadas aos educadores em geral.', 'A');

-- Mais reservas
INSERT INTO `tb_rvg_reserva_vaga` (`RVG_ID_RESERVA_VAGA`, `RVG_NM_RESERVA_VAGA`, `RVG_DS_RESERVA_VAGA`, `RVG_ST_RESERVA_VAGA`) VALUES ('4', 'SUS - ES', 'Concorrer às vagas destinadas aos profissionais que atuam no SUS no estado do ES.', 'A');
INSERT INTO `tb_rvg_reserva_vaga` (`RVG_ID_RESERVA_VAGA`, `RVG_NM_RESERVA_VAGA`, `RVG_DS_RESERVA_VAGA`, `RVG_ST_RESERVA_VAGA`) VALUES ('5', 'SUS - MG, BA e RJ', 'Concorrer às vagas destinadas aos profissionais que atuam no SUS no leste de MG, sul da BA e norte do RJ.', 'A');
INSERT INTO `tb_rvg_reserva_vaga` (`RVG_ID_RESERVA_VAGA`, `RVG_NM_RESERVA_VAGA`, `RVG_DS_RESERVA_VAGA`, `RVG_ST_RESERVA_VAGA`) VALUES ('6', 'Não SUS - ES', 'Concorrer às vagas destinadas aos profissionais que não atuam no SUS no estado do ES.', 'A');
INSERT INTO `tb_rvg_reserva_vaga` (`RVG_ID_RESERVA_VAGA`, `RVG_NM_RESERVA_VAGA`, `RVG_DS_RESERVA_VAGA`, `RVG_ST_RESERVA_VAGA`) VALUES ('7', 'Não SUS - MG, BA e RJ', 'Concorrer às vagas destinadas aos profissionais que não atuam no SUS no leste de MG, sul da BA e norte do RJ.', 'A');


-- Cadastro do administrador: senha = 'adminNeaad'; É necessário trocar a senha no primeiro login
INSERT INTO TB_USR_USUARIO (`USR_DS_EMAIL`,`USR_DS_LOGIN`, `USR_DS_SENHA`, `USR_DS_NOME`,`USR_TP_USUARIO`, `USR_ST_SITUACAO`, `USR_TP_VINCULO_UFES`, `USR_LOG_DT_CRIACAO`, `USR_TROCAR_SENHA`)
VALUES ('suporte.sead.ufes@gmail.com','suporte.sead.ufes@gmail.com', 'd7a54129b4410c97e422f9b02ce36c3b', 'Administrador SEAD do Sistema', 'A', 'A', '0', now(), 'S');
