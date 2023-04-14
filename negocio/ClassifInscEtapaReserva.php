<?php
	 /**
                            * tb_cie_classif_insc_etapa_reserva class
                            * This class manipulates the table ClassifInscEtapaReserva
                            * @requires    >= PHP 5
                            * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
			    * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
                            * @copyright   (C)2015       
                            * @DataBase = selecaoneaaddev
                            * @DatabaseType = mysql
                            * @Host = 172.20.11.188
                            * @date 07/04/2015
                            **/ 

class ClassifInscEtapaReserva {

	private $IPR_ID_INSCRICAO;
 	private $ESP_ID_ETAPA_SEL;
 	private $RVC_ID_RESERVA_CHAMADA;
 	private $CIE_NR_CLASSIFICACAO_CAND;
 	private $CIE_CLASSIF_UTILIZADA;

	/* Construtor padrão da classe */	

	public function __construct($IPR_ID_INSCRICAO, $ESP_ID_ETAPA_SEL, $RVC_ID_RESERVA_CHAMADA, $CIE_NR_CLASSIFICACAO_CAND, $CIE_CLASSIF_UTILIZADA){
		$this->IPR_ID_INSCRICAO = $IPR_ID_INSCRICAO;
		$this->ESP_ID_ETAPA_SEL = $ESP_ID_ETAPA_SEL;
		$this->RVC_ID_RESERVA_CHAMADA = $RVC_ID_RESERVA_CHAMADA;
		$this->CIE_NR_CLASSIFICACAO_CAND = $CIE_NR_CLASSIFICACAO_CAND;
		$this->CIE_CLASSIF_UTILIZADA = $CIE_CLASSIF_UTILIZADA;
	}



	/* GET FIELDS FROM TABLE */	

		function getIPR_ID_INSCRICAO(){
			return $this->IPR_ID_INSCRICAO;
		}/* End of get IPR_ID_INSCRICAO */

		function getESP_ID_ETAPA_SEL(){
			return $this->ESP_ID_ETAPA_SEL;
		}/* End of get ESP_ID_ETAPA_SEL */

		function getRVC_ID_RESERVA_CHAMADA(){
			return $this->RVC_ID_RESERVA_CHAMADA;
		}/* End of get RVC_ID_RESERVA_CHAMADA */

		function getCIE_NR_CLASSIFICACAO_CAND(){
			return $this->CIE_NR_CLASSIFICACAO_CAND;
		}/* End of get CIE_NR_CLASSIFICACAO_CAND */

		function getCIE_CLASSIF_UTILIZADA(){
			return $this->CIE_CLASSIF_UTILIZADA;
		}/* End of get CIE_CLASSIF_UTILIZADA */



	/* SET FIELDS FROM TABLE */	

		function setIPR_ID_INSCRICAO($value){
		$this->IPR_ID_INSCRICAO = $value;
		}/* End of SET IPR_ID_INSCRICAO */

		function setESP_ID_ETAPA_SEL($value){
		$this->ESP_ID_ETAPA_SEL = $value;
		}/* End of SET ESP_ID_ETAPA_SEL */

		function setRVC_ID_RESERVA_CHAMADA($value){
		$this->RVC_ID_RESERVA_CHAMADA = $value;
		}/* End of SET RVC_ID_RESERVA_CHAMADA */

		function setCIE_NR_CLASSIFICACAO_CAND($value){
		$this->CIE_NR_CLASSIFICACAO_CAND = $value;
		}/* End of SET CIE_NR_CLASSIFICACAO_CAND */

		function setCIE_CLASSIF_UTILIZADA($value){
		$this->CIE_CLASSIF_UTILIZADA = $value;
		}/* End of SET CIE_CLASSIF_UTILIZADA */


}
?>
