-- MySQL Script generated by MySQL Workbench
-- Sex 14 Nov 2014 16:17:50 BRST
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema selecaoneaad
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema baseCEP
-- -----------------------------------------------------

DELIMITER $$
CREATE TRIGGER `tg_usrBINS_setHash` BEFORE INSERT ON `tb_usr_usuario` FOR EACH ROW
BEGIN
SET new.usr_hash_alteracao_ext = FC_CALCULA_HASH_USUARIO(new.USR_DS_NOME, new.USR_DS_EMAIL, new.USR_DS_SENHA, new.USR_TP_VINCULO_UFES, new.USR_ST_SITUACAO);
END;
$$

CREATE TRIGGER `tg_usr_AINS_cria_conf` AFTER INSERT ON `tb_usr_usuario` FOR EACH ROW
BEGIN
INSERT INTO tb_cfu_configuracao_usuario(`USR_ID_USUARIO`) values (new.USR_ID_USUARIO);
END;
$$

CREATE TRIGGER `tg_usr_BUPD_setHash` BEFORE UPDATE ON `tb_usr_usuario` FOR EACH ROW
begin
SET new.usr_hash_alteracao_ext = FC_CALCULA_HASH_USUARIO(new.USR_DS_NOME, new.USR_DS_EMAIL, new.USR_DS_SENHA, new.USR_TP_VINCULO_UFES, new.USR_ST_SITUACAO);
END;
$$

CREATE TRIGGER `tg_usr_BDEL_del_conf` BEFORE DELETE ON `tb_usr_usuario` FOR EACH ROW
BEGIN
DELETE FROM tb_cfu_configuracao_usuario WHERE `USR_ID_USUARIO` = old.USR_ID_USUARIO;
END;
$$

DELIMITER ;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;