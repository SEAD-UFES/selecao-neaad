-- Seleciona um usuário por email
select * from tb_usr_usuario where usr_ds_email = :dsEmail;

-- Atualiza o coordenador de um determinado curso 
update tb_cur_curso
set cur_id_coordenador = :idCoordenador where cur_id_curso= :idCurso;

-- Troca o tipo do usuário
update tb_usr_usuario 
set usr_tp_usuario = :tpUsuario where usr_id_usuario= :idUsuario;

-- Excluir inscriçoes
delete from tb_rap_resp_anexo_proc;
delete from tb_pin_polo_inscricao;
delete from tb_ipr_inscricao_processo;


-- Apagar inscrições de um processo espcifico
delete from tb_rap_resp_anexo_proc where PRC_ID_PROCESSO = :idProcesso;
DELETE FROM tb_pin_polo_inscricao 
WHERE
    IPR_ID_INSCRICAO IN (SELECT 
        IPR_ID_INSCRICAO
    FROM
        tb_ipr_inscricao_processo
    
    WHERE
        PRC_ID_PROCESSO = :idProcesso);
delete from tb_ipr_inscricao_processo  where PRC_ID_PROCESSO = :idProcesso;


-- Forçar Capitalize no BD
UPDATE tb_usr_usuario 
SET 
    usr_ds_nome = fc_capitalize(usr_ds_nome)

-- Criar configuraçao para quem nao tem
insert into tb_cfu_configuracao_usuario(USR_ID_USUARIO)
select 
    USR_ID_USUARIO
from
    tb_usr_usuario
where
    USR_ID_USUARIO not in (select 
            USR_ID_USUARIO
        from
            tb_cfu_configuracao_usuario);

-- Verficando se tem alguem sem configuracao
select 
    count(*)
from
    tb_usr_usuario
where
    USR_ID_USUARIO not in (select 
            USR_ID_USUARIO
        from
            tb_cfu_configuracao_usuario);


-- Update com ordenaçao
SET @counter = 0;
UPDATE 
tb_ipr_inscricao_processo
SET IPR_NR_CLASSIFICACAO_CAND = @counter := @counter + 1
where IPR_VL_TOTAL_NOTA <> 0
ORDER BY IPR_VL_TOTAL_NOTA desc;

-- Forçar reavaliaçao da classificacao
update tb_ipr_inscricao_processo set IPR_ST_AVAL_AUTOMATICA = NULL where pch_id_chamada = :idChamada;
update tb_esp_etapa_sel_proc set ESP_ST_CLASSIFICACAO = NULL where pch_id_chamada = :idChamada;

-- Verificar quem falta analisar a nota do candidato
select * from tb_ipr_inscricao_processo where PRC_ID_PROCESSO = :idProcesso
and IPR_ST_ANALISE IS NULL;

-- Pesquisar email alternativo usado por alguém 
select * from tb_ctc_contato_candidato where CTC_EMAIL_CONTATO like 'email';
select * from tb_cdt_candidato where CTC_ID_CONTATO_CDT = 'idContato';
select * from tb_usr_usuario where USR_ID_USUARIO = 'idUsuario';