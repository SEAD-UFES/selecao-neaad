#!/bin/sh
#------------------------------ #### Instruções #### ------------------------------------------
# Este Script cria o banco de dados para o sistema Seleção EAD
#
# Atenção para o ajuste correto dos parâmetros. Caso o banco já exista ele será excluído, 
# sendo substituido pela nova versão.
#
#----------------------------- #################### -------------------------------------------



#------------------------------ #### Parâmetros #### ------------------------------------------
# Nome do banco de dados
nomeBanco='selecaoneaadteste' # Ex: 'selecaoneaad'

# Nome do servidor onde será criado o BD
servidor='localhost' # Ex: 'localhost'

# Dados do usuário MySql que executará os comandos
usuarioSql='selecaoneaadcmdt'
senhaSql="sctH9OXSead"

# Caminho absoluto da pasta onde se localiza os códigos de criação do banco
pastaSqls='/var/scripts-selecaoneaad/_sql'
#----------------------------- #################### -------------------------------------------






#------------------------------ #### Constantes #### ------------------------------------------
# comando para exclusão de um banco
cmdDelBD='DROP DATABASE IF EXISTS'

# comando para criação de um banco
cmdCriaBD='CREATE DATABASE'

# Pasta tmp
pastaTmp='/tmp'
#----------------------------- #################### -------------------------------------------



#------------------------------ #### Variáveis #### ------------------------------------------
# Definindo string de senha
if [ "$senhaSql" == "" ]
then
  strSenha=''
else
  strSenha="-p$senhaSql"
fi

# Definindo variáveis de execução
execBunzip="bunzip2"
execSql="mysql"
paramSql="-h $servidor -u $usuarioSql $strSenha --default-character-set=utf8"

# ARQUIVOS de pastaSqls
arqCreate="CREATE_SELECAO_NEAAD.sql"
arqTrigger="TRIGGER_SELECAO_NEAAD.sql"
arqFuncao="PROCEDURE_SELECAO_NEAAD.sql"
pastaInserts="inserts"
arqCepPuro="cep.sql.bz2"
arqCep="inserts/$arqCepPuro"
arqDescompCep="cep.sql"
#----------------------------- #################### -------------------------------------------

# Definindo função de tratamento de erro
# Um parâmetro: Mensagem de erro que deve ser exibida,se ocorreu algum erro
trataErro(){
  if [ $? -eq 0 ]
  then
    return 0
  else
    echo $1
    exit 1
  fi
}


echo "############ Criando banco de dados $nomeBanco no servidor $servidor ##############"

echo -e "Apagando possível BD antigo...\n"
$execSql $paramSql -e "$cmdDelBD $nomeBanco"
trataErro "Erro ao apagar possível BD antigo."


echo -e "Criando BD $nomeBanco...\n"
$execSql $paramSql -e "$cmdCriaBD $nomeBanco"
trataErro "Erro ao criar BD $nomeBanco."


echo -e "Executando sqls de criação do banco..."
echo -e "Tabelas..."
$execSql $paramSql $nomeBanco < "$pastaSqls/$arqCreate"
trataErro "Erro ao executar sqls de criação das tabelas do BD."
echo -e "Gatilhos..."
$execSql $paramSql $nomeBanco < "$pastaSqls/$arqTrigger"
trataErro "Erro ao executar sqls de criação dos gatilhos do BD."
echo -e "Funções..."
$execSql $paramSql $nomeBanco < "$pastaSqls/$arqFuncao"
trataErro "Erro ao executar sqls de criação das funções do BD."


echo -e "\nExecutando sqls de carga de dados..."
for arq in $pastaSqls/$pastaInserts/*.sql; do
  echo -e "Arquivo $arq..."
  $execSql $paramSql $nomeBanco < $arq
  trataErro "Erro ao executar sqls de carga do arquivo $arq."
done


echo -e "\nDescompactando sqls da base de CEP..."
cp $pastaSqls/$arqCep $pastaTmp
$execBunzip $pastaTmp/$arqCepPuro
trataErro "Erro ao descompactar sqls da base de CEP."


echo -e "\nExecutando sqls da base de CEP..."
$execSql $paramSql $nomeBanco < $pastaTmp/$arqDescompCep
trataErro "Erro ao executar sqls da base de CEP."


echo -e "\nExcluindo arquivos temporários..."
rm -fr $pastaTmp/$arqDescompCep
trataErro "Erro ao excluir arquivos temporários."


echo -e "############### Criação do banco finalizada com sucesso ########################\n"