<?php

/**
 * <b>Create.Class</b>
 * Classe responsavel por cadastros genericos no BD !
 *
 * @copyright (c) 2022, By:Zed
 */
class Create extends Conn {

    private $Tabela;
    private $Dados;
    private $Result;

    /** @var PDOStatement */
    private $Create;

    /** @var PDO */
    private $Conn;

    /**
     * <b>ExeCreate:</b> Executa  em cadastros simplificado no BD utilizado prepara Statements.
     * Basta informar o nome da tabela e um array como nome da coluna e valor!
     *
     * @param STRING $Tabela = Informe o nome da tabela no BD !
     * @param ARRAY $Dados = Informe um array atribuitivo. ( Nome da Coluna => Valor ).
     */
    public function ExeCreate($Tabela, array $Dados){
        $this->Tabela = (string) $Tabela;
        $this->Dados = $Dados;

        $this->getSyntax();
        $this->Execute();
    }


    /**
     * <b>Obter resultado:</b> Retorna o ID do registro inserido ou FALSE caso nem um registro seja inserido!
     * @return INT $Variavel = lastInsertId OR FALSE
     */
    public function getResult(){
        return $this->Result;
    }


    /**
     * ****************************************
     * *********** PRIVATE METHODS ************
     * ****************************************
     */

    //Obtém o PDO e Prepara a query
    private function Connect() {
        $this->Conn = parent::getConn();
        $this->Create = $this->Conn->prepare($this->Create);
    }

    //Cria a sintaxe da query para Prepared Statements
    private function getSyntax() {
        $Fileds = implode(', ', array_keys($this->Dados));
        $Places = ':' . implode(', :', array_keys($this->Dados));
        $this->Create = "INSERT INTO {$this->Tabela} ({$Fileds}) VALUES ({$Places})";
    }

    //Obtém a Conexão e a Syntax, executa a query!
    private function Execute() {
        $this->Connect();
        try{
            $this->Create->execute($this->Dados);
            $this->Result = $this->Conn->lastInsertId();
        } catch (PDOException $e) {
            $this->Result = null;
            WSErro("<b>Erro ao cadastrar:</b> {$e->getMessage()}", $e->getCode());
        }
    }
}
